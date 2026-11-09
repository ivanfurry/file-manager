<?php

namespace App\Http\Controllers;
use App\Models\File;
use App\Models\Folder;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller {
    public function index(Request $request) {
        $query = File::where('user_id', 1);
        
        if ($request->folder_id) {
            $query->where('folder_id', $request->folder_id);
        } else {
            $query->whereNull('folder_id');
        }

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $files = $query->with('tags')->get();
        $folders = Folder::where('user_id', 1)->where('parent_id', $request->folder_id)->get();
        $currentFolder = $request->folder_id ? Folder::find($request->folder_id) : null;
        $allFolders = Folder::where('user_id', 1)->get();

        return view('dashboard', compact('files', 'folders', 'currentFolder', 'allFolders'));
    }

    public function store(Request $request) {
    $request->validate(['file' => 'required|file|mimes:jpg,jpeg,png,pdf,txt,docx,gif|max:5120', 'folder_id' => 'nullable|exists:folders,id']);
        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        File::create([
            'user_id' => 1, 
            'folder_id' => $request->folder_id, 
            'name' => $file->getClientOriginalName(),
            'file_path' => $path, 
            'mime_type' => $file->getMimeType(), 
            'size' => $file->getSize(),
        ]);
        return back()->with('success', 'Файл успешно загружен.');
    }

    public function download(File $file) {
        return response()->download(storage_path('app/public/' . $file->file_path), $file->name);
    }

    public function preview(File $file) {
        if (!str_starts_with($file->mime_type, 'image/')) return back()->with('error', 'Предпросмотр доступен только для изображений.');
        return response()->file(storage_path('app/public/' . $file->file_path));
    }

    public function destroy(File $file) {
        Storage::disk('public')->delete($file->file_path);
        $file->delete();
        return back()->with('success', 'Файл удален.');
    }

    public function move(Request $request, File $file) {
        $request->validate(['folder_id' => 'nullable|exists:folders,id']);
        $file->update(['folder_id' => $request->folder_id]);
        return back()->with('success', 'Файл перемещен.');
    }

    public function addTag(Request $request, File $file) {
        $request->validate(['tag_name' => 'required|string|max:255']);
        $tag = Tag::firstOrCreate(['user_id' => 1, 'name' => $request->tag_name], ['name' => $request->tag_name]);
        if (!$file->tags()->where('tag_id', $tag->id)->exists()) {
            $file->tags()->attach($tag->id);
        }
        return back()->with('success', 'Тег добавлен.');
    }

    public function removeTag(File $file, Tag $tag) {
        $file->tags()->detach($tag->id);
        return back()->with('success', 'Тег удален.');
    }
}