<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use App\Models\LabGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class RestoreController extends Controller
{
    public function restore($type, $id)
    {
        if ($type === 'lab') {
            $lab = Lab::findOrFail($id);

            $path = $lab->group
                ? 'labs/' . $lab->group->fullSlugPath() . '/' . Str::slug($lab->name) . '.json'
                : 'labs/' . Str::slug($lab->name) . '.json';

            $defaultData = [
                'nodes' => [],
                'connections' => [],
                'power' => null,
                'name' => $lab->name,
                'author' => $lab->author,
                'description' => $lab->description,
            ];

            Storage::put($path, json_encode($defaultData, JSON_PRETTY_PRINT));

            return response()->json(['success' => true]);
        }

        if ($type === 'folder') {
            $folder = LabGroup::findOrFail($id);
            $path = storage_path('app/labs/' . $folder->fullSlugPath());

            if (!file_exists($path)) {
                mkdir($path, 0775, true);
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid type']);
    }

    public function deleteOnlyDb($type, $id)
    {
        if ($type === 'lab') {
            $lab = Lab::findOrFail($id);
            $lab->delete();
            return response()->json(['success' => true]);
        }

        if ($type === 'folder') {
            $folder = LabGroup::findOrFail($id);
            $folder->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid type']);
    }
}
