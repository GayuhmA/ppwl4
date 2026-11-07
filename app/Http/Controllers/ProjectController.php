<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:200',
            'per_page' => 'nullable|integer|in:5,10,25',
        ]);
        $search = trim($validated['search'] ?? '');
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Project::with('category');
        if ($search !== '') {
            $query->where('title', 'like', '%'.$search.'%');
        }

        $projects = $query->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($perPage)->withQueryString();

        return view('projects.index', compact('projects', 'search', 'perPage'));
    }

    public function create()
    {
        return view('projects.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:5|max:200',
            'description' => 'required|string|min:10',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|in:published,draft',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=400,min_height=300',
        ]);

        $validated['status'] ??= 'draft';

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')
                ->store('projects/thumbnails', 'public');
        }

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project berhasil ditambahkan.');
    }

    public function show(Project $project)
    {
        $project->load(['category', 'images']);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', [
            'project' => $project,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:5|max:200',
            'description' => 'required|string|min:10',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|in:published,draft',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=400,min_height=300',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($project->thumbnail) {
                Storage::disk('public')->delete($project->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')
                ->store('projects/thumbnails', 'public');
        }

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        if ($project->thumbnail) {
            Storage::disk('public')->delete($project->thumbnail);
        }

        foreach ($project->images as $image) {
            Storage::disk('public')->delete($image->image);
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project berhasil dihapus.');
    }
}
