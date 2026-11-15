<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use App\Models\Technology;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function projects(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'category' => ['nullable', 'exists:categories,slug'],
            'technology' => ['nullable', 'exists:technologies,slug'],
            'sort' => ['nullable', 'in:terbaru,terlama,az'],
        ]);

        $search = trim($filters['search'] ?? '');

        $query = Project::with(['category', 'technologies'])
            ->withCount('images')
            ->where('status', 'published')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['category'] ?? null, function ($query, $slug) {
                $query->whereHas('category', function ($categoryQuery) use ($slug) {
                    $categoryQuery->where('slug', $slug);
                });
            })
            ->when($filters['technology'] ?? null, function ($query, $slug) {
                $query->whereHas('technologies', function ($technologyQuery) use ($slug) {
                    $technologyQuery->where('slug', $slug);
                });
            });

        switch ($filters['sort'] ?? 'terbaru') {
            case 'terlama':
                $query->orderByRaw('project_date IS NULL')
                    ->orderBy('project_date')
                    ->orderBy('created_at')
                    ->orderBy('id');
                break;
            case 'az':
                $query->orderBy('title')->orderBy('id');
                break;
            default:
                $query->orderByRaw('project_date IS NULL')
                    ->orderByDesc('project_date')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id');
        }

        $projects = $query->paginate(9)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $technologies = Technology::orderBy('name')->get();

        return view('projects', compact(
            'projects',
            'categories',
            'technologies',
            'search'
        ));
    }

    public function show(Project $project): View
    {
        abort_if($project->status !== 'published', 404);

        $project->load(['category', 'technologies', 'images']);

        return view('portfolio.project', compact('project'));
    }
}
