@extends('layouts.super-admin')

@section('title', 'Owner Applications')
@section('page-title', 'Owner Applications')

@section('content')

<div class="page-header">
    <div>
        <h2>Owner Applications</h2>
        <p>Review requests from sports facility owners.</p>
    </div>
</div>

@if ($errors->any())
<div class="admin-card" style="color:#b42318;margin-bottom:20px">
    {{ $errors->first() }}
</div>
@endif

<div class="admin-card" style="margin-bottom:20px">
    <form method="GET"
        action="{{ route('super_admin.applications.index') }}"
        style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">

        <label for="status">Filter by status</label>

        <select name="status" id="status"
            style="padding:10px;border:1px solid #ddd;border-radius:8px">

            <option value="">All Applications</option>

            <option value="submitted" @selected(request('status')==='submitted' )>
                Under Review
            </option>

            <option value="approved" @selected(request('status')==='approved' )>
                Approved
            </option>

            <option value="rejected" @selected(request('status')==='rejected' )>
                Rejected
            </option>
        </select>

        <button type="submit" class="admin-button">
            Filter
        </button>
    </form>
</div>

<div class="admin-card" style="overflow-x:auto">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Owner</th>
                <th>Facility</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($applications as $application)
            <tr>
                <td>#{{ $application->id }}</td>

                <td>
                    <strong>{{ $application->admin?->name ?? 'Unknown' }}</strong>
                    <div style="font-size:12px;color:#667085">
                        {{ $application->admin?->email }}
                    </div>
                </td>

                <td>{{ $application->facility_name }}</td>

                <td>
                    @if ($application->status === 'submitted')
                    <span style="color:#b7791f;font-weight:700">
                        Under Review
                    </span>
                    @elseif ($application->status === 'approved')
                    <span style="color:#18845c;font-weight:700">
                        Approved
                    </span>
                    @else
                    <span style="color:#c0392b;font-weight:700">
                        Rejected
                    </span>
                    @endif
                </td>

                <td>
                    {{ $application->submitted_at?->format('M d, Y') ?? '-' }}
                </td>

                <td>
                    <a
                        href="{{ route('super_admin.applications.show', $application) }}"
                        class="admin-button">
                        Review
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:30px">
                    No applications found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:24px">
        {{ $applications->links() }}
    </div>
</div>

@endsection