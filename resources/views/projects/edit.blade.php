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
            <label for="short_description" class="form-label">Ringkasan singkat</label>
            <textarea name="short_description" id="short_description" rows="2" maxlength="255"
                      class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description', $project->short_description) }}</textarea>
            <div class="form-text">Opsional, maksimal 255 karakter. Cocok untuk ringkasan di kartu project.</div>
            @error('short_description')
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
                <label for="project_date" class="form-label">Tanggal project</label>
                <input type="date" name="project_date" id="project_date"
                       class="form-control @error('project_date') is-invalid @enderror"
                       value="{{ old('project_date', $project->project_date) }}">
                @error('project_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3 d-flex align-items-end">
                <div class="form-check mb-2">
                    <input type="hidden" name="is_featured" value="0">
                    <input class="form-check-input @error('is_featured') is-invalid @enderror"
                           type="checkbox" name="is_featured" id="is_featured" value="1"
                           @checked(old('is_featured', $project->is_featured))>
                    <label class="form-check-label" for="is_featured">Project unggulan</label>
                    @error('is_featured')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="project_url" class="form-label">URL project</label>
                <input type="url" name="project_url" id="project_url"
                       class="form-control @error('project_url') is-invalid @enderror"
                       value="{{ old('project_url', $project->project_url) }}" maxlength="255" placeholder="https://...">
                @error('project_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="repository_url" class="form-label">URL repository</label>
                <input type="url" name="repository_url" id="repository_url"
                       class="form-control @error('repository_url') is-invalid @enderror"
                       value="{{ old('repository_url', $project->repository_url) }}" maxlength="255" placeholder="https://...">
                @error('repository_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
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

        <fieldset class="mb-3">
            <legend class="form-label">Teknologi yang digunakan</legend>
            @forelse ($technologies as $technology)
                <div class="form-check">
                    <input class="form-check-input @error('technologies') is-invalid @enderror"
                           type="checkbox" name="technologies[]"
                           id="technology-{{ $technology->id }}" value="{{ $technology->id }}"
                           @checked(in_array(
                               $technology->id,
                               (array) old('technologies', $project->technologies->pluck('id')->all())
                           ))>
                    <label class="form-check-label" for="technology-{{ $technology->id }}">
                        {{ $technology->name }}
                    </label>
                </div>
            @empty
                <p class="text-muted small mb-0">Belum ada pilihan teknologi. Tambahkan data teknologi terlebih dahulu.</p>
            @endforelse
            @error('technologies')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
            @error('technologies.*')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </fieldset>

        <div class="mb-3">
            <label for="new_technology" class="form-label">Tambah teknologi baru</label>
            <input type="text" name="new_technology" id="new_technology"
                   class="form-control @error('new_technology') is-invalid @enderror"
                   value="{{ old('new_technology') }}" maxlength="100"
                   placeholder="Contoh: Laravel, Vue, MySQL">
            <div class="form-text">Opsional. Teknologi ini akan ditambahkan ke daftar dan dikaitkan dengan project setelah disimpan.</div>
            @error('new_technology')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
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
