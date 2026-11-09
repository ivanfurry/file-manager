<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function store(Request $request) {
        $request->validate(['name' => 'required|string|max:255', 'parent_id' => 'nullable|exists:folders,id']);
        Folder::create(['user_id' => 1, 'parent_id' => $request->parent_id, 'name' => $request->name]);
        return back()->with('success', 'Папка создана.');
    }

    public function update(Request $request, Folder $folder) {
        $request->validate(['name' => 'required|string|max:255']);
        $folder->update(['name' => $request->name]);
        return back()->with('success', 'Папка переименована.');
    }

    public function destroy(Folder $folder) {
        $folder->delete();
        return back()->with('success', 'Папка удалена.');
    }
}
