<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:200',
            'per_page' => 'nullable|integer|in:5,10,25',
            'status' => 'nullable|in:draft,published',
        ]);
        $search = trim($validated['search'] ?? '');
        $perPage = (int) ($validated['per_page'] ?? 10);
        $status = $validated['status'] ?? '';

        $query = Project::with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%');
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            });

        $projects = $query->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($perPage)->withQueryString();

        return view('projects.index', compact('projects', 'search', 'perPage', 'status'));
    }

    public function create()
    {
        return view('projects.create', [
            'categories' => Category::orderBy('name')->get(),
            'technologies' => Technology::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['nullable', 'in:published,draft'],
            'project_date' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'project_url' => ['nullable', 'url', 'max:255'],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'technologies' => ['nullable', 'array'],
            'technologies.*' => ['integer', 'exists:technologies,id'],
            'new_technology' => ['nullable', 'string', 'max:100'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=400,min_height=300'],
        ]);

        $technologyIds = $validated['technologies'] ?? [];
        $newTechnologyName = trim($validated['new_technology'] ?? '');
        unset($validated['technologies']);
        unset($validated['new_technology']);

        if ($newTechnologyName !== '') {
            $technology = Technology::firstOrCreate(
                ['name' => $newTechnologyName],
                ['slug' => $this->uniqueTechnologySlug($newTechnologyName)]
            );
            $technologyIds[] = $technology->id;
        }
        $technologyIds = array_values(array_unique($technologyIds));

        $validated['slug'] = $this->uniqueSlug($validated['title']);
        $validated['status'] ??= 'draft';
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')
                ->store('projects/thumbnails', 'public');
        }

        $project = Project::create($validated);
        $project->technologies()->sync($technologyIds);

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
        $project->load('technologies');

        return view('projects.edit', [
            'project' => $project,
            'categories' => Category::orderBy('name')->get(),
            'technologies' => Technology::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'status' => ['nullable', 'in:published,draft'],
            'project_date' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'project_url' => ['nullable', 'url', 'max:255'],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'technologies' => ['nullable', 'array'],
            'technologies.*' => ['integer', 'exists:technologies,id'],
            'new_technology' => ['nullable', 'string', 'max:100'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=400,min_height=300'],
        ]);

        $technologyIds = $validated['technologies'] ?? [];
        $newTechnologyName = trim($validated['new_technology'] ?? '');
        unset($validated['technologies']);
        unset($validated['new_technology']);

        if ($newTechnologyName !== '') {
            $technology = Technology::firstOrCreate(
                ['name' => $newTechnologyName],
                ['slug' => $this->uniqueTechnologySlug($newTechnologyName)]
            );
            $technologyIds[] = $technology->id;
        }
        $technologyIds = array_values(array_unique($technologyIds));

        $validated['slug'] = $this->uniqueSlug($validated['title'], $project);
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('thumbnail')) {
            if ($project->thumbnail) {
                Storage::disk('public')->delete($project->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')
                ->store('projects/thumbnails', 'public');
        }

        $project->update($validated);
        $project->technologies()->sync($technologyIds);

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

    private function uniqueSlug(string $title, ?Project $project = null): string
    {
        $base = Str::slug($title) ?: 'project';
        $slug = $base;
        $suffix = 2;

        while (
            Project::query()
                ->where('slug', $slug)
                ->when($project, fn ($query) => $query->where('id', '!=', $project->id))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function uniqueTechnologySlug(string $name): string
    {
        $base = Str::slug($name) ?: 'technology';
        $slug = $base;
        $suffix = 2;

        while (Technology::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
