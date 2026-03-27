<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ChatSupportController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 50);
        if ($limit < 10) $limit = 10;
        if ($limit > 200) $limit = 200;

        $threads = DB::table('chat_messages as cm')
            ->selectRaw('MAX(cm.id) as last_id, cm.user_id, cm.guest_id')
            ->groupBy('cm.user_id', 'cm.guest_id')
            ->orderByDesc('last_id')
            ->limit($limit)
            ->get();

        $lastIds = $threads->pluck('last_id')->filter()->values()->all();

        $lastMessages = ChatMessage::query()
            ->whereIn('id', $lastIds)
            ->orderByDesc('id')
            ->get()
            ->keyBy(function (ChatMessage $m) {
                return ($m->user_id ? ('u:' . $m->user_id) : ('g:' . $m->guest_id));
            });

        $userIds = $threads->pluck('user_id')->filter()->map(function ($v) {
            return (int) $v;
        })->unique()->values()->all();

        $usersById = User::query()
            ->whereIn('id', $userIds)
            ->get(['id', 'name', 'email'])
            ->keyBy('id');

        $items = $threads->map(function ($t) use ($lastMessages, $usersById) {
            $key = ($t->user_id ? ('u:' . $t->user_id) : ('g:' . $t->guest_id));
            $m = $lastMessages->get($key);

            $uid = (int) ($t->user_id ?? 0);
            if ($uid > 0) {
                $u = $usersById->get($uid);
                $displayName = trim((string) optional($u)->name);
                if ($displayName === '') {
                    $displayName = trim((string) optional($u)->email);
                }
                if ($displayName === '') {
                    $displayName = 'User #' . $uid;
                }
            } else {
                $displayName = 'Khách CDN';
            }

            return (object) [
                'user_id' => $t->user_id,
                'guest_id' => $t->guest_id,
                'display_name' => $displayName,
                'last_message' => $m,
            ];
        });

        return view('admin.chat_support.index', [
            'items' => $items,
            'limit' => $limit,
        ]);
    }

    public function unread(Request $request)
    {
        $sinceId = (int) $request->query('since_id', 0);
        if ($sinceId < 0) $sinceId = 0;

        $q = ChatMessage::query()->where('type', 'user');
        if ($sinceId > 0) {
            $q->where('id', '>', $sinceId);
        }

        $count = (int) $q->count();
        $maxId = (int) (ChatMessage::query()->where('type', 'user')->max('id') ?? 0);

        return response()->json([
            'ok' => true,
            'count' => $count,
            'max_id' => $maxId,
        ]);
    }

    public function thread(Request $request)
    {
        $userId = (int) $request->query('user_id', 0);
        $guestId = trim((string) $request->query('guest_id', ''));

        $afterId = (int) $request->query('after_id', 0);
        $isAjax = $request->query('ajax') === '1' || $request->wantsJson();

        if ($userId <= 0 && $guestId === '') {
            abort(404);
        }

        $q = ChatMessage::query();
        if ($userId > 0) {
            $q->where('user_id', $userId);
        } else {
            $q->whereNull('user_id')->where('guest_id', $guestId);
        }

        if ($isAjax) {
            if ($afterId > 0) {
                $q->where('id', '>', $afterId);
            }

            $items = $q->orderBy('id')->limit(100)->get(['id', 'type', 'text', 'created_at']);

            return response()->json([
                'ok' => true,
                'items' => $items,
            ]);
        }

        $messages = $q->orderBy('id')->get(['id', 'user_id', 'guest_id', 'type', 'text', 'created_at']);

        return view('admin.chat_support.thread', [
            'userId' => $userId > 0 ? $userId : null,
            'guestId' => $userId > 0 ? null : $guestId,
            'messages' => $messages,
        ]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:5000'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'guest_id' => ['nullable', 'string', 'max:64'],
        ]);

        $userId = (int) ($data['user_id'] ?? 0);
        $guestId = trim((string) ($data['guest_id'] ?? ''));

        if ($userId <= 0 && $guestId === '') {
            if ($request->wantsJson() || $request->query('ajax') === '1') {
                return response()->json([
                    'ok' => false,
                    'message' => 'Thiếu user_id/guest_id.',
                ], 422);
            }

            return back()->withErrors(['text' => 'Thiếu user_id/guest_id.']);
        }

        $msg = ChatMessage::create([
            'user_id' => $userId > 0 ? $userId : null,
            'guest_id' => $userId > 0 ? null : $guestId,
            'type' => 'staff',
            'text' => $data['text'],
        ]);

        if ($request->wantsJson() || $request->query('ajax') === '1') {
            return response()->json([
                'ok' => true,
                'item' => $msg->only(['id', 'type', 'text', 'created_at']),
            ]);
        }

        return redirect()->route('admin.chat-support.thread', [
            'user_id' => $userId > 0 ? $userId : null,
            'guest_id' => $userId > 0 ? null : $guestId,
        ]);
    }
}
