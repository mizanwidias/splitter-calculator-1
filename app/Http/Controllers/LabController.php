<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use Illuminate\Http\Request;

class LabController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labs = Lab::paginate(10); // Ambil semua lab

        return view('lab.index', [
            'title' => 'Labs Collection',
            'labs' => $labs,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('lab.create', [
            'title' => 'Make Your Own Lab'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $lab = Lab::create([
            'name' => $request->name,
            'author' => $request->author,
            'description' => $request->description,
        ]);

        // Redirect ke halaman topologi
        return redirect()->route('lab.canvas', $lab->id)->with('success', 'Lab berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lab = Lab::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $lab->update([
            'name' => $request->name,
            'author' => $request->author,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lab berhasil diperbarui!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */ public function destroy(string $id)
    {
        $lab = Lab::findOrFail($id);
        $lab->delete();

        return redirect()->route('lab')->with('success', 'Lab berhasil dihapus!');
    }

    public function topologi(Lab $lab)
    {
        return view('lab.canvas', compact('lab'));
    }
}
