<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiConversation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'model', 'system_context', 'last_message_at'];

    protected $casts = [
        'system_context' => 'array',
        'last_message_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aiMessages(): HasMany
    {
        return $this->hasMany(AiMessage::class, 'ai_conversation_id');
    }

    public function latestAiMessage()
    {
        return $this->hasOne(AiMessage::class, 'ai_conversation_id')->latestOfMany();
    }
}