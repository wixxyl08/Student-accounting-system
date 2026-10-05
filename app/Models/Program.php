<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'education_requirement',
        'retraining_period',
        'custom_months',
        'duration',
        'status',
        'notify_days_before',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'custom_months' => 'integer',
        'notify_days_before' => 'integer',
    ];

    /**
     * Связь: у программы много групп
     */
    public function groups()
    {
        return $this->hasMany(Group::class);
    }

    /**
     * Связь: у программы много зачислений
     */
    public function enrollments()
    {
    return $this->hasManyThrough(
        Enrollment::class,   // конечная модель
        Group::class,        // промежуточная модель
        'program_id',        // FK в groups, ведущий к programs
        'group_id',          // FK в enrollments, ведущий к groups
        'id',                // локальный ключ в programs
        'id'                 // локальный ключ в groups
    );
    }

    /**
     * Связь: у программы много договоров
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Скоуп: только активные программы
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Скоуп: только архив
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archive');
    }

    /**
     * Метод: получить периодичность повторного обучения в месяцах
     */
    public function getRetrainingMonthsAttribute()
    {
        return match ($this->retraining_period) {
            '1_year' => 12,
            '3_years' => 36,
            '5_years' => 60,
            'custom' => $this->custom_months ?? 0,
            default => 0,
        };
    }

    /**
     * Метод: вычислить дату следующего обучения
     */
    public function calculateNextTrainingDate($fromDate)
    {
        $months = $this->getRetrainingMonthsAttribute();
        
        if ($months <= 0) {
            return null;
        }

        return \Carbon\Carbon::parse($fromDate)->addMonths($months);
    }

    /**
     * Аксессор: читаемое название периодичности
     */
    public function getRetrainingPeriodNameAttribute()
    {
        return match ($this->retraining_period) {
            'none' => 'Не требуется',
            '1_year' => '1 год',
            '3_years' => '3 года',
            '5_years' => '5 лет',
            'custom' => "Каждые {$this->custom_months} мес.",
            default => 'Не указано',
        };
    }
}