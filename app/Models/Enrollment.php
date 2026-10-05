<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'group_id',
        'status',
        'completed_at',
        'next_training_date',
        'certificate_number',
        'note',
    ];

    protected $casts = [
        'completed_at' => 'date',
        'next_training_date' => 'date',
    ];

    /**
     * Связь: зачисление принадлежит сотруднику
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Связь: зачисление принадлежит группе
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Связь: программа через группу
     */
    public function program()
    {
        return $this->hasOneThrough(
            Program::class,
            Group::class,
            'id',           // внешний ключ в groups (id)
            'id',           // внешний ключ в programs (id)
            'group_id',     // локальный ключ в enrollments
            'program_id'    // локальный ключ в groups
        );
    }

    /**
     * Скоуп: только завершённые (прошли обучение)
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Скоуп: только с рассчитанной датой следующего обучения
     */
    public function scopeWithNextTraining($query)
    {
        return $query->whereNotNull('next_training_date');
    }

    /**
     * Скоуп: пора на повторное обучение (в пределах N дней)
     */
    public function scopeDueForRetraining($query, $days = null)
    {
        return $query->where('status', 'completed')
            ->whereNotNull('next_training_date')
            ->whereRaw('next_training_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)', [$days ?? 60]);
    }

    /**
     * Аксессор: читаемое название статуса
     */
    public function getStatusNameAttribute()
    {
        return match ($this->status) {
            'enrolled' => 'Зачислен',
            'studying' => 'Обучается',
            'completed' => 'Прошёл обучение',
            'expelled' => 'Отчислен',
            default => 'Не указан',
        };
    }

    /**
     * Метод: отметить прохождение обучения
     * Автоматически рассчитывает дату следующего обучения
     */
    public function markAsCompleted($completedDate = null)
    {
        $completedDate = $completedDate ?? now();
        
        $this->status = 'completed';
        $this->completed_at = $completedDate;

        // Рассчитываем дату следующего обучения по программе
        $program = $this->group->program;
        if ($program) {
            $nextDate = $program->calculateNextTrainingDate($completedDate);
            $this->next_training_date = $nextDate;
        }

        $this->save();

        return $this;
    }

    /**
     * Метод: сколько дней осталось до повторного обучения
     */
    public function getDaysUntilRetrainingAttribute()
    {
        if (!$this->next_training_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->next_training_date->startOfDay(), false);
    }

    /**
     * Аксессор: цветовая индикация для уведомлений
     * зелёный — более 120 дней, жёлтый — 60-120, красный — менее 60
     */
    public function getUrgencyColorAttribute()
    {
    $days = $this->getDaysUntilRetrainingAttribute();

    if ($days === null) {
        return 'gray';
    }

    if ($days > 120) {
        return 'green';
    }

    if ($days >= 60) {
        return 'yellow';
    }

    return 'red';
    }
}