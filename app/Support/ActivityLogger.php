<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $action, $subject = null, ?string $description = null, array $properties = [], ?Request $request = null): void
    {
        try {
            $user = Auth::user();
            $subjectType = null;
            $subjectId = null;

            if (is_object($subject)) {
                $subjectType = get_class($subject);
                $subjectId = $subject->id ?? null;
            } elseif (is_array($subject)) {
                $subjectType = $subject['type'] ?? null;
                $subjectId = $subject['id'] ?? null;
            }

            $req = $request ?: request();

            ActivityLog::create([
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'description' => $description,
                'properties' => $properties,
                'ip_address' => method_exists($req, 'ip') ? $req->ip() : null,
                'user_agent' => method_exists($req, 'userAgent') ? $req->userAgent() : null,
            ]);
        } catch (\Throwable $e) {
        }
    }
}
