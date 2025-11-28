@extends(backpack_view('blank'))

@php
    $formatBytes = static function (int $bytes): string {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = (int) floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, 2) . ' ' . ($units[$power] ?? 'B');
    };
@endphp

@section('header')
    <section class="content-header">
        <h1>
            Бэкапы базы данных
            <small>Создание, скачивание и восстановление дампов</small>
        </h1>
    </section>
@endsection

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Новый ручной дамп</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('backpack.dumper.manual') }}">
                    @csrf
                    <div class="form-group">
                        <label for="dump-label">Название</label>
                        <input type="text" name="label" id="dump-label" class="form-control" value="{{ old('label') }}" placeholder="Например: Перед обновлением" />
                        <small class="form-text text-muted">Поле необязательно, по умолчанию используется имя файла.</small>
                        @error('label')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="dump-tables">Таблицы</label>
                        <select name="tables[]" id="dump-tables" multiple class="form-control" size="12">
                            @foreach($tables as $table)
                                <option value="{{ $table }}" @selected(collect(old('tables', []))->contains($table))>{{ $table }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Оставьте список пустым, чтобы выгрузить всю базу.</small>
                        @error('tables')
                            <span class="text-danger small d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Создать дамп</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Ручные дампы</h4>
                <span class="text-muted small">{{ $manualDumps->count() }} файлов</span>
            </div>
            <div class="card-body p-0">
                @if($manualDumps->isEmpty())
                    <p class="text-muted px-3 py-4 mb-0">Пока нет созданных дампов.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Таблицы</th>
                                    <th>Создан</th>
                                    <th>Размер</th>
                                    <th class="text-right">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($manualDumps as $dump)
                                    <tr>
                                        <td>{{ $dump->title() }}</td>
                                        <td><span class="badge badge-light">{{ $dump->tablesLabel() }}</span></td>
                                        <td>{{ $dump->createdAt->format('d.m.Y H:i') }}</td>
                                        <td>{{ $formatBytes($dump->size) }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('backpack.dumper.download', $dump->identifier()) }}" class="btn btn-sm btn-outline-secondary">Скачать</a>
                                            <form method="POST" action="{{ route('backpack.dumper.restore') }}" class="d-inline" onsubmit="return confirm('Восстановить выбранный дамп? Текущее содержимое таблиц будет перезаписано.');">
                                                @csrf
                                                <input type="hidden" name="reference" value="{{ $dump->identifier() }}">
                                                <button type="submit" class="btn btn-sm btn-warning">Восстановить</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <h3 class="mb-3">Автоматические дампы</h3>
    @forelse($autoCases as $case)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-1">{{ $case['label'] }} <small class="text-muted">({{ $case['key'] }})</small></h4>
                    <div class="text-muted small">
                        <span class="mr-3">Cron: <code>{{ $case['cron'] ?? '—' }}</code></span>
                        <span class="mr-3">Таблицы: {{ $case['tables'] === '*' ? 'Вся база' : (is_array($case['tables']) ? implode(', ', $case['tables']) : $case['tables']) }}</span>
                        @if(!empty($case['retention']['keep_last']))
                            <span class="mr-3">Храним последних: {{ $case['retention']['keep_last'] }}</span>
                        @endif
                        @if(!empty($case['retention']['keep_days']))
                            <span>Удаляем старше: {{ $case['retention']['keep_days'] }} дн.</span>
                        @endif
                    </div>
                </div>
                <form method="POST" action="{{ route('backpack.dumper.auto.run', $case['key']) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-primary">Сделать дамп сейчас</button>
                </form>
            </div>
            <div class="card-body p-0">
                @php($caseDumps = $autoDumps[$case['key']] ?? collect())
                @if($caseDumps->isEmpty())
                    <p class="text-muted px-3 py-4 mb-0">Пока нет файлов для этого сценария.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Файл</th>
                                    <th>Создан</th>
                                    <th>Размер</th>
                                    <th class="text-right">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($caseDumps as $dump)
                                    <tr>
                                        <td>{{ $dump->title() }}</td>
                                        <td>{{ $dump->createdAt->format('d.m.Y H:i') }}</td>
                                        <td>{{ $formatBytes($dump->size) }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('backpack.dumper.download', $dump->identifier()) }}" class="btn btn-sm btn-outline-secondary">Скачать</a>
                                            <form method="POST" action="{{ route('backpack.dumper.restore') }}" class="d-inline" onsubmit="return confirm('Восстановить выбранный дамп?');">
                                                @csrf
                                                <input type="hidden" name="reference" value="{{ $dump->identifier() }}">
                                                <button type="submit" class="btn btn-sm btn-warning">Восстановить</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <p class="text-muted">Автоматические сценарии пока не настроены.</p>
    @endforelse
</div>
@endsection
