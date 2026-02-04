<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'template_id',
        'type',
        'channel',
        'recipient',
        'subject',
        'content',
        'body',
        'priority',
        'status',
        'error_message',
        'metadata',
        'read_at',
        'sent_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope để lấy thông báo chưa đọc
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // Scope để lấy thông báo đã đọc
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    // Scope để lấy thông báo theo độ ưu tiên
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Scope để lấy thông báo theo kênh
    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    // Scope để lấy thông báo theo loại
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope để lấy thông báo gần đây
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
