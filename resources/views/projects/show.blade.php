@extends('layouts.app')

@section('title', 'Detail Project')

@section('content')
    <h1 class="h3">{{ $project->title }}</h1>
    <p class="text-muted">Tanggal: {{ $project->created_at->format('d M Y') }}</p>
    <p style="white-space: pre-wrap;">{{ $project->description }}</p>

    <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary">Edit</a>

    <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline"
        onsubmit="return confirm('Yakin ingin menghapus project ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Hapus</button>
    </form>

    <a href="{{ route('projects.index') }}" class="btn btn-secondary">Kembali</a>
@endsection
