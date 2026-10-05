<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'last_name',
        'first_name',
        'middle_name',
        'birth_date',
        'phone',
        'email',
        'organization_id',
        'position',
        'status',
        'fired_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'fired_at' => 'date',
    ];

    /**
     * Связь: сотрудник привязан к организации (или null — физлицо)
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Связь: у сотрудника много записей об образовании
     */
    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    /**
     * Связь: у сотрудника много зачислений в группы
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Связь: кто создал сотрудника
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Связь: кто редактировал
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Аксессор: полное ФИО
     */
    public function getFullNameAttribute()
    {
        return trim("{$this->last_name} {$this->first_name} {$this->middle_name}");
    }

    /**
     * Скоуп: только активные сотрудники
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Скоуп: только уволенные
     */
    public function scopeFired($query)
    {
        return $query->where('status', 'fired');
    }
}
