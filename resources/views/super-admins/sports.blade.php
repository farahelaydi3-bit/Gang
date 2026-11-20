@extends('layouts.super-admin')
@section('title', 'Manage Sports')
@section('page-title', 'Sports Management')
@section('content')
<div class="page-header">
    <div>
        <h2>Manage Sports</h2>
        <p>Add, rename, or remove sports available across CourtBook.</p>
    </div>
    <a href="{{ route('super_admin.dashboard') }}" class="admin-button admin-button-outline">Back to Dashboard</a>
</div>

@if ($errors->any())
    <div class="admin-card" role="alert" style="margin-bottom:20px;border-color:var(--admin-danger);color:var(--admin-danger)">
        @foreach ($errors->all() as $error)
            <p style="margin:0 0 6px">{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="admin-card" style="margin-bottom:24px">
    <div class="admin-card-header"><h3>Add New Sport</h3></div>
    <form action="{{ route('super_admin.sports.store') }}" method="POST" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
        @csrf
        <div style="flex:1;min-width:220px">
            <label class="form-label" for="sport-name">Sport Name</label>
            <input id="sport-name" type="text" name="name" class="form-control" maxlength="255" value="{{ old('name') }}" placeholder="e.g. Padel" required>
        </div>
        <button class="admin-button" type="submit">Add Sport</button>
    </form>
</div>

<div class="admin-card">
    <div class="admin-card-header">
        <h3>All Sports ({{ $sports->count() }})</h3>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Sport / Edit Name</th><th>Courts</th><th>Action</th></tr></thead>
            <tbody>
                @forelse ($sports as $sport)
                    <tr>
                        <td>{{ $sport->id }}</td>
                        <td>
                            <form id="edit-sport-{{ $sport->id }}" method="POST" action="{{ route('super_admin.sports.update', $sport->id) }}" style="display:flex;gap:8px;min-width:230px">
                                @csrf
                                @method('PUT')
                                <input class="form-control" type="text" name="name" value="{{ $sport->name }}" maxlength="255" required aria-label="Edit sport name">
                                <button class="admin-button admin-button-outline" type="submit">Save</button>
                            </form>
                        </td>
                        <td>{{ $sport->courts_count }}</td>
                        <td>
                            @if ($sport->courts_count == 0)
                                <form method="POST" action="{{ route('super_admin.sports.destroy', $sport->id) }}" onsubmit="return confirm('Delete this sport?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-button admin-button-danger" type="submit">Delete</button>
                                </form>
                            @else
                                <span class="badge badge-warning" title="This sport has linked courts">In Use</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">No sports added yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
