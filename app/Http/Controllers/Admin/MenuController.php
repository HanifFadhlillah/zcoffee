<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        return view('admin.menus.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'price'       => 'required|integer|min:1000|max:999999',
            'category'    => 'required|in:espresso,manual,noncoffee,maincourse,snack',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('menus', 'public');
        }

        $data['is_active'] ??= true;

        Menu::create($data);

        return redirect()
            ->route('manage.menus.index')
            ->with('success', "Menu '{$data['name']}' berhasil ditambahkan.");
    }

    public function edit(Menu $menu)
    {
        return view('admin.menus.edit', compact('menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'price'       => 'required|integer|min:1000|max:999999',
            'category'    => 'required|in:espresso,manual,noncoffee,maincourse,snack',
            'is_active'   => 'boolean',
            'sort_order'  => 'integer|min:0',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $data['image'] = $request->file('image')
                ->store('menus', 'public');
        }

        $data['is_active'] = $request->boolean('is_active');

        $menu->update($data);

        return redirect()
            ->route('manage.menus.index')
            ->with('success', "Menu '{$menu->name}' berhasil diperbarui.");
    }

    public function destroy(Menu $menu)
    {
        // Cegah hapus menu yang sudah ada order
        if ($menu->orderItems()->exists()) {
            return back()->with('error',
                "Menu '{$menu->name}' tidak bisa dihapus karena sudah ada transaksi. Non-aktifkan saja."
            );
        }

        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }

        $name = $menu->name;
        $menu->delete();

        return redirect()
            ->route('manage.menus.index')
            ->with('success', "Menu '{$name}' berhasil dihapus.");
    }

    /**
     * Toggle aktif/non-aktif menu via AJAX.
     */
    public function toggleActive(Menu $menu)
    {
        $menu->update(['is_active' => !$menu->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $menu->is_active,
        ]);
    }
}
