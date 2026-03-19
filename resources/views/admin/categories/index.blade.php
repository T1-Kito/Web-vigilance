@extends('layouts.admin')

@section('title', 'Quản lý danh mục')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Quản lý danh mục</h2>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Thêm danh mục
        </a>
    </div>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Thứ tự</th>
                            <th>Tên danh mục</th>
                            <th>Danh mục cha</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        function renderCategoryRows($categories, $parentId = null, $prefix = '', $all = null) {
                            foreach($categories->where('parent_id', $parentId) as $cat) {
                                echo '<tr>';
                                echo '<td></td>';
                                echo '<td>'.($cat->sort_order ?? 0).'</td>';
                                echo '<td class="fw-bold">'.$prefix.$cat->name.'</td>';
                                echo '<td>'.($cat->parent ? $cat->parent->name : '-').'</td>';
                                echo '<td>';
                                echo '<a href="'.route('admin.categories.edit', $cat->id).'" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil-square"></i></a>';
                                echo '<form action="'.route('admin.categories.destroy', $cat->id).'" method="POST" class="d-inline">';
                                echo csrf_field();
                                echo method_field('DELETE');
                                echo '<button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Xóa danh mục này?\')"><i class="bi bi-trash"></i></button>';
                                echo '</form>';
                                echo '</td>';
                                echo '</tr>';
                                renderCategoryRows($categories, $cat->id, $prefix.'--- ', $categories);
                            }
                        }
                        renderCategoryRows($categories);
                        @endphp
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection 