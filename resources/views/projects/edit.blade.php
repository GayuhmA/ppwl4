@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
    <h1 class="h3 mb-3">Edit Project</h1>

    <form action="{{ route('projects.update', $project) }}" method="POST" class="col-md-8">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="title" class="form-label">Judul Project</label>
            <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror"
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

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('projects.show', $project) }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection
