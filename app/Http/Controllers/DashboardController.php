<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\Skill;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard', [
            'totalProjects' => Project::count(),
            'publishedProjects' => Project::where('status', 'published')->count(),
            'draftProjects' => Project::where('status', 'draft')->count(),
            'projectsWithThumbnail' => Project::whereNotNull('thumbnail')->count(),
            'totalGalleryImages' => ProjectImage::count(),
            'topSkill' => Skill::orderByDesc('level')->orderBy('name')->first(),
            'categories' => Category::withCount('projects')->orderBy('name')->get(),
            'latestProjects' => Project::with(['category'])->withCount('images')->latest()->take(5)->get(),
        ]);
    }
}
