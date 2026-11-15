@extends('layouts.app')

@section('title', 'Projects')

@section('content')
    <h1 class="h3 mb-4">Projects</h1>

    <form method="GET" action="{{ route('portfolio.projects') }}" class="row g-2 mb-4">
        <div class="col-md-4">
            <label for="search" class="form-label">Cari project</label>
            <input type="search" id="search" name="search" class="form-control"
                   value="{{ request('search') }}" maxlength="200">
        </div>
        <div class="col-md-3">
            <label for="category" class="form-label">Kategori</label>
            <select id="category" name="category" class="form-select">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}"
                            @selected(request('category') === $category->slug)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="technology" class="form-label">Teknologi</label>
            <select id="technology" name="technology" class="form-select">
                <option value="">Semua teknologi</option>
                @foreach ($technologies as $technology)
                    <option value="{{ $technology->slug }}"
                            @selected(request('technology') === $technology->slug)>
                        {{ $technology->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="sort" class="form-label">Urutkan</label>
            <select id="sort" name="sort" class="form-select">
                <option value="terbaru" @selected(request('sort', 'terbaru') === 'terbaru')>Terbaru</option>
                <option value="terlama" @selected(request('sort') === 'terlama')>Terlama</option>
                <option value="az" @selected(request('sort') === 'az')>Judul A–Z</option>
            </select>
        </div>
        <div class="col-12 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('portfolio.projects') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <p class="text-muted">{{ $projects->total() }} hasil ditemukan.</p>

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
                            {{ \Illuminate\Support\Str::limit($project->short_description ?: $project->description, 120) }}
                        </p>
                        @if ($project->category)
                            <p class="small mb-1">Kategori: {{ $project->category->name }}</p>
                        @endif
                        @if ($project->technologies->isNotEmpty())
                            <p class="small text-muted">
                                Teknologi: {{ $project->technologies->pluck('name')->join(', ') }}
                            </p>
                        @endif
                        <p class="small text-muted">{{ $project->images_count }} gambar gallery</p>
                        <a href="{{ route('portfolio.projects.show', ['project' => $project->slug]) }}"
                           class="btn btn-primary mt-auto align-self-start">Lihat Project</a>
                    </div>
                </article>
            </div>
        @empty
            <p class="text-muted">Tidak ada project yang cocok.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>
@endsection
