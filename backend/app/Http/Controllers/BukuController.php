<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    private function rules(): array
    {
        return [
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'tahun_terbit' => 'required|integer|min:1000|max:' . date('Y'),
            'stok' => 'required|integer|min:0',
        ];
    }

    public function index()
    {
        return response()->json(['data' => Buku::latest()->get()]);
    }

    public function store(Request $request)
    {
        $buku = Buku::create($request->validate($this->rules()));

        return response()->json(['message' => 'Buku dibuat', 'data' => $buku], 201);
    }

    public function show(Buku $buku)
    {
        return response()->json(['data' => $buku]);
    }

    public function update(Request $request, Buku $buku)
    {
        $buku->update($request->validate($this->rules()));

        return response()->json(['message' => 'Buku diubah', 'data' => $buku]);
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();

        return response()->json(['message' => 'Buku dihapus']);
    }
}
