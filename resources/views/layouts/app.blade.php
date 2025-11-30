{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Aplikasi Tagihan Internet' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('tagihan.index') }}">
            PT Tunas Media Data
        </a>
        @auth
            <div class="navbar-nav me-auto">
                <a class="nav-link" href="{{ route('tagihan.index') }}">Daftar Tagihan</a>
                <a class="nav-link" href="{{ route('tagihan.rekap') }}">Rekap Penarikan</a>
            </div>
        @endauth
        <div class="d-flex align-items-center gap-2">
            @auth
                <a href="{{ route('rekap-keuangan.index') }}" class="btn btn-sm btn-outline-light">Rekap Keuangan</a>
                <a href="{{ route('pendapatan-lain.index') }}" class="btn btn-sm btn-outline-light">Pendapatan Lain</a>
                <a href="{{ route('penarikan.index') }}" class="btn btn-sm btn-outline-light">Rekap Penarikan</a>
            @endauth
            @auth
                <a href="{{ route('credentials.edit') }}" class="btn btn-sm btn-outline-light">Ganti Kredensial</a>
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light">Login</a>
            @endauth
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
