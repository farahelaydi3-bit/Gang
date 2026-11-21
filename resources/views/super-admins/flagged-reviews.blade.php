@extends('layouts.super-admin')

@section('title', 'AI Moderation')

@section('content')
    @php
        $showSummaries = request('tab') === 'summaries';
    @endphp

    <x-super-admin.page-header title="AI Moderation" description="Review moderation activity and court review summaries." />
    <x-super-admin.form-errors />

    <nav class="super-admin-filter super-admin-filter-form" aria-label="Moderation views">
        <a href="{{ route('super_admin.reviews.flagged', ['tab' => 'reports']) }}"
            class="admin-button {{ $showSummaries ? 'admin-button-outline' : '' }}" @if (! $showSummaries) aria-current="page" @endif>Moderation reports</a>
        <a href="{{ route('super_admin.reviews.flagged', ['tab' => 'summaries']) }}"
            class="admin-button {{ $showSummaries ? '' : 'admin-button-outline' }}" @if ($showSummaries) aria-current="page" @endif>Court summaries</a>
    </nav>

    <div class="super-admin-records">
        @if ($showSummaries)
            @forelse ($summaries as $summary)
                <article class="admin-card">
                    <header class="super-admin-record-heading">
                        <h2 dir="auto">{{ $summary->court?->name ?? 'Unknown court' }}</h2>
                        @if ($summary->generated_at)
                            <time class="super-admin-muted" datetime="{{ $summary->generated_at->toIso8601String() }}">
                                Updated {{ $summary->generated_at->format('M j, Y · g:i A') }}
                            </time>
                        @endif
                    </header>
                    <dl class="super-admin-record-facts">
                        <div><dt>Total reviews</dt><dd>{{ $summary->total_reviews }}</dd></div>
                        <div><dt>Average rating</dt><dd>{{ $summary->average_rating }}/5</dd></div>
                        <div><dt>Safe</dt><dd>{{ $summary->safe_reviews }}</dd></div>
                        <div><dt>Spam</dt><dd>{{ $summary->spam_reviews }}</dd></div>
                        <div><dt>Abusive</dt><dd>{{ $summary->abusive_reviews }}</dd></div>
                    </dl>
                    <div class="super-admin-record-section"><h3>Summary</h3><p dir="auto">{{ $summary->summary ?? '—' }}</p></div>
                    <div class="super-admin-record-section"><h3>Top complaints</h3><p dir="auto">{{ $summary->top_complaints ?? '—' }}</p></div>
                    <div class="super-admin-record-section"><h3>Top praise</h3><p dir="auto">{{ $summary->top_praises ?? '—' }}</p></div>
                </article>
            @empty
                <div class="admin-card super-admin-muted">No court summaries yet.</div>
            @endforelse
        @else
            @forelse ($reports as $report)
                @php
                    $actionClass = match ($report->action) {
                        'auto_banned' => 'badge-danger',
                        'unbanned' => 'badge-success',
                        'flagged' => 'badge-warning',
                        default => '',
                    };
                @endphp
                <article class="admin-card">
                    <header class="super-admin-record-heading">
                        <h2 dir="auto">{{ $report->court?->name ?? 'Unknown court' }}</h2>
                        <span class="badge {{ $actionClass }}">{{ ucfirst(str_replace('_', ' ', $report->action)) }}</span>
                    </header>
                    <dl class="super-admin-record-facts">
                        <div><dt>Owner</dt><dd dir="auto">{{ $report->admin?->name ?? '—' }}</dd></div>
                        <div><dt>Reported on</dt><dd>{{ $report->created_at?->format('M j, Y · g:i A') ?? '—' }}</dd></div>
                        <div><dt>AI decision</dt><dd>{{ ucfirst($report->ai_decision ?? '—') }}</dd></div>
                        @if ($report->review)
                            <div><dt>Reviewer</dt><dd dir="auto">{{ $report->review->user?->name ?? 'Unknown' }}</dd></div>
                            <div><dt>Rating</dt><dd>{{ $report->review->rating }}/5</dd></div>
                        @endif
                    </dl>
                    @if ($report->review)
                        <div class="super-admin-record-section"><h3>Review comment</h3><p dir="auto">{{ $report->review->comment ?: '—' }}</p></div>
                    @endif
                    <div class="super-admin-record-section"><h3>AI reason</h3><p dir="auto">{{ $report->ai_reason ?? '—' }}</p></div>
                    @if ($report->notes)
                        <div class="super-admin-record-section"><h3>Notes</h3><p dir="auto">{{ $report->notes }}</p></div>
                    @endif
                    @if ($report->action === 'auto_banned' && $report->admin)
                        <form class="super-admin-record-section" action="{{ route('super_admin.admins.unban', $report->admin) }}"
                            method="POST" onsubmit="return confirm('Reactivate this owner?');">
                            @csrf
                            <input type="hidden" name="review_id" value="{{ $report->review_id }}">
                            <input type="hidden" name="court_id" value="{{ $report->court_id }}">
                            <button type="submit" class="admin-button">Reactivate owner</button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="admin-card super-admin-muted">No moderation reports yet.</div>
            @endforelse
        @endif
    </div>

    {{ ($showSummaries ? $summaries : $reports)->withQueryString()->links('components.super-admin.pagination') }}
@endsection
