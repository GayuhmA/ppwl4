@extends('layouts.app')

@section('title', 'Edit Skill')

@section('content')
    <h1 class="h3 mb-3">Edit Skill</h1>

    <form action="{{ route('skills.update', $skill) }}" method="POST" class="col-md-6">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nama Skill</label>
            <input type="text" name="name" id="name" required maxlength="100"
                   value="{{ old('name', $skill->name) }}"
                   class="form-control @error('name') is-invalid @enderror">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="level" class="form-label">Level (0–100)</label>
            <input type="number" name="level" id="level" required min="0" max="100"
                   value="{{ old('level', $skill->level) }}"
                   class="form-control @error('level') is-invalid @enderror">
            @error('level')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('skills.index') }}" class="btn btn-secondary">Batal</a>
    </form>
@endsection
