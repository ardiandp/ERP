<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class MasterCrudController extends Controller
{
    protected function config(): array
    {
        return config('masterdata');
    }

    protected function entity(string $key): array
    {
        $entities = $this->config()['entities'];
        abort_unless(isset($entities[$key]), 404);

        return $entities[$key];
    }

    public function index(string $entity)
    {
        $cfg = $this->entity($entity);
        $model = $cfg['model'];
        $items = $model::query()->latest()->paginate(15);

        return view('masterdata.index', compact('cfg', 'items', 'entity'));
    }

    public function create(string $entity)
    {
        $cfg = $this->entity($entity);

        return view('masterdata.create', compact('cfg', 'entity'));
    }

    public function store(Request $request, string $entity)
    {
        $cfg = $this->entity($entity);
        $data = $request->validate($this->rules($cfg));
        $model = $cfg['model'];
        $record = $model::create($data);
        AuditLog::record('create', $record, [], $data);

        return redirect()->route('masterdata.index', $entity)->with('success', $cfg['title'].' dibuat.');
    }

    public function edit(string $entity, $id)
    {
        $cfg = $this->entity($entity);
        $model = $cfg['model'];
        $item = $model::findOrFail($id);

        return view('masterdata.edit', compact('cfg', 'item', 'entity'));
    }

    public function update(Request $request, string $entity, $id)
    {
        $cfg = $this->entity($entity);
        $model = $cfg['model'];
        $item = $model::findOrFail($id);
        $data = $request->validate($this->rules($cfg, $item));
        $item->update($data);
        AuditLog::record('update', $item, [], $data);

        return redirect()->route('masterdata.index', $entity)->with('success', $cfg['title'].' diperbarui.');
    }

    public function destroy(string $entity, $id)
    {
        $cfg = $this->entity($entity);
        $model = $cfg['model'];
        $item = $model::findOrFail($id);
        AuditLog::record('delete', $item, $item->toArray());
        $item->delete();

        return back()->with('success', $cfg['title'].' dihapus.');
    }

    protected function rules(array $cfg, $item = null): array
    {
        $rules = [];
        foreach ($cfg['fields'] as $field => $def) {
            $rules[$field] = $def['rules'] ?? ['nullable', 'string'];
            if (! empty($def['unique'])) {
                $rules[$field][] = 'unique:'.(new $cfg['model'])->getTable().','.$field.($item ? ','.$item->id : '');
            }
        }

        return $rules;
    }
}
