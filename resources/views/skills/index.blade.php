@extends('layouts.app')

@section('title', 'Kelola Skills')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Kelola Skills</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Dashboard</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr><th>Nama</th><th>Level</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse ($skills as $skill)
                    <tr>
                        <td>{{ $skill->name }}</td>
                        <td style="min-width: 180px">
                            <div class="progress" role="progressbar" aria-label="Level {{ $skill->name }}"
                                 aria-valuenow="{{ $skill->level }}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar" style="width: {{ $skill->level }}%">{{ $skill->level }}%</div>
                            </div>
                        </td>
                        <td><a href="{{ route('skills.edit', $skill) }}" class="btn btn-primary btn-sm">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="3">Belum ada skill. Jalankan database seeder.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
