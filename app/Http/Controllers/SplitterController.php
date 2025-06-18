<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SplitterController extends Controller
{

    public function save(Request $request)
    {
        // Simpan ke database atau file
        \Illuminate\Support\Facades\Storage::put('topologi.json', json_encode([
            'data' => $request->topology,
            'total_loss' => $request->total_loss,
            'saved_at' => now()
        ]));

        return response()->json(['status' => 'success']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
