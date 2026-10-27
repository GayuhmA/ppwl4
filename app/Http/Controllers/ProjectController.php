<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:200',
            'per_page' => 'nullable|integer|in:5,10,25',
        ]);
        $search = trim($validated['search'] ?? '');
        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = Project::query();

        if ($search !== '') {
            $query->where('title', 'like', '%'.$search.'%');
        }

        $projects = $query->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($perPage)->withQueryString();

        return view('projects.index', compact('projects', 'search', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('projects.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:5|max:200',
            'description' => 'required|string|min:10',
        ]);
        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:5|max:200',
            'description' => 'required|string|min:10',
        ]);
        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project berhasil dihapus.');
    }
}
