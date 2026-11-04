@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <h1 class="h3 mb-3">Profil Saya</h1>

    <dl class="row">
        <dt class="col-sm-2">Nama</dt>
        <dd class="col-sm-10">{{ auth()->user()->name }}</dd>

        <dt class="col-sm-2">Email</dt>
        <dd class="col-sm-10">{{ auth()->user()->email }}</dd>
    </dl>

    <a href="{{ route('profile.edit') }}" class="btn btn-secondary">
        Edit Profil
    </a>
@endsection
