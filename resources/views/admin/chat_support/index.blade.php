@extends('layouts.admin')

@section('title', 'Hộp thư Chat')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="mb-1">Hộp thư Chat</h4>
            <div class="text-muted">Danh sách hội thoại theo user/guest</div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header fw-bold">Hội thoại gần đây</div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 180px;">Đối tượng</th>
                        <th>Tin nhắn cuối</th>
                        <th style="width: 170px;">Thời gian</th>
                        <th style="width: 120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $it)
                        @php
                            $m = $it->last_message;
                            $label = $it->display_name ?? ($it->user_id ? ('User #' . $it->user_id) : 'Khách CDN');
                            $created = $m ? optional($m->created_at)->format('d/m/Y H:i') : '';
                            $preview = $m ? $m->text : '';
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;">{{ $label }}</td>
                            <td style="max-width: 720px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $preview }}</td>
                            <td style="white-space:nowrap;">{{ $created }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.chat-support.thread', ['user_id' => $it->user_id, 'guest_id' => $it->guest_id]) }}">Mở</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Chưa có tin nhắn</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
