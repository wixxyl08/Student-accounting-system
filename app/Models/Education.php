<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasFactory;
    protected $table = 'educations';
    protected $fillable = [
        'employee_id',
        'level',
        'institution',
        'graduation_year',
        'specialty',
        'file_path',
    ];

    protected $casts = [
        'graduation_year' => 'integer',
    ];

    /**
     * Связь: образование принадлежит сотруднику
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Аксессор: читаемое название уровня образования
     */
    public function getLevelNameAttribute()
    {
        return match ($this->level) {
            'higher' => 'Высшее',
            'secondary_professional' => 'Средне-профессиональное',
            'secondary_general' => 'Среднее общее',
            'other' => 'Иное',
            default => 'Не указано',
        };
    }
}
