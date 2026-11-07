@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <h1 class="h3 mb-4">Projects</h1>

    <div class="row g-4">
        @forelse ($projects as $project)
            <div class="col-md-6 col-lg-4">
                <article class="card h-100 shadow-sm">
                    @if ($project->thumbnail)
                        <img src="{{ asset('storage/' . $project->thumbnail) }}"
                             alt="{{ $project->title }}" class="card-img-top"
                             style="height: 220px; object-fit: cover">
                    @endif
                    <div class="card-body d-flex flex-column">
                        <h2 class="h5 card-title">{{ $project->title }}</h2>
                        <p class="card-text text-muted">
                            {{ \Illuminate\Support\Str::limit($project->description, 120) }}
                        </p>
                        <p class="small text-muted">{{ $project->images_count }} gambar gallery</p>
                        <a href="{{ route('portfolio.projects.show', $project) }}"
                           class="btn btn-primary mt-auto align-self-start">Lihat Project</a>
                    </div>
                </article>
            </div>
        @empty
            <p class="text-muted">Belum ada project.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>
@endsection
