<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'link',
        'is_read',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Icons for each notification type.
     */
    public const TYPE_ICONS = [
        'penjualan' => 'bi-cart-check',
        'pembelian' => 'bi-box-seam',
        'system' => 'bi-bell',
    ];

    /**
     * Colors for each notification type.
     */
    public const TYPE_COLORS = [
        'penjualan' => '#10b981',   // green
        'pembelian' => '#3b82f6',   // blue
        'system' => '#06b6d4',      // cyan
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get icon for notification type.
     */
    public function getIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->type] ?? 'bi-bell';
    }

    /**
     * Get color for notification type.
     */
    public function getColorAttribute(): string
    {
        return self::TYPE_COLORS[$this->type] ?? '#06b6d4';
    }

    /**
     * Scope: Get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Get notifications for a specific user or broadcast.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id'); // broadcast notifications
        });
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Get human-readable time difference.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
