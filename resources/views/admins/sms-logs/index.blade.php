@extends('admins.layout.app')

@section('title', 'SMS History')

@section('content')
    <div class="group-games-page">
        <x-super-admin.page-header title="SMS History" description="Review waiting-list message attempts for your courts." />

        <div class="admin-card group-games-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Game or court</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Attempted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->user?->name ?? $log->phone }}</td>
                                <td>{{ $log->waitingList?->groupGame?->title ?? $log->waitingList?->court?->name ?? '—' }}</td>
                                <td>{{ $log->message }}</td>
                                <td><x-dashboard-status :state="$log->status" /></td>
                                <td>{{ ($log->sent_at ?? $log->created_at)->format('M j, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No SMS attempts have been logged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $logs->links('components.super-admin.pagination') }}</div>
        </div>
    </div>
@endsection
