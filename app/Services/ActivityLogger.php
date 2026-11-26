<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        array $metadata = [],
        ?User $actor = null
    ): ActivityLog {
        $user = $actor ?? auth()->user();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'metadata' => empty($metadata) ? null : $metadata,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}
