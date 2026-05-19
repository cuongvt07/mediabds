<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatFeedback extends Model
{
    protected $table = 'chat_feedback';

    protected $fillable = [
        'chat_message_id', 'user_id', 'rating',
        'error_category', 'note',
        'intent_at_time', 'confidence_at_time', 'tool_calls_meta',
    ];

    protected $casts = [
        'tool_calls_meta' => 'array',
        'confidence_at_time' => 'float',
    ];

    public const ERROR_CATEGORIES = [
        'wrong_tool'     => 'Sai tool',
        'wrong_filter'   => 'Sai filter / sai param',
        'wrong_format'   => 'Sai format trình bày',
        'missed_data'    => 'Bỏ sót dữ liệu',
        'fabricated'     => 'Bịa dữ liệu',
        'other'          => 'Khác',
    ];

    public function chatMessage(): BelongsTo
    {
        return $this->belongsTo(ChatMessage::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
