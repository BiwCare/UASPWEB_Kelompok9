<?php

namespace App\Http\Controllers;

use App\Models\KategoriPeralatan;
use App\Models\Peralatan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home');
    }

    public function catalog(Request $request): View
    {
        $categories = KategoriPeralatan::query()
            ->where('aktif', true)
            ->withCount('peralatan')
            ->orderBy('nama')
            ->get();

        $equipmentQuery = Peralatan::query()
            ->with('kategori')
            ->withCount(['units as unit_tersedia_count' => fn ($query) => $query->where('status', 'tersedia')])
            ->where('aktif', true)
            ->latest();

        if ($request->filled('kategori')) {
            $equipmentQuery->whereHas('kategori', fn ($query) => $query->where('slug', $request->string('kategori')->toString()));
        }

        if ($request->filled('cari')) {
            $search = $request->string('cari')->trim()->toString();
            $equipmentQuery->where(fn ($query) => $query
                ->where('nama', 'like', "%{$search}%")
                ->orWhere('merek', 'like', "%{$search}%"));
        }

        return view('catalog', [
            'categories' => $categories,
            'equipment' => $equipmentQuery->paginate(9)->withQueryString(),
        ]);
    }

    public function show(Peralatan $peralatan): View
    {
        abort_unless($peralatan->aktif, 404);

        $peralatan->load('kategori')->loadCount([
            'units as unit_tersedia_count' => fn ($query) => $query->where('status', 'tersedia'),
        ]);

        return view('equipment.show', [
            'equipment' => $peralatan,
        ]);
    }
}
