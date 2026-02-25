<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $query = Media::latest();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            match ($request->type) {
                'image'    => $query->where('mime_type', 'like', 'image/%'),
                'video'    => $query->where('mime_type', 'like', 'video/%'),
                'document' => $query->where(function ($q) {
                    $q->where('mime_type', 'like', 'application/%')
                      ->orWhere('mime_type', 'like', 'text/%');
                }),
                default    => null,
            };
        }

        $medias = $query->get()->map(function ($m) {
            $m->url = Storage::disk($m->disk)->url($m->file_path);
            return $m;
        });

        return view('media.index', compact('medias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:51200',
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');

        $media = Media::create([
            'name'      => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size'      => $file->getSize(),
            'disk'      => 'public',
        ]);

        $media->url = Storage::disk($media->disk)->url($media->file_path);

        return response()->json([
            'success' => true,
            'media'   => $media,
        ]);
    }

    public function update(Request $request, Media $media)
    {
        $validated = $request->validate([
            'alt_text'    => 'nullable|string|max:500',
            'caption'     => 'nullable|string|max:500',
            'description' => 'nullable|string',
        ]);

        $media->update($validated);

        return response()->json(['success' => true, 'media' => $media]);
    }

    public function destroy(Media $media)
    {
        if (Storage::disk($media->disk)->exists($media->file_path)) {
            Storage::disk($media->disk)->delete($media->file_path);
        }

        $media->delete();

        return response()->json(['success' => true]);
    }
}
