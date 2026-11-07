<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProjectGalleryController extends Controller
{
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'max:10'],
            'images.*' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
                'dimensions:min_width=800',
            ],
        ]);

        $files = $validated['images'];

        if ($project->images()->count() + count($files) > 10) {
            throw ValidationException::withMessages([
                'images' => 'Total gambar gallery untuk satu project maksimal 10.',
            ]);
        }

        $sortOrder = ((int) $project->images()->max('sort_order')) + 1;

        foreach ($files as $file) {
            $project->images()->create([
                'image' => $file->store('projects/gallery', 'public'),
                'sort_order' => $sortOrder++,
            ]);
        }

        return back()->with('success', 'Gambar gallery berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, ProjectImage $image): RedirectResponse
    {
        abort_if($image->project_id !== $project->id, 404);

        $validated = $request->validate([
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $image->update($validated);

        return back()->with('success', 'Caption gambar berhasil diperbarui.');
    }

    public function destroy(Project $project, ProjectImage $image): RedirectResponse
    {
        abort_if($image->project_id !== $project->id, 404);

        Storage::disk('public')->delete($image->image);
        $image->delete();

        return back()->with('success', 'Gambar gallery berhasil dihapus.');
    }
}
