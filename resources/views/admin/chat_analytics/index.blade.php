@extends('layouts.admin')

@section('title', 'Thống kê Chat - Câu hỏi khách')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="mb-1">Thống kê Chat</h4>
            <div class="text-muted">Tổng hợp câu hỏi khách hay hỏi</div>
        </div>
        <form method="GET" class="d-flex align-items-center gap-2">
            <label class="text-muted">Khoảng thời gian:</label>
            <select name="days" class="form-select" style="width: 160px;">
                @foreach([1,3,7,14,30,60,90] as $d)
                    <option value="{{ $d }}" {{ (int)$days === (int)$d ? 'selected' : '' }}>{{ $d }} ngày</option>
                @endforeach
            </select>
            <div class="form-check ms-2">
                <input class="form-check-input" type="checkbox" name="unanswered" value="1" id="onlyUnanswered" {{ !empty($onlyUnanswered) ? 'checked' : '' }}>
                <label class="form-check-label" for="onlyUnanswered">Chỉ câu chưa trả lời được</label>
            </div>
            <button class="btn btn-primary">Lọc</button>
        </form>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Top chủ đề (intent)</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Chủ đề</th>
                                <th class="text-end">Lượt hỏi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topIntents as $row)
                                <tr>
                                    <td>{{ $row->intent }}</td>
                                    <td class="text-end">{{ number_format($row->cnt) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">Top câu hỏi (nguyên văn - gom theo nội dung)</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Câu hỏi</th>
                                <th class="text-end">Lượt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topQuestions as $row)
                                <tr>
                                    <td style="max-width: 720px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $row->sample_text }}">{{ $row->sample_text }}</td>
                                    <td class="text-end">{{ number_format($row->cnt) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header fw-bold">50 câu hỏi gần nhất</div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Intent</th>
                                <th>Trạng thái</th>
                                <th>Nội dung</th>
                                <th>Trang</th>
                                <th>User</th>
                                <th>Guest</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latest as $m)
                                <tr>
                                    <td style="white-space:nowrap;">{{ optional($m->created_at)->format('d/m/Y H:i') }}</td>
                                    <td style="white-space:nowrap;">{{ $m->intent }}</td>
                                    <td style="white-space:nowrap;">
                                        @if(!empty($m->is_unanswered))
                                            <span class="badge bg-danger">Chưa trả lời</span>
                                            @if(!empty($m->unanswered_reason))
                                                <span class="text-muted">({{ $m->unanswered_reason }})</span>
                                            @endif
                                        @else
                                            <span class="badge bg-success">OK</span>
                                        @endif
                                    </td>
                                    <td style="max-width: 720px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $m->text }}">{{ $m->text }}</td>
                                    <td style="max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $m->page_url }}">{{ $m->page_url }}</td>
                                    <td style="white-space:nowrap;">{{ $m->user_id ?? '-' }}</td>
                                    <td style="white-space:nowrap;">{{ $m->guest_id ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
