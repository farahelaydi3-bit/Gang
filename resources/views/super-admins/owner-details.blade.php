@extends('layouts.super-admin')

@section('title', 'Owner Details')

@section('content')
    <x-super-admin.page-header :title="$admin->name" :description="$admin->business_name" :reference="'Owner #' . $admin->id">
        <x-slot:actions>
            <a href="{{ route('super_admin.admins.index') }}" class="admin-button admin-button-outline">
                <span aria-hidden="true">←</span> Back to owners
            </a>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="super-admin-records">
        <section class="admin-card" aria-labelledby="owner-information">
            <div class="admin-card-header"><h2 id="owner-information">Owner information</h2></div>
            @php
                $statusClass = match ($admin->status) {
                    'active' => 'badge-success',
                    'pending' => 'badge-warning',
                    'rejected', 'suspended' => 'badge-danger',
                    default => '',
                };
            @endphp

            <dl class="super-admin-record-facts">
                <div><dt>Email address</dt><dd>{{ $admin->email }}</dd></div>
                <div><dt>Phone number</dt><dd>{{ $admin->phone ?: '—' }}</dd></div>
                <div><dt>Business name</dt><dd dir="auto">{{ $admin->business_name ?: '—' }}</dd></div>
                <div>
                    <dt>Account status</dt>
                    <dd><span class="badge {{ $statusClass }}">{{ ucfirst($admin->status ?? 'Unknown') }}</span></dd>
                </div>
                <div>
                    <dt>Registered</dt>
                    <dd>
                        @if ($admin->created_at)
                            <time datetime="{{ $admin->created_at->toDateString() }}">{{ $admin->created_at->format('M j, Y') }}</time>
                        @else
                            —
                        @endif
                    </dd>
                </div>
            </dl>
        </section>

        <section class="admin-card super-admin-table-card" aria-labelledby="owner-courts">
            <div class="admin-card-header"><h2 id="owner-courts">Courts ({{ $courts->total() }})</h2></div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th scope="col">Court</th>
                            <th scope="col">Sport</th>
                            <th scope="col">Address</th>
                            <th scope="col">Listing status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courts as $court)
                            @php
                                $statusClass = match ($court->status) {
                                    'approved' => 'badge-success',
                                    'pending' => 'badge-warning',
                                    'inactive', 'rejected' => 'badge-danger',
                                    default => '',
                                };
                            @endphp
                            <tr>
                                <td><a class="super-admin-record-link" href="{{ route('super_admin.courts.show', $court) }}" dir="auto">{{ $court->name }}</a></td>
                                <td>{{ $court->sports?->name ?? '—' }}</td>
                                <td dir="auto">{{ $court->address ?: '—' }}</td>
                                <td><span class="badge {{ $statusClass }}">{{ ucfirst($court->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="super-admin-empty">This owner has no courts yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $courts->links('components.super-admin.pagination') }}
        </section>
    </div>
@endsection
