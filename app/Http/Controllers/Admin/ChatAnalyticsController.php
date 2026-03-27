<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatQuestionEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->query('days', 7);
        if ($days < 1) $days = 1;
        if ($days > 90) $days = 90;

        $onlyUnanswered = (bool) $request->boolean('unanswered');

        $from = Carbon::now()->subDays($days)->startOfDay();

        $base = ChatQuestionEvent::query()->where('created_at', '>=', $from);
        if ($onlyUnanswered) {
            $base->where('is_unanswered', true);
        }

        $topIntents = (clone $base)
            ->select('intent', DB::raw('COUNT(*) as cnt'))
            ->groupBy('intent')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        $topQuestions = (clone $base)
            ->select('normalized_text', DB::raw('MIN(text) as sample_text'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('normalized_text')
            ->orderByDesc('cnt')
            ->limit(20)
            ->get();

        $latest = (clone $base)
            ->orderByDesc('id')
            ->limit(50)
            ->get(['id', 'intent', 'is_unanswered', 'unanswered_reason', 'text', 'page_url', 'user_id', 'guest_id', 'created_at']);

        return view('admin.chat_analytics.index', compact('days', 'onlyUnanswered', 'topIntents', 'topQuestions', 'latest'));
    }
}
