@extends('admins.layout.app')

@section('title', __('SMS History'))

@section('content')
    <div class="group-games-page">
        <x-super-admin.page-header title="{{ __('SMS History') }}" description="{{ __('Review waiting-list message attempts for your courts.') }}" />

        <div class="admin-card group-games-table-card">
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Recipient') }}</th>
                            <th>{{ __('Game or court') }}</th>
                            <th>{{ __('Message') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Attempted') }}</th>
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
                            <tr><td colspan="5">{{ __('No SMS attempts have been logged yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="admin-pagination">{{ $logs->links('components.super-admin.pagination') }}</div>
        </div>
    </div>
@endsection
