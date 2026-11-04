<nav class="border-bottom py-3">
    <div class="container d-flex flex-wrap gap-3">
        <a href="{{ route('home') }}">Home</a>
        <a href="{{ route('about') }}">About</a>
        <a href="{{ route('education') }}">Education</a>

        @auth
            @if (auth()->user()->email === 'admin@example.com')
                <a href="{{ route('projects.index') }}">Projects</a>
            @endif

            <a href="{{ route('profile-saya') }}">Profil Saya</a>

            <span class="ms-auto">Halo, {{ auth()->user()->name }}</span>

            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="ms-auto">Login</a>
        @endauth
    </div>
</nav>
