<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function projects(): View
    {
        $projects = Project::withCount('images')
            ->latest()
            ->paginate(9);

        return view('projects', compact('projects'));
    }

    public function show(Project $project): View
    {
        $project->load('images');

        return view('portfolio.project', compact('project'));
    }
}
