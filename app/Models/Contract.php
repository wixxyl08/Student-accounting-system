<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'group_id',
        'organization_id',
        'employee_id',
        'program_id',
        'total_amount',
        'file_docx_path',
        'file_pdf_path',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Связь: договор привязан к группе
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Связь: договор с организацией (или null, если с физлицом)
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Связь: договор с физическим лицом (или null, если с организацией)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Связь: договор на программу
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Скоуп: договоры по организации
     */
    public function scopeForOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Скоуп: договоры по программе
     */
    public function scopeForProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Аксессор: имя заказчика (организация ИЛИ физлицо)
     */
    public function getCustomerNameAttribute()
    {
        if ($this->organization) {
            return $this->organization->full_name;
        }

        if ($this->employee) {
            return $this->employee->full_name;
        }

        return 'Заказчик не указан';
    }

    /**
     * Метод: сгенерировать номер договора (шаблон: Д-YYYY-NNNN)
     */
    public static function generateNumber($year = null)
        {
         $year = $year ?? date('Y');
    
            // Получаем максимальный номер за этот год
            $lastContract = self::whereYear('created_at', $year)
                ->orderBy('id', 'desc')
                ->first();
    
            if ($lastContract) {
                // Извлекаем число из последнего номера (Д-2026-0004 → 4)
                $parts = explode('-', $lastContract->number);
                $lastNumber = (int) end($parts);
             } else {
             $lastNumber = 0;
            }
    
        $nextNumber = $lastNumber + 1;
    
        return 'Д-' . $year . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
