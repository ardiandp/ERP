@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Notifikasi</h1>
        <form method="POST" action="{{ route('notifications.readAll') }}">
            @csrf
            <button class="btn btn-outline-secondary btn-sm">Tandai semua dibaca</button>
        </form>
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            @forelse ($notifications as $notification)
                @php($data = $notification->data)
                @php($level = $data['level'] ?? 'secondary')
                <div class="list-group-item d-flex justify-content-between align-items-start {{ $notification->read_at ? '' : 'bg-light' }}">
                    <div>
                        <span class="badge bg-{{ $level }}">{{ $data['type'] ?? 'info' }}</span>
                        <span class="{{ $notification->read_at ? 'text-muted' : 'fw-semibold' }}">
                            {{ $data['message'] ?? '' }}
                        </span>
                        <div class="small text-muted">{{ $notification->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="text-nowrap">
                        @if (! empty($data['url']))
                            <a href="{{ $data['url'] }}" class="btn btn-sm btn-outline-primary">Buka</a>
                        @endif
                        @unless ($notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary">Tandai</button>
                            </form>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="list-group-item text-center text-muted">Belum ada notifikasi.</div>
            @endforelse
        </div>
    </div>

    <div class="mt-3">{{ $notifications->links() }}</div>
@endsection
