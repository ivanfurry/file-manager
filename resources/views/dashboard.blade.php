@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Левая колонка: Папки -->
    <div class="col-md-3">
        <div class="card mb-3">
            <div class="card-header">Навигация</div>
            <div class="card-body">
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary w-100 mb-2">🏠 Корневая папка</a>
                @if($currentFolder)
                    <a href="{{ route('dashboard', ['folder_id' => $currentFolder->parent_id]) }}" class="btn btn-sm btn-outline-secondary w-100 mb-2">⬆ На уровень выше</a>
                @endif
                <ul class="list-group">
                    @foreach($folders as $folder)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ route('dashboard', ['folder_id' => $folder->id]) }}" class="text-decoration-none text-dark">📁 {{ $folder->name }}</a>
                            <div>
                                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#renameFolderModal{{ $folder->id }}">✎</button>
                                <form action="{{ route('folders.destroy', $folder) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить папку?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">✕</button>
                                </form>
                            </div>
                        </li>
                        <!-- Модальное окно переименования -->
                        <div class="modal fade" id="renameFolderModal{{ $folder->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form action="{{ route('folders.update', $folder) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-content">
                                        <div class="modal-header"><h5 class="modal-title">Переименовать папку</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                        <div class="modal-body"><input type="text" name="name" class="form-control" value="{{ $folder->name }}" required></div>
                                        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button><button type="submit" class="btn btn-primary">Сохранить</button></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Создать папку</div>
            <div class="card-body">
                <form action="{{ route('folders.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? null }}">
                    <div class="mb-2"><input type="text" name="name" class="form-control form-control-sm" placeholder="Имя папки" required></div>
                    <button type="submit" class="btn btn-sm btn-primary w-100">Создать</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Правая колонка: Файлы -->
    <div class="col-md-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Файлы @if($currentFolder) в "{{ $currentFolder->name }}" @endif</span>
                <form action="{{ route('dashboard') }}" method="GET" class="d-flex">
                    @if($currentFolder) <input type="hidden" name="folder_id" value="{{ $currentFolder->id }}"> @endif
                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Поиск по имени..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-secondary">Найти</button>
                </form>
            </div>
            <div class="card-body">
                <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data" class="mb-4">
                    @csrf
                    <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? null }}">
                    <div class="input-group">
                        <input type="file" name="file" class="form-control" required>
                        <button type="submit" class="btn btn-success">Загрузить (до 5 МБ)</button>
                    </div>
                    <small class="text-muted">Разрешены: jpg, jpeg, png, pdf</small>
                </form>

                <table class="table table-hover align-middle">
                    <thead><tr><th>Имя</th><th>Теги</th><th>Размер</th><th>Действия</th></tr></thead>
                    <tbody>
                        @foreach($files as $file)
                        <tr>
                            <td>@if(str_starts_with($file->mime_type, 'image/')) 🖼️ @else 📄 @endif {{ $file->name }}</td>
                            <td>
                                @foreach($file->tags as $tag)
                                    <span class="badge bg-info text-dark">
                                        {{ $tag->name }}
                                        <form action="{{ route('files.tags.remove', [$file, $tag]) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm p-0 text-dark" style="font-size: 0.8em;">✕</button>
                                        </form>
                                    </span>
                                @endforeach
                                <form action="{{ route('files.tags.add', $file) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="text" name="tag_name" class="form-control form-control-sm d-inline-block" style="width: 80px;" placeholder="Тег">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">+</button>
                                </form>
                            </td>
                            <td>{{ number_format($file->size / 1024, 1) }} КБ</td>
                            <td>
                                <a href="{{ route('files.download', $file) }}" class="btn btn-sm btn-primary">Скачать</a>
                                @if(str_starts_with($file->mime_type, 'image/'))
                                    <a href="{{ route('files.preview', $file) }}" target="_blank" class="btn btn-sm btn-info">Просмотр</a>
                                @endif
                                
                                <form action="{{ route('files.move', $file) }}" method="POST" class="d-inline">
                                    @csrf @method('PUT')
                                    <select name="folder_id" class="form-select form-select-sm d-inline-block" style="width: auto;" onchange="this.form.submit()">
                                        <option value="">📁 Корень</option>
                                        @foreach($allFolders as $folder)
                                            @if($folder->id !== ($currentFolder->id ?? null))
                                                <option value="{{ $folder->id }}" {{ $file->folder_id == $folder->id ? 'selected' : '' }}>{{ $folder->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </form>

                                <form action="{{ route('files.destroy', $file) }}" method="POST" class="d-inline" onsubmit="return confirm('Удалить файл?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection