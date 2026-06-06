<?php

namespace App\Http\Controllers\Api;

use App\Models\SavedSearch;
use Illuminate\Http\Request;

class SavedSearchApiController extends BaseApiController
{
    public function index()
    {
        $items = SavedSearch::where('user_id', auth()->id())
            ->latest()
            ->get()
            ->map(function (SavedSearch $item) {
                return $this->mapItem($item);
            });

        return $this->ok($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:120',
            'params' => 'required|array',
        ]);

        $item = SavedSearch::create([
            'user_id' => auth()->id(),
            'label' => $data['label'],
            'params' => $data['params'],
        ]);

        return $this->ok($this->mapItem($item), 'Created', 201);
    }

    public function destroy(int $id)
    {
        $item = SavedSearch::where('user_id', auth()->id())->findOrFail($id);
        $item->delete();

        return $this->ok(null, 'Deleted');
    }

    private function mapItem(SavedSearch $item): array
    {
        return [
            'id' => (string) $item->id,
            'label' => $item->label,
            'params' => $item->params ?? [],
            'createdAt' => optional($item->created_at)->toISOString(),
        ];
    }
}
