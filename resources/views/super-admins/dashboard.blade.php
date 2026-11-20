@extends('layouts.super-admin')
@section('title', 'Dashboard')
@section('page-title', 'Platform Overview')
@section('content')
<div class="page-header">
    <div>
        <h2>Dashboard</h2>
        <p>Welcome back, {{ auth('super_admin')->user()?->name ?? 'Super Admin' }}. Here is your platform overview.</p>
    </div>
</div>

@php
    $stats = [
        ['label' => 'Total Players', 'value' => $totalPlayers, 'symbol' => 'P'],
        ['label' => 'Total Owners', 'value' => $totalOwners, 'symbol' => 'O'],
        ['label' => 'Pending Owners', 'value' => $pendingOwners, 'symbol' => '!'],
        ['label' => 'Approved Courts', 'value' => $activeCourts, 'symbol' => 'C'],
        ['label' => 'Public Games', 'value' => $publicGames, 'symbol' => 'G'],
        ['label' => 'Total Bookings', 'value' => $totalBookings, 'symbol' => 'B'],
        ['label' => 'Total Sports', 'value' => $totalSports ?? 0, 'symbol' => 'S'],
    ];
@endphp
<div class="stats-grid">
    @foreach ($stats as $stat)
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true" style="font-weight:800;font-size:22px">{{ $stat['symbol'] }}</div>
            <div class="stat-info">
                <span>{{ $stat['label'] }}</span>
                <strong>{{ number_format($stat['value']) }}</strong>
            </div>
        </div>
    @endforeach
</div>

<div class="admin-card" style="margin-bottom:24px">
    <div class="admin-card-header">
        <h3>Recent Owners</h3>
        <a class="admin-button admin-button-outline" href="{{ route('super_admin.admins.index') }}">View All</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Owner</th><th>Business</th><th>Email</th><th>Status</th></tr></thead>
            <tbody>
                @forelse (($recentOwners ?? collect()) as $owner)
                    <tr>
                        <td>{{ $owner->name }}</td>
                        <td>{{ $owner->business_name ?? '—' }}</td>
                        <td>{{ $owner->email }}</td>
                        <td><span class="badge {{ $owner->status === 'active' ? 'badge-success' : ($owner->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">{{ ucfirst($owner->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4">No owners registered yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card-header"><h3>Recent Courts</h3></div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Court</th><th>Owner</th><th>Sport</th><th>Status</th></tr></thead>
            <tbody>
                @forelse (($recentCourts ?? collect()) as $court)
                    <tr>
                        <td>{{ $court->name }}</td>
                        <td>{{ $court->admin?->name ?? '—' }}</td>
                        <td>{{ $court->sports?->name ?? '—' }}</td>
                        <td><span class="badge {{ $court->status === 'approved' ? 'badge-success' : ($court->status === 'pending' ? 'badge-warning' : 'badge-danger') }}">{{ ucfirst($court->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4">No courts found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
