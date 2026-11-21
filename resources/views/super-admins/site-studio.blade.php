@extends('layouts.super-admin')

@section('title', 'Gang Site Studio')

@section('content')
    <x-super-admin.page-header title="Gang Site Studio"
        description="Manage court owner websites. The trial lasts six months from first publication, then costs 5 JOD per month." />
    <x-super-admin.form-errors />

    <div class="super-admin-records">
        @forelse ($websites as $website)
            @php
                $public = $website->hasAccess();
            @endphp
            <article class="admin-card">
                <header class="super-admin-record-heading">
                    <h2 dir="auto">{{ $website->name }}</h2>
                    <div class="super-admin-record-badges">
                        <span class="badge {{ $website->is_active ? 'badge-success' : 'badge-danger' }}">
                            {{ $website->is_active ? 'Active' : 'Disabled' }}
                        </span>
                        <span class="badge {{ $public ? 'badge-success' : 'badge-warning' }}">
                            {{ $public ? 'Visible to visitors' : 'Hidden from visitors' }}
                        </span>
                    </div>
                </header>

                <dl class="super-admin-record-facts">
                    <div>
                        <dt>Owner</dt>
                        <dd dir="auto">{{ $website->admin?->name ?? '—' }}</dd>
                        @if ($website->admin?->email)
                            <dd class="super-admin-muted">{{ $website->admin->email }}</dd>
                        @endif
                    </div>
                    <div><dt>Publication</dt><dd>{{ $website->is_published ? 'Published' : 'Draft' }}</dd></div>
                    <div><dt>Trial started</dt><dd>{{ $website->trial_started_at?->format('Y-m-d') ?? 'Not started' }}</dd></div>
                    <div><dt>Trial ends</dt><dd>{{ $website->trial_ends_at?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt>Paid through</dt><dd>{{ $website->paid_until?->format('Y-m-d') ?? '—' }}</dd></div>
                </dl>

                <div class="super-admin-record-section super-admin-site-actions">
                    <form class="super-admin-filter-form" action="{{ route('super_admin.site-studio.renew', $website) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <div class="super-admin-filter-field">
                            <label for="paid-{{ $website->id }}" class="form-label">Paid through</label>
                            <input id="paid-{{ $website->id }}" class="form-control" type="date" name="paid_until"
                                min="{{ now()->addDay()->toDateString() }}" value="{{ $website->paid_until?->toDateString() }}" required>
                        </div>
                        <button type="submit" class="admin-button" title="Verify that payment was received before updating the subscription.">
                            Update subscription
                        </button>
                    </form>

                    <div class="super-admin-filter-form">
                        <a class="admin-button admin-button-outline" href="{{ route('gang.sites.show', $website->slug) }}"
                            target="_blank" rel="noopener">Open website <span aria-hidden="true">↗</span></a>
                        <form action="{{ route('super_admin.site-studio.toggle', $website) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="admin-button {{ $website->is_active ? 'admin-button-outline' : '' }}">
                                {{ $website->is_active ? 'Disable website' : 'Activate website' }}
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="admin-card super-admin-muted">No owner websites yet.</div>
        @endforelse
    </div>

    {{ $websites->links('components.super-admin.pagination') }}
@endsection
