<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'full_name',
        'short_name',
        'inn',
        'kpp',
        'ogrn',
        'legal_address',
        'actual_address',
        'phone',
        'email',
        'contact_person',
        'contact_position',
        'note',
        'created_by',
        'updated_by',
    ];

    /**
     * Связь: у организации много сотрудников
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Связь: кто создал организацию
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Связь: кто последний редактировал
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}