<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['patient_id', 'dietitian_id', 'scheduled_at', 'duration_minutes', 'status', 'room_id'])]
class Appointment extends Model
{
    public const JOIN_EARLY_MINUTES = 15;
    public const JOIN_GRACE_MINUTES = 30;

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function dietitian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dietitian_id');
    }

    public function isParticipant(User $user): bool
    {
        return (int) $user->id === (int) $this->patient_id
            || (int) $user->id === (int) $this->dietitian_id;
    }

    public function joinOpensAt(): Carbon
    {
        return $this->scheduled_at->copy()->subMinutes(self::JOIN_EARLY_MINUTES);
    }

    public function joinClosesAt(): Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes + self::JOIN_GRACE_MINUTES);
    }

    public function isJoinable(): bool
    {
        return $this->status === 'scheduled'
            && now()->betweenIncluded($this->joinOpensAt(), $this->joinClosesAt());
    }
}