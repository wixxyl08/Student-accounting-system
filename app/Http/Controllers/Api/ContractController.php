<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\ActivityLog;
use App\Models\Contract;
use App\Models\Group;
use App\Models\Organization;
use App\Models\Employee;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractController extends Controller
{
    /**
     * Список договоров
     * GET /api/contracts
     */
    public function index(Request $request)
    {
        $query = Contract::query()
            ->with([
                'group:id,name',
                'organization:id,full_name,short_name',
                'employee:id,last_name,first_name,middle_name',
                'program:id,name,price',
            ]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                  ->orWhereHas('organization', fn($sub) => $sub->where('full_name', 'like', "%{$search}%"))
                  ->orWhereHas('employee', function ($sub) use ($search) {
                      $sub->where('last_name', 'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($groupId = $request->input('group_id')) {
            $query->where('group_id', $groupId);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);
        $contracts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ContractResource::collection($contracts);
    }

    /**
     * Создание договора (с генерацией DOCX + PDF)
     * POST /api/contracts
     */
    public function store(StoreContractRequest $request)
    {
        $data = $request->validated();

        // Проверка: заказчик — организация ИЛИ физлицо
        if (empty($data['organization_id']) && empty($data['employee_id'])) {
            return response()->json([
                'message' => 'Укажите заказчика — организацию или физическое лицо.',
            ], 422);
        }

        // Загружаем данные
        $group = Group::with(['enrollments.employee', 'program'])->find($data['group_id']);
        $program = Program::find($data['program_id']);

        $students = $group->enrollments->map(fn($e) => $e->employee)->filter();
        $studentsCount = $students->count();
        $totalAmount = $program->price * $studentsCount;

        // Генерируем номер
        $number = Contract::generateNumber();

        // Создаём договор
        $contract = Contract::create([
            'number' => $number,
            'group_id' => $group->id,
            'organization_id' => $data['organization_id'] ?? null,
            'employee_id' => $data['employee_id'] ?? null,
            'program_id' => $program->id,
            'total_amount' => $totalAmount,
        ]);

        // Генерируем файлы
        $this->generateDocx($contract);
        $this->generatePdf($contract);

        ActivityLog::log('created', $contract);

        return (new ContractResource($contract->load(['group', 'organization', 'employee', 'program'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Просмотр одного договора
     * GET /api/contracts/{id}
     */
    public function show(Contract $contract)
    {
        $contract->load(['group', 'organization', 'employee', 'program']);

        return new ContractResource($contract);
    }

    /**
     * Удаление договора (вместе с файлами)
     * DELETE /api/contracts/{id}
     */
    public function destroy(Contract $contract)
    {
        if ($contract->file_docx_path) {
            Storage::disk('local')->delete($contract->file_docx_path);
        }
        if ($contract->file_pdf_path) {
            Storage::disk('local')->delete($contract->file_pdf_path);
        }

        ActivityLog::log('deleted', $contract);
        $contract->delete();

        return response()->json([
            'message' => 'Договор удалён.',
        ]);
    }

    /**
     * Скачивание DOCX
     * GET /api/contracts/{id}/download-docx
     */
    public function downloadDocx(Contract $contract)
    {
        $path = storage_path("app/{$contract->file_docx_path}");

        if (!$contract->file_docx_path || !file_exists($path)) {
            return response()->json(['message' => 'Файл DOCX не найден.'], 404);
        }

        return response()->download($path, "{$contract->number}.docx");
    }

    /**
     * Скачивание PDF
     * GET /api/contracts/{id}/download-pdf
     */
    public function downloadPdf(Contract $contract)
    {
        $path = storage_path("app/{$contract->file_pdf_path}");

        if (!$contract->file_pdf_path || !file_exists($path)) {
            return response()->json(['message' => 'Файл PDF не найден.'], 404);
        }

        return response()->download($path, "{$contract->number}.pdf");
    }

    /**
     * Генерация DOCX из шаблона
     */
    private function generateDocx(Contract $contract)
    {
        $templatePath = storage_path('app/templates/contract.docx');

        if (!file_exists($templatePath)) {
            return;
        }

        $group = $contract->group;
        $program = $contract->program;
        $organization = $contract->organization;
        $employee = $contract->employee;

        $students = $group->enrollments->map(fn($e) => $e->employee)->filter();
        $studentsCount = $students->count();

        // Формируем список обучающихся
        $studentsList = $students->map(function ($s, $index) {
            $position = $s->position ?? '—';
            return ($index + 1) . '. ' . $s->full_name . ' — ' . $position;
        })->implode("\n");

        // Заказчик
        if ($organization) {
            $customerName = $organization->full_name;
            $customerContact = $organization->contact_person ?? '—';
            $customerPosition = $organization->contact_position ?? '—';
        } else {
            $customerName = $employee->full_name ?? '—';
            $customerContact = $employee->full_name ?? '—';
            $customerPosition = '—';
        }

        // Заполняем шаблон
        $template = new TemplateProcessor($templatePath);
        $template->setValue('contract_number', $contract->number);
        $template->setValue('contract_date', now()->format('d.m.Y'));
        $template->setValue('customer_name', $customerName);
        $template->setValue('customer_position', $customerPosition);
        $template->setValue('customer_contact', $customerContact);
        $template->setValue('program_name', $program->name);
        $template->setValue('program_price', number_format($program->price, 2, ',', ' '));
        $template->setValue('students_count', $studentsCount);
        $template->setValue('total_amount', number_format($contract->total_amount, 2, ',', ' '));
        $template->setValue('students_list', $studentsList);
        $template->setValue('start_date', $group->start_date?->format('d.m.Y') ?? '—');
        $template->setValue('end_date', $group->end_date?->format('d.m.Y') ?? '—');

        $fileName = "contracts/contract-{$contract->id}.docx";
        $template->saveAs(storage_path("app/{$fileName}"));

        $contract->update(['file_docx_path' => $fileName]);
    }

    /**
     * Генерация PDF через HTML-шаблон
     */
    private function generatePdf(Contract $contract)
    {
        $group = $contract->group;
        $program = $contract->program;
        $organization = $contract->organization;
        $employee = $contract->employee;

        $students = $group->enrollments->map(fn($e) => $e->employee)->filter();

        if ($organization) {
            $customerName = $organization->full_name;
            $customerContact = $organization->contact_person ?? '—';
            $customerPosition = $organization->contact_position ?? '—';
        } else {
            $customerName = $employee->full_name ?? '—';
            $customerContact = $employee->full_name ?? '—';
            $customerPosition = '—';
        }

        $html = view('contracts.template', [
            'contract' => $contract,
            'group' => $group,
            'program' => $program,
            'students' => $students,
            'customerName' => $customerName,
            'customerContact' => $customerContact,
            'customerPosition' => $customerPosition,
        ])->render();

        $pdf = Pdf::loadHTML($html);
        $fileName = "contracts/contract-{$contract->id}.pdf";
        $pdf->save(storage_path("app/{$fileName}"));

        $contract->update(['file_pdf_path' => $fileName]);
    }
}