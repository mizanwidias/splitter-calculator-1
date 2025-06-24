<?php

namespace App\Http\Controllers;

use App\Models\LabGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LabGroupController extends Controller
{
    /**
     * Tampilkan daftar folder (opsional).
     */
    public function index()
    {
        $groups = LabGroup::all();
        return view('lab-group.index', compact('groups'));
    }

    /**
     * Tampilkan form pembuatan folder (opsional).
     */
    public function create(Request $request)
    {
        $parentId = $request->get('parent');
        $parentFolder = $parentId ? LabGroup::find($parentId) : null;

        return view('lab-group.create', [
            'parentGroups' => LabGroup::all(),
            'selectedGroup' => $parentId,
            'breadcrumbs' => app(LabController::class)->getBreadcrumbs($parentFolder),
        ]);
    }

    public function rename(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $folder = LabGroup::findOrFail($id);

        $newName = $request->name;
        $newSlug = Str::slug($newName);

        // Cek apakah ada folder lain di parent yang punya slug sama
        $exists = LabGroup::where('parent_id', $folder->parent_id)
            ->where('slug', $newSlug)
            ->where('id', '!=', $folder->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Folder dengan nama ini sudah ada dalam direktori yang sama.',
            ]);
        }

        // Hitung path lama dan path baru
        $oldPath = storage_path('app/labs/' . $folder->fullSlugPath());
        $folder->slug = $newSlug; // update slug sementara supaya fullSlugPath() hitung path baru
        $newPath = storage_path('app/labs/' . $folder->fullSlugPath());

        // Rename folder fisik kalau path berubah
        if ($oldPath !== $newPath && file_exists($oldPath)) {
            rename($oldPath, $newPath);
        }

        // Simpan perubahan nama dan slug
        $folder->update([
            'name' => $newName,
            'slug' => $newSlug,
        ]);

        return response()->json(['success' => true]);
    }


    public function checkContents($id)
    {
        $folder = LabGroup::with('labs')->findOrFail($id);
        return response()->json([
            'hasLabs' => $folder->labs->isNotEmpty(),
            'hasFolders' => $folder->children->isNotEmpty(),
            'totalLabs' => $folder->labs->count(),
            'totalFolders' => $folder->children->count()
        ]);
    }

    /**
     * Simpan folder baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:lab_groups,id',
        ]);

        $slug = Str::slug($request->name);

        // 🔒 Cek duplikat folder dalam folder yang sama
        $existingFolder = LabGroup::where('slug', $slug)
            ->where('parent_id', $request->parent_id)
            ->first();

        if ($existingFolder) {
            return back()->withErrors(['name' => 'Folder dengan nama ini sudah ada di folder ini.'])->withInput();
        }

        if (LabGroup::where('slug', $slug)->exists()) {
            return back()->withErrors(['name' => 'Folder dengan nama ini sudah ada.'])->withInput();
        }

        $group = LabGroup::create([
            'name' => $request->name,
            'slug' => $slug,
            'parent_id' => $request->parent_id,
        ]);

        $folderPath = storage_path('app/labs/' . $group->fullSlugPath());

        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0775, true);
        }

        return redirect()->route('lab', ['group' => $group->id])
            ->with('success', 'Folder berhasil dibuat!');
    }

    /**
     * Update nama folder.
     */
    public function update(Request $request, $id) {}

    /**
     * Hapus folder.
     */

    public function destroy($id)
    {
        $group = LabGroup::with(['children', 'labs'])->findOrFail($id);

        DB::transaction(function () use ($group) {
            // 🔁 Recursive delete semua child folder
            $this->deleteGroupRecursively($group);

            // 🧹 Hapus folder fisik
            $folderPath = storage_path('app/labs/' . $group->fullSlugPath());
            if (file_exists($folderPath)) {
                File::deleteDirectory($folderPath);
            }

            // ❌ Delete folder utama
            $group->delete();
        });

        return redirect()->route('lab')->with('success', 'Folder dan isinya berhasil dihapus!');
    }

    private function deleteGroupRecursively($group)
    {
        foreach ($group->children as $child) {
            $this->deleteGroupRecursively($child);
            $child->delete();
        }

        // Hapus semua lab di folder ini
        foreach ($group->labs as $lab) {
            // 🔥 Hapus file JSON fisik-nya juga
            $jsonPath = 'labs/' . $group->fullSlugPath() . '/' . Str::slug($lab->name) . '.json';
            if (Storage::exists($jsonPath)) {
                Storage::delete($jsonPath);
            }

            $lab->delete();
        }
    }
}
