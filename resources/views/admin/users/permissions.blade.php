@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Cập nhật quyền cho {{ $user->name }}</h5>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}">
                @csrf
                @method('PATCH')

                @foreach ($permissionGroups as $group)
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <strong>{{ $group->name }}</strong>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @foreach ($group->permissions as $permission)
                                    <div class="col-md-6">
                                        <label class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->slug }}" @checked(in_array($permission->slug, $user->permissions ?? [], true))>
                                            <span class="form-check-label">
                                                {{ $permission->name }}
                                                <small class="text-muted">({{ $permission->slug }})</small>
                                            </span>
                                        </label>
                                        @if($permission->description)
                                            <div class="text-muted small ms-4">{{ $permission->description }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lưu quyền</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
