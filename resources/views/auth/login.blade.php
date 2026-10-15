@extends('layouts.auth')
@section('title', 'Login')
@section('content')
<div id="loginform">
    <div class="text-center p-t-20 p-b-20">
        <span class="db"><img src="{{ asset('theme/matrix-admin/assets/images/logo-icon.png') }}" alt="logo" /></span>
        <span class="text-white font-weight-bold d-block p-t-10">ERP Dian Mustika</span>
    </div>
    @if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form class="form-horizontal m-t-20" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="row p-b-30">
            <div class="col-12">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-success text-white"><i class="ti-user"></i></span>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" placeholder="Email" required autofocus>
                </div>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-warning text-white"><i class="ti-pencil"></i></span>
                    </div>
                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Password" required>
                </div>
                <div class="form-check text-white">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
            </div>
        </div>
        <div class="row border-top border-secondary">
            <div class="col-12">
                <div class="form-group">
                    <div class="p-t-20">
                        <button class="btn btn-success float-right" type="submit">Login</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
