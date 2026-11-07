@extends('layouts.app')

@section('title', 'Detail Project')

@section('content')
    <h1 class="h3">{{ $project->title }}</h1>
    <p class="text-muted">Tanggal: {{ $project->created_at->format('d M Y') }}</p>
    <p>
        <span class="badge text-bg-light">{{ $project->category?->name ?? 'Tanpa kategori' }}</span>
        <span class="badge {{ $project->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }}">
            {{ ucfirst($project->status) }}
        </span>
    </p>

    @if ($project->thumbnail)
        <img src="{{ asset('storage/' . $project->thumbnail) }}"
             alt="{{ $project->title }}" class="img-fluid rounded mb-3" style="max-height:300px">
    @endif

    <p style="white-space: pre-wrap;">{{ $project->description }}</p>

    <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary">Edit</a>

    <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline"
          onsubmit="return confirm('Yakin ingin menghapus project ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Hapus</button>
    </form>

    <a href="{{ route('projects.index') }}" class="btn btn-secondary">Kembali</a>

    <hr class="my-4">
    <h2 class="h5 mb-3">Gallery</h2>

    <form action="{{ route('projects.gallery.store', $project) }}" method="POST"
          enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="mb-2">
            <label for="images" class="form-label">Upload Gambar (maks total 10, masing-masing maks 4 MB)</label>
            <input type="file" name="images[]" id="images" multiple accept="image/*"
                   class="form-control @error('images') is-invalid @enderror
                          @error('images.*') is-invalid @enderror">
            @error('images')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @error('images.*')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">Format JPG/PNG/WebP, lebar minimal 800 px. Saat ini ada {{ $project->images->count() }} gambar.</div>
        </div>
        <button type="submit" class="btn btn-success btn-sm">Upload Gambar</button>
    </form>

    @if ($project->images->isNotEmpty())
        <div class="row g-3">
            @foreach ($project->images as $img)
                <div class="col-6 col-md-3">
                    <div class="card h-100">
                        <img src="{{ asset('storage/' . $img->image) }}"
                             alt="{{ $img->caption ?: $project->title }}"
                             class="card-img-top" style="height:150px; object-fit:cover">
                        <div class="card-body p-2">
                            <form action="{{ route('projects.gallery.update', [$project, $img]) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <label for="caption-{{ $img->id }}" class="form-label small">Caption</label>
                                <input type="text" name="caption" id="caption-{{ $img->id }}"
                                       value="{{ $img->caption }}" maxlength="255"
                                       class="form-control form-control-sm @error('caption') is-invalid @enderror"
                                       placeholder="Caption gambar (opsional)">
                                @error('caption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <button type="submit" class="btn btn-primary btn-sm mt-2">Simpan Caption</button>
                            </form>
                        </div>
                        <div class="card-footer p-2">
                            <form action="{{ route('projects.gallery.destroy', [$project, $img]) }}"
                                  method="POST"
                                  onsubmit="return confirm('Hapus gambar ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted">Belum ada gambar gallery.</p>
    @endif
@endsection
