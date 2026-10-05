<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'action',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Связь: запись журнала принадлежит пользователю
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь: сущность, к которой относится запись (полиморфная)
     */
    public function entity()
    {
        return $this->morphTo();
    }

    /**
     * Скоуп: записи по конкретной сущности
     */
    public function scopeForEntity($query, $type, $id)
    {
        return $query->where('entity_type', $type)
            ->where('entity_id', $id);
    }

    /**
     * Скоуп: записи конкретного пользователя
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Аксессор: читаемое название действия
     */
    public function getActionNameAttribute()
    {
        return match ($this->action) {
            'created' => 'Создано',
            'updated' => 'Изменено',
            'deleted' => 'Удалено',
            default => $this->action,
        };
    }

    /**
     * Статический метод: записать действие
     */
    public static function log($action, $entity, $userId = null)
    {
        return self::create([
            'user_id' => $userId ?? auth()->id(),
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id,
            'action' => $action,
            'created_at' => now(),
        ]);
    }
}
