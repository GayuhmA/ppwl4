@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <h1 class="h3 mb-4">Dashboard Admin</h1>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Total Projects" :value="$totalProjects" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Published" :value="$publishedProjects" color="text-success" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Draft" :value="$draftProjects" color="text-secondary" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Dengan Thumbnail" :value="$projectsWithThumbnail" color="text-success" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Gambar Gallery" :value="$totalGalleryImages" color="text-warning" />
        </div>
        <div class="col-sm-6 col-xl-3">
            <x-stat-card label="Skill Teratas"
                         :value="$topSkill ? $topSkill->name . ' (' . $topSkill->level . '%)' : 'Belum ada'"
                         color="text-info" />
        </div>
    </div>

    <div class="alert alert-light border mb-4">
        Published + Draft: <strong>{{ $publishedProjects + $draftProjects }}</strong>
        dari <strong>{{ $totalProjects }}</strong> project.
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <h2 class="h5 mb-3">Jumlah Project per Kategori</h2>
            <div class="table-responsive">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr><th>Kategori</th><th>Jumlah Project</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->projects_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2">Belum ada kategori.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <h2 class="h5 mb-3">5 Project Terbaru</h2>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Gallery</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($latestProjects as $project)
                    <tr>
                        <td>{{ $project->title }}</td>
                        <td>{{ $project->category?->name ?? 'Tanpa kategori' }}</td>
                        <td>
                            <span class="badge {{ $project->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ ucfirst($project->status) }}
                            </span>
                        </td>
                        <td>{{ $project->images_count }} gambar</td>
                        <td>{{ $project->created_at->format('d M Y') }}</td>
                        <td>
                            @if (auth()->user()->email === 'admin@example.com')
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-info">Kelola</a>
                            @else
                                <a href="{{ route('portfolio.projects.show', $project) }}" class="btn btn-sm btn-info">Detail</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Belum ada project.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
