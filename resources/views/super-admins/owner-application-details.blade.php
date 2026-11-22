@extends('layouts.super-admin')

@section('title', __('Application Details'))
@section('page-title', 'Review Owner Application')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/owner-application-review.css') }}">
@endpush

@section('content')
    @php
        $owner = $application->admin;
        $status = match ($application->status) {
            'submitted' => __('Under review'),
            'approved' => __('Approved'),
            'rejected' => __('Rejected'),
            default => ucfirst($application->status),
        };
    @endphp

    <div class="overview-page application-review">
        <x-super-admin.page-header :title="$application->facility_name" :reference="__('Owner application #:number', ['number' => $application->id])">
            @if ($application->submitted_at)
                <p>{{ __('Submitted') }} <time datetime="{{ $application->submitted_at->toIso8601String() }}">{{ $application->submitted_at->format('M j, Y · g:i A') }}</time></p>
            @endif
            <x-slot:actions>
                <span class="application-review-status" data-status="{{ $application->status }}">{{ $status }}</span>
                <a class="admin-button admin-button-outline" href="{{ route('super_admin.applications.index') }}">
                    <span aria-hidden="true">←</span> {{ __('Back to applications') }}
                </a>
            </x-slot:actions>
        </x-super-admin.page-header>

        <div class="application-review-grid">
            <div class="application-review-column">
                <section class="overview-panel application-review-card" aria-labelledby="review-facility">
                    <h2 id="review-facility">{{ __('Facility details') }}</h2>
                    <dl class="application-review-facts">
                        <div>
                            <dt>{{ __('Address') }}</dt>
                            <dd dir="auto">{{ $application->address ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Number of courts') }}</dt>
                            <dd>{{ number_format($application->courts_count) }}</dd>
                        </div>
                    </dl>

                    <div class="application-review-section">
                        <h3>{{ __('Sports offered') }}</h3>
                        @if ($application->sports->isNotEmpty())
                            <ul class="application-review-sports">
                                @foreach ($application->sports as $sport)
                                    <li dir="auto">{{ __($sport->name) }}</li>
                                @endforeach
                            </ul>
                        @else
                            <p class="application-review-muted">{{ __('No sports listed.') }}</p>
                        @endif
                    </div>

                    <div class="application-review-section">
                        <h3>{{ __('Facility description') }}</h3>
                        <p class="application-review-text" dir="auto">{{ $application->facility_description ?: '—' }}</p>
                    </div>

                    <div class="application-review-section">
                        <h3>{{ __('Message from the owner') }}</h3>
                        <p class="application-review-text" dir="auto">{{ $application->application_message ?: '—' }}</p>
                    </div>
                </section>

                <section class="overview-panel application-review-card" aria-labelledby="review-attachments">
                    <h2 id="review-attachments">{{ __('Commercial registration image and supporting documents') }}</h2>
                    @if ($application->attachments->isNotEmpty())
                        <ul class="application-review-files">
                            @foreach ($application->attachments as $attachment)
                                <li>
                                    <div>
                                        <strong dir="auto">{{ $attachment->document_type === 'commercial_registration' ? __('Commercial registration image') : __('Supporting document') }}: {{ $attachment->original_name }}</strong>
                                        <small>{{ $attachment->mime_type }} · {{ number_format($attachment->file_size / 1024, 1) }} {{ __('KB') }}</small>
                                    </div>
                                    <a class="admin-button admin-button-outline"
                                        href="{{ route('super_admin.applications.attachments.download', [$application, $attachment->id]) }}"
                                        aria-label="Download {{ $attachment->original_name }}">{{ __('Download') }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="application-review-muted">{{ __('No commercial registration image or supporting documents uploaded.') }}</p>
                    @endif
                </section>
            </div>

            <div class="application-review-column">
                <section class="overview-panel application-review-card" aria-labelledby="review-owner">
                    <h2 id="review-owner">{{ __('Owner information') }}</h2>
                    <dl class="application-review-facts application-review-owner">
                        <div>
                            <dt>{{ __('Name') }}</dt>
                            <dd dir="auto">{{ $owner?->name ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Business') }}</dt>
                            <dd dir="auto">{{ $owner?->business_name ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Email address') }}</dt>
                            <dd>{{ $owner?->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Phone number') }}</dt>
                            <dd>{{ $owner?->phone ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('Account status') }}</dt>
                            <dd>{{ $owner?->status ? __(ucfirst($owner->status)) : '—' }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="overview-panel application-review-card" aria-labelledby="review-decision">
                    @if ($application->status === 'submitted')
                        <h2 id="review-decision">{{ __('Review decision') }}</h2>

                        <div class="application-review-actions" id="reviewActionButtons" hidden>
                            <button type="button" class="admin-button" aria-controls="approveApplicationPanel" aria-expanded="false">{{ __('Approve application') }}</button>
                            <button type="button" class="admin-button admin-button-outline application-review-reject" aria-controls="rejectApplicationPanel" aria-expanded="false">{{ __('Reject application') }}</button>
                        </div>

                        <form id="approveApplicationPanel" class="application-review-decision"
                            method="POST" action="{{ route('super_admin.applications.approve', $application) }}">
                            @csrf
                            @method('PATCH')
                            <h3 tabindex="-1" data-review-focus>{{ __('Approve this application?') }}</h3>
                            <p>{{ __('Approving activates the owner’s account.') }}</p>
                            <div class="application-review-controls">
                                <button type="submit" class="admin-button">{{ __('Confirm approval') }}</button>
                                <button type="button" class="admin-button admin-button-outline" data-review-cancel hidden>{{ __('Cancel') }}</button>
                            </div>
                        </form>

                        <form id="rejectApplicationPanel" class="application-review-decision"
                            method="POST" action="{{ route('super_admin.applications.reject', $application) }}"
                            @error('review_notes') data-review-error @enderror>
                            @csrf
                            @method('PATCH')
                            <label for="review_notes">{{ __('Reason for rejection') }}</label>
                            <p id="review-notes-hint">{{ __('The owner will see this reason and can submit a new application.') }}</p>
                            <textarea id="review_notes" name="review_notes" rows="4" required minlength="5" maxlength="2000" dir="auto"
                                aria-describedby="review-notes-hint @error('review_notes') review-notes-error @enderror"
                                @error('review_notes') aria-invalid="true" @enderror data-review-focus>{{ old('review_notes') }}</textarea>
                            @error('review_notes')
                                <p class="application-review-error" id="review-notes-error" role="alert">{{ $message }}</p>
                            @enderror
                            <div class="application-review-controls">
                                <button type="submit" class="admin-button admin-button-danger">{{ __('Confirm rejection') }}</button>
                                <button type="button" class="admin-button admin-button-outline" data-review-cancel hidden>{{ __('Cancel') }}</button>
                            </div>
                        </form>
                    @else
                        <h2 id="review-decision">{{ __('Review result') }}</h2>
                        <dl class="application-review-facts application-review-owner">
                            <div>
                                <dt>{{ __('Decision') }}</dt>
                                <dd>{{ $status }}</dd>
                            </div>
                            @if ($application->reviewed_at)
                                <div>
                                    <dt>{{ __('Reviewed on') }}</dt>
                                    <dd><time datetime="{{ $application->reviewed_at->toIso8601String() }}">{{ $application->reviewed_at->format('M j, Y · g:i A') }}</time></dd>
                                </div>
                                <div>
                                    <dt>{{ __('Reviewed by') }}</dt>
                                    <dd dir="auto">{{ $application->reviewer?->name ?? 'Unknown' }}</dd>
                                </div>
                            @endif
                        </dl>
                        @if ($application->review_notes)
                            <div class="application-review-section">
                                <h3>{{ __('Review notes') }}</h3>
                                <p class="application-review-text" dir="auto">{{ $application->review_notes }}</p>
                            </div>
                        @endif
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const actions = document.getElementById('reviewActionButtons');
            if (!actions) return;

            const choices = [...actions.querySelectorAll('[aria-controls]')].map(button => ({
                button,
                panel: document.getElementById(button.getAttribute('aria-controls'))
            }));

            const showDecision = selected => {
                choices.forEach(({ button, panel }) => {
                    panel.hidden = panel !== selected;
                    button.setAttribute('aria-expanded', String(panel === selected));
                });
            };

            choices.forEach(({ button, panel }) => {
                const cancel = panel.querySelector('[data-review-cancel]');
                cancel.hidden = false;
                cancel.addEventListener('click', () => {
                    showDecision(null);
                    button.focus();
                });
                button.addEventListener('click', () => {
                    const opening = panel.hidden;
                    showDecision(opening ? panel : null);
                    if (opening) panel.querySelector('[data-review-focus]').focus();
                });
            });

            const invalidPanel = choices.find(({ panel }) => panel.hasAttribute('data-review-error'))?.panel;
            showDecision(invalidPanel ?? null);
            actions.hidden = false;
            invalidPanel?.querySelector('[data-review-focus]').focus();
        })();
    </script>
@endpush
