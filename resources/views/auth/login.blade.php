@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <h4 class="mb-3">Login</h4>
        <form method="POST" action="{{ route('login') }}" class="card card-body">
            @csrf
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-check mb-3">
                <input type="checkbox" name="remember" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <button class="btn btn-primary">Login</button>
        </form>
    </div>
</div>
@endsection
