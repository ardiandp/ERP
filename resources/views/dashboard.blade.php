@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h4>Dashboard</h4>
<p>Selamat datang, {{ auth()->user()->name }}.</p>
@endsection
