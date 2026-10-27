@extends('layouts.app')

@section('title', 'Daftar Project')

@section('content')
    <h1 class="h3 mb-3">Daftar Project</h1>
    <a href="{{ route('projects.create') }}" class="btn btn-primary mb-3">Tambah Project</a>

    <form action="{{ route('projects.index') }}" method="GET" class="row g-2 mb-3">
        <div class="col-sm-6">
            <label for="search" class="form-label">Cari judul</label>
            <input type="search" name="search" id="search" class="form-control"
                value="{{ old('search', $search) }}" maxlength="200">
            @error('search')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-sm-3">
            <label for="per_page" class="form-label">Data per halaman</label>
            <select name="per_page" id="per_page" class="form-select">
                @foreach ([5, 10, 25] as $size)
                    <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }}</option>
                @endforeach
            </select>
            @error('per_page')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-sm-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="{{ route('projects.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Judul Project</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                    <tr>
                        <td>{{ $projects->firstItem() + $loop->index }}</td>
                        <td>{{ $project->title }}</td>
                        <td><a href="{{ route('projects.show', $project) }}">Lihat Detail</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            @if ($projects->total() > 0)
                                Tidak ada data pada halaman ini.
                            @elseif ($search !== '')
                                Tidak ada project yang cocok.
                            @else
                                Belum ada data project.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p>
        Halaman: {{ $projects->currentPage() }} dari {{ $projects->lastPage() }}<br>
        Jumlah Data: {{ $projects->total() }}<br>
        Data Per Halaman: {{ $projects->perPage() }}
    </p>

    {{ $projects->links() }}
@endsection
