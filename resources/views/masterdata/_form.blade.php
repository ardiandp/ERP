@csrf
<div class="row">
    @foreach($cfg['fields'] as $field => $def)
    @php
        $type = $def['type'] ?? 'text';
        $value = old($field, data_get($item ?? null, $field));
        $options = $def['options'] ?? [];
    @endphp
    <div class="col-md-4 mb-3">
        <label class="form-label">{{ $def['label'] ?? $field }}</label>
        @if($type === 'select')
            <select name="{{ $field }}" class="form-select">
                <option value="">-</option>
                @foreach($options as $opt)
                <option value="{{ $opt }}" @selected((string) $value === (string) $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        @elseif($type === 'textarea')
            <textarea name="{{ $field }}" class="form-control">{{ $value }}</textarea>
        @elseif($type === 'number')
            <input step="any" type="number" name="{{ $field }}" class="form-control" value="{{ $value }}">
        @elseif($type === 'date')
            <input type="date" name="{{ $field }}" class="form-control" value="{{ $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value }}">
        @else
            <input name="{{ $field }}" class="form-control" value="{{ $value }}">
        @endif
    </div>
    @endforeach
</div>
@if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<button class="btn btn-primary">Simpan</button>
