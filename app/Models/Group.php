<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'program_id',
        'start_date',
        'end_date',
        'status',
        'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Связь: группа принадлежит программе
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Связь: у группы много зачислений (обучающихся)
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Связь: у группы много обучающихся через enrollments
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'enrollments')
            ->withPivot(['status', 'completed_at', 'next_training_date', 'certificate_number'])
            ->withTimestamps();
    }

    /**
     * Связь: у группы много договоров
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Скоуп: только активные группы
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['recruiting', 'ongoing']);
    }

    /**
     * Скоуп: только завершённые
     */
    public function scopeFinished($query)
    {
        return $query->where('status', 'finished');
    }

    /**
     * Аксессор: читаемое название статуса
     */
    public function getStatusNameAttribute()
    {
        return match ($this->status) {
            'recruiting' => 'Набор',
            'ongoing' => 'Идёт обучение',
            'finished' => 'Завершена',
            'cancelled' => 'Отменена',
            default => 'Не указан',
        };
    }

    /**
     * Метод: сгенерировать название группы автоматически
     */
    public static function generateName($programId, $year = null)
    {
        $program = Program::find($programId);
        $year = $year ?? date('Y');
        
        // Считаем, сколько групп уже есть у этой программы в этом году
        $count = self::where('program_id', $programId)
            ->whereYear('start_date', $year)
            ->count() + 1;

        return "{$program->name} — Группа {$count} — {$year}";
    }
}
