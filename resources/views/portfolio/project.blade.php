@extends('layouts.app')

@section('title', $project->title)

@section('content')
    <article>
        <a href="{{ route('portfolio.projects') }}" class="btn btn-outline-secondary btn-sm mb-3">Kembali ke Projects</a>

        <h1 class="h2">{{ $project->title }}</h1>
        <p class="text-muted">
            {{ $project->project_date ?: $project->created_at->format('d M Y') }}
            @if ($project->is_featured)
                <span class="badge text-bg-warning ms-2">Project unggulan</span>
            @endif
        </p>

        @if ($project->category || $project->technologies->isNotEmpty())
            <div class="d-flex flex-wrap gap-2 mb-3">
                @if ($project->category)
                    <span class="badge text-bg-light">{{ $project->category->name }}</span>
                @endif
                @foreach ($project->technologies as $technology)
                    <span class="badge text-bg-secondary">{{ $technology->name }}</span>
                @endforeach
            </div>
        @endif

        @if ($project->short_description)
            <p class="lead">{{ $project->short_description }}</p>
        @endif

        @if ($project->thumbnail)
            <img src="{{ asset('storage/' . $project->thumbnail) }}"
                 alt="{{ $project->title }}" class="img-fluid rounded mb-4"
                 style="max-height: 480px; width: 100%; object-fit: cover">
        @endif

        <p style="white-space: pre-wrap">{{ $project->description }}</p>

        <div class="d-flex gap-2 mb-4">
            @if ($project->project_url)
                <a href="{{ $project->project_url }}" class="btn btn-primary"
                   target="_blank" rel="noopener noreferrer">Buka Project</a>
            @endif
            @if ($project->repository_url)
                <a href="{{ $project->repository_url }}" class="btn btn-outline-secondary"
                   target="_blank" rel="noopener noreferrer">Lihat Repository</a>
            @endif
        </div>

        @if ($project->images->isNotEmpty())
            <h2 class="h4 mt-5 mb-3">Gallery</h2>
            <div class="row g-3">
                @foreach ($project->images as $image)
                    <figure class="col-sm-6 col-lg-4">
                        <img src="{{ asset('storage/' . $image->image) }}"
                             alt="{{ $image->caption ?: $project->title }}"
                             class="img-fluid rounded w-100"
                             style="height: 240px; object-fit: cover">
                        @if ($image->caption)
                            <figcaption class="text-muted small mt-1">{{ $image->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @endif
    </article>
@endsection
