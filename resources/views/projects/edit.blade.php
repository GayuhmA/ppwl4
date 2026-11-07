@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
    <h1 class="h3 mb-3">Edit Project</h1>

    <form action="{{ route('projects.update', $project) }}" method="POST"
          enctype="multipart/form-data" class="col-md-8">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="title" class="form-label">Judul Project</label>
            <input type="text" name="title" id="title"
                   class="form-control @error('title') is-invalid @enderror"
                   value="{{ old('title', $project->title) }}" minlength="5" maxlength="200" required>
            @error('title')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" id="description" rows="5" minlength="10" required
                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $project->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="category_id" class="form-label">Kategori</label>
                <select name="category_id" id="category_id"
                        class="form-select @error('category_id') is-invalid @enderror">
                    <option value="">Tanpa kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                                @selected(old('category_id', $project->category_id) == $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                    <option value="draft" @selected(old('status', $project->status) === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $project->status) === 'published')>Published</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="mb-3">
            <label for="thumbnail" class="form-label">Thumbnail</label>
            @if ($project->thumbnail)
                <div class="mb-2">
                    <img src="{{ asset('storage/' . $project->thumbnail) }}"
                         alt="Thumbnail saat ini" class="img-thumbnail" style="max-height:150px">
                    <div class="form-text">Thumbnail saat ini. Upload baru untuk menggantinya.</div>
                </div>
            @endif
            <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
                   class="form-control @error('thumbnail') is-invalid @enderror">
            <div class="form-text">JPG/PNG/WebP, maks 2 MB, min 400×300 px. Kosongkan jika tidak ingin mengganti.</div>
            @error('thumbnail')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection
