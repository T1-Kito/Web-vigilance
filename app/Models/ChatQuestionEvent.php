<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatQuestionEvent extends Model
{
    protected $fillable = [
        'user_id',
        'guest_id',
        'intent',
        'text',
        'is_unanswered',
        'unanswered_reason',
        'normalized_text',
        'page_url',
        'ip',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
