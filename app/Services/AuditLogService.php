<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function record(
        Model $subject,
        string $action,
        ?User $actor = null,
        array $properties = [],
        ?Request $request = null,
    ): void {
        if (! config('booking.audit.enabled', true)) {
            return;
        }

        AuditLog::query()->create([
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'actor_id' => $actor?->id,
            'action' => $action,
            'properties' => $properties !== [] ? $properties : null,
            'ip_address' => $request?->ip(),
            'created_at' => now(),
        ]);
    }
}
