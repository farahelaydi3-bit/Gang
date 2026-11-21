@extends('layouts.super-admin')

@section('title', 'AI Moderation')
@section('page-title', 'AI Moderation')

@section('content')
    <div class="wrap page">
        <h1>AI Moderation</h1>

        <div style="display:flex; gap:1rem; margin-bottom:1rem;">
            <a href="?tab=reports"
                class="button {{ request('tab', 'reports') === 'reports' ? '' : 'outline' }}">
                🚨 Moderation Reports
            </a>
            <a href="?tab=summaries"
                class="button {{ request('tab') === 'summaries' ? '' : 'outline' }}">
                📊 Court Summaries
            </a>
        </div>

        @if(request('tab') === 'summaries')
            {{-- ───────── COURT SUMMARIES TAB ───────── --}}
            @forelse($summaries as $summary)
                <div class="panel" style="margin-bottom:1rem;">
                    <h3>{{ $summary->court->name ?? 'Unknown Court' }}</h3>
                    <p>
                        <strong>Reviews:</strong> {{ $summary->total_reviews }}
                        (safe: {{ $summary->safe_reviews }},
                        spam: {{ $summary->spam_reviews }},
                        abusive: {{ $summary->abusive_reviews }})
                        · <strong>Avg rating:</strong> {{ $summary->average_rating }}/5
                        · <em>Updated {{ $summary->generated_at?->diffForHumans() }}</em>
                    </p>

                    <p><strong>Summary:</strong> {{ $summary->summary ?? '—' }}</p>
                    <p><strong>Top Complaints:</strong> {{ $summary->top_complaints ?? '—' }}</p>
                    <p><strong>Top Praises:</strong> {{ $summary->top_praises ?? '—' }}</p>
                </div>
            @empty
                <p class="muted">
                    No summaries generated yet.
                    Run <code>php artisan reviews:refresh-summaries</code>.
                </p>
            @endforelse

            {{ $summaries->appends(['tab' => 'summaries'])->links() }}

        @else
            {{-- ───────── MODERATION REPORTS TAB ───────── --}}
            @forelse($reports as $report)
                <div class="panel" style="margin-bottom:1rem;
                    border-left:4px solid
                    @if($report->action === 'auto_banned') red
                    @elseif($report->action === 'unbanned') green
                    @else gray @endif;">

                    <p>
                        <strong>{{ ucfirst(str_replace('_', ' ', $report->action)) }}</strong>
                        — {{ $report->created_at->format('M j, Y H:i') }}
                    </p>

                    @if($report->review)
                        <p>
                            Review by <strong>{{ $report->review->user->name ?? 'Unknown' }}</strong>
                            on <strong>{{ $report->court->name ?? 'Unknown court' }}</strong>
                            (Owner: {{ $report->admin->name ?? 'N/A' }})
                        </p>
                        <p>Rating: {{ $report->review->rating }}/5</p>
                        <p>Comment: "{{ $report->review->comment }}"</p>
                    @endif

                    <p>
                        <strong>AI Decision:</strong> {{ ucfirst($report->ai_decision ?? '—') }}<br>
                        <strong>AI Reason:</strong> {{ $report->ai_reason ?? '—' }}
                    </p>

                    <p><em>{{ $report->notes }}</em></p>

                    @if($report->action === 'auto_banned' && $report->admin && $report->resolutions->isEmpty())
                        <form action="{{ route('super_admin.admins.unban', $report->admin) }}"
                            method="POST"
                            onsubmit="return confirm('Reactivate this owner?');">
                            @csrf
                            <input type="hidden" name="review_id" value="{{ $report->review_id }}">
                            <input type="hidden" name="court_id" value="{{ $report->court_id }}">
                            <button type="submit" class="button">Unban Owner</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="muted">No moderation reports yet.</p>
            @endforelse

            {{ $reports->appends(['tab' => 'reports'])->links() }}
        @endif
    </div>
@endsection
