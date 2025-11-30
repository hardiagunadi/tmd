{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 420px;">
    <h4 class="mb-3">Masuk</h4>

    @if($errors->any())
        <div class="alert alert-danger">Email atau password salah.</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="card p-3 shadow-sm">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Username (Email)</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="mt-3 text-muted" style="font-size: 0.9rem;">
        <div>Default username: <strong>admin@admin.com</strong></div>
        <div>Default password: <strong>admin</strong></div>
    </div>
</div>
@endsection
