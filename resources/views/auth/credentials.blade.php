{{-- resources/views/auth/credentials.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 520px;">
    <h4 class="mb-3">Ganti Username & Password</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">Periksa kembali input yang diberikan.</div>
    @endif

    <form method="POST" action="{{ route('credentials.update') }}" class="card p-3 shadow-sm">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Username (Email)</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            @error('email')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Biarkan kosong jika tidak diganti">
            @error('password')
                <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru">
        </div>

        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>
@endsection
