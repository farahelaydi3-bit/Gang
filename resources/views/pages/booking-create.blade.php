@extends('layouts.demo')

@section('title', __('Book a court'))
@section('page', 'booking-create')

@section('content')
    @php
        $selectedCourtId = old('court_id', request('court_id'));
        $selectedCourt = $courts->firstWhere('id', $selectedCourtId);
    @endphp

    <div class="wrap page booking-create-page">
        <a class="booking-back-link" href="{{ route('courtbook.bookings') }}"><span aria-hidden="true">←</span> {{ __('My bookings') }}</a>

        <header class="booking-create-heading">
            <div>
                <span class="booking-create-kicker"><i></i>{{ __('YOUR NEXT GAME STARTS HERE') }}</span>
                <h1>{{ __('Book a court') }}</h1>
                <p>{{ __('Choose a court and available time, then confirm your booking.') }}</p>
            </div>
            <div class="booking-steps" aria-label="{{ __('Booking steps') }}">
                <span class="booking-step-active"><b>01</b>{{ __('Choose') }}</span>
                <i aria-hidden="true"></i>
                <span><b>02</b>{{ __('Review') }}</span>
                <i aria-hidden="true"></i>
                <span><b>03</b>{{ __('Pay') }}</span>
            </div>
        </header>

        @if ($errors->any())
            <div class="booking-errors" role="alert">
                <strong>{{ __('Please fix the following:') }}</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <span id="booking-validation-copy" hidden
            data-required-fields="{{ __('Please choose a court, date, start time, and end time before continuing.') }}"
            data-incomplete-fields="{{ __('Choose a court, date, start time, and end time before continuing.') }}"
            data-booking-duration="{{ __('Booking duration') }}"></span>

        <div class="booking-create-layout">
            <main class="booking-form-card">
                <div class="booking-form-intro">
                    <span class="booking-form-step">01</span>
                    <div>
                        <span>{{ __('PICK YOUR PLAYGROUND') }}</span>
                        <h2>{{ __('Set up your booking') }}</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('courtbook.bookings.store') }}" id="booking-form">
                    @csrf
                    <div class="booking-form-fields">
                        <div class="booking-field booking-field-wide">
                            <label for="court_id">{{ __('Court') }}</label>
                            <select id="court_id" name="court_id" required>
                                <option value="">{{ __('Choose a court') }}</option>
                                @foreach ($courts as $court)
                                    <option value="{{ $court->id }}"
                                        data-court-name="{{ $court->name }}"
                                        data-sport-name="{{ __($court->sports?->name) }}"
                                       data-court-image="{{ $court->image
    ? (\Illuminate\Support\Str::startsWith($court->image, 'images/')
        ? asset($court->image)
        : asset('storage/' . $court->image))
    : asset('courtbook-assets/images/hero-football.png') }}"
                                        data-weekday-price="{{ $court->weekday_price }}"
                                        data-weekend-price="{{ $court->weekend_price }}"
                                        data-evening-price="{{ $court->evening_price }}"
                                        data-deposit-amount="{{ $court->deposit_amount }}"
                                        @selected((string) old('court_id', request('court_id')) === (string) $court->id)>
                                        {{ $court->name }} — {{ __($court->sports?->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="booking-field">
                            <label for="booking_type">{{ __('Booking type') }}</label>
                            <select id="booking_type" name="booking_type" required>
                                @foreach (['individual', 'group', 'subscription'] as $type)
                                    <option value="{{ $type }}" @selected(old('booking_type', 'individual') === $type)>
                                        {{ __(ucfirst($type)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="booking-field">
                            <label for="availability_date">{{ __('Date') }}</label>
                            <input id="availability_date" name="availability_date" type="date"
                                min="{{ now()->format('Y-m-d') }}" value="{{ old('availability_date', old('booking_date', now()->format('Y-m-d'))) }}" required>
                            <input id="booking_date" name="booking_date" type="hidden" value="{{ old('booking_date', now()->format('Y-m-d')) }}">
                        </div>

                        <div class="booking-field">
                            <label for="start_time">{{ __('Available start time') }}</label>
                            <select id="start_time" name="start_time" required disabled>
                                <option value="">{{ __('Choose a court and date first') }}</option>
                            </select>
                        </div>

                        <div class="booking-field">
                            <label for="end_time">{{ __('End time') }}</label>
                            <select id="end_time" name="end_time" required disabled>
                                <option value="">{{ __('Choose a start time first') }}</option>
                            </select>
                        </div>
                    </div>

                    <input id="duration_minutes" name="duration_minutes" type="hidden">
                    <div class="booking-availability-note">
                        <span class="booking-availability-icon" aria-hidden="true">✦</span>
                        <div>
                            <p id="availability-message" aria-live="polite">{{ __('Choose a court and date to load available times.') }}</p>
                            <p id="duration-message" aria-live="polite"></p>
                        </div>
                    </div>

                    <button class="button booking-review-button" type="button" id="review-booking">
                        {{ __('Review booking') }} <span aria-hidden="true">{{ app()->isLocale('ar') ? '←' : '→' }}</span>
                    </button>
                </form>
            </main>

            <aside class="booking-create-aside" aria-label="{{ __('Booking preview') }}">
                <div class="booking-court-preview">
                    <img id="booking-court-image"
                       src="{{ $selectedCourt?->image
    ? (\Illuminate\Support\Str::startsWith($selectedCourt->image, 'images/')
        ? asset($selectedCourt->image)
        : asset('storage/' . $selectedCourt->image))
    : asset('courtbook-assets/images/players.png') }}"
                        alt="" loading="eager">
                    <span class="booking-preview-shade"></span>
                    <span class="booking-preview-label"><i></i>{{ __('GANG PICK') }}</span>
                    <div class="booking-preview-copy">
                        <span id="booking-preview-sport">{{ $selectedCourt?->sports?->name ?? __('Choose your sport') }}</span>
                        <strong id="booking-preview-court">{{ $selectedCourt?->name ?? __('Your court goes here') }}</strong>
                    </div>
                    <span class="booking-preview-court-lines" aria-hidden="true"></span>
                </div>

                <div class="booking-live-summary">
                    <div class="booking-live-summary-heading">
                        <span class="booking-summary-icon" aria-hidden="true">✦</span>
                        <div><span>{{ __('YOUR GAME PLAN') }}</span><h2>{{ __('Booking preview') }}</h2></div>
                    </div>
                    <div class="booking-live-row"><span>{{ __('Date') }}</span><strong id="booking-preview-date">—</strong></div>
                    <div class="booking-live-row"><span>{{ __('Time') }}</span><strong id="booking-preview-time">—</strong></div>
                    <div class="booking-live-row"><span>{{ __('Duration') }}</span><strong id="booking-preview-duration">—</strong></div>
                    <div class="booking-live-total"><span>{{ __('Estimated total') }}</span><strong id="booking-preview-total">—</strong></div>
                    <p class="booking-summary-footnote">{{ __('You’ll review the price breakdown before payment.') }}</p>
                </div>
                <div class="booking-secure-note"><span aria-hidden="true">✓</span>{{ __('Your time is reserved after payment.') }}</div>
            </aside>
        </div>

        <dialog class="booking-summary-modal" id="booking-summary-modal" aria-labelledby="booking-summary-title">
            <div class="row spread dialog-head">
                <div><span class="booking-create-kicker"><i></i>{{ __('FINAL CHECK') }}</span><h2 id="booking-summary-title">{{ __('Price summary') }}</h2></div>
                <button class="button outline" type="button" id="close-summary" aria-label="{{ __('Close') }}">×</button>
            </div>

            <div class="booking-summary-items">
                <div class="booking-summary-item"><span>{{ __('Court') }}</span><strong id="summary-court">—</strong></div>
                <div class="booking-summary-item"><span>{{ __('Date') }}</span><strong id="summary-date">—</strong></div>
                <div class="booking-summary-item"><span>{{ __('Time') }}</span><strong id="summary-time">—</strong></div>
                <div class="booking-summary-item"><span>{{ __('Duration') }}</span><strong id="summary-duration">—</strong></div>
                <div class="booking-summary-item"><span>{{ __('Total booking price') }}</span><strong id="estimated-total">—</strong></div>
                <div class="booking-summary-item"><span>{{ __('Deposit due now') }}</span><strong id="estimated-deposit">—</strong></div>
                <div class="booking-summary-item"><span>{{ __('Remaining after the booking') }}</span><strong id="estimated-balance">—</strong></div>
            </div>

            <p class="muted booking-modal-note">{{ __('The deposit is paid now to confirm your booking. The remaining balance is due after the booking.') }}</p>
            <div class="row spread booking-modal-actions">
                <button class="button outline" type="button" id="edit-booking">{{ __('Back to edit') }}</button>
                <button class="button" type="button" id="confirm-booking">{{ __('Continue to payment') }}</button>
            </div>
        </dialog>
    </div>

    <script>
        (() => {
            const court = document.getElementById('court_id');
            const operatingDate = document.getElementById('availability_date');
            const date = document.getElementById('booking_date');
            const start = document.getElementById('start_time');
            const end = document.getElementById('end_time');
            const duration = document.getElementById('duration_minutes');
            const message = document.getElementById('availability-message');
            const durationMessage = document.getElementById('duration-message');
            const summaryModal = document.getElementById('booking-summary-modal');
            const totalPrice = document.getElementById('estimated-total');
            const depositPrice = document.getElementById('estimated-deposit');
            const balancePrice = document.getElementById('estimated-balance');
            const summaryCourt = document.getElementById('summary-court');
            const summaryDate = document.getElementById('summary-date');
            const summaryTime = document.getElementById('summary-time');
            const summaryDuration = document.getElementById('summary-duration');
            const previewCourt = document.getElementById('booking-preview-court');
            const previewSport = document.getElementById('booking-preview-sport');
            const previewImage = document.getElementById('booking-court-image');
            const previewDate = document.getElementById('booking-preview-date');
            const previewTime = document.getElementById('booking-preview-time');
            const previewDuration = document.getElementById('booking-preview-duration');
            const previewTotal = document.getElementById('booking-preview-total');
            const confirmBooking = document.getElementById('confirm-booking');
            const reviewBooking = document.getElementById('review-booking');
            const bookingForm = document.getElementById('booking-form');
            const bookingCopy = document.getElementById('booking-validation-copy').dataset;
            let availabilityRequest = 0;
            let confirmedForPayment = false;

            function formatDuration(minutes) {
                const hours = Math.floor(minutes / 60);
                const remainingMinutes = minutes % 60;
                let hourText = '';

                if (hours === 1) hourText = 'ساعة';
                else if (hours === 2) hourText = 'ساعتان';
                else if (hours > 2 && hours <= 10) hourText = `${hours} ساعات`;
                else if (hours > 10) hourText = `${hours} ساعة`;

                if (remainingMinutes === 30) {
                    return hours > 0 ? `${hourText} ونصف` : 'نصف ساعة';
                }
                if (remainingMinutes > 0) {
                    return hours > 0 ? `${hourText} و${remainingMinutes} دقيقة` : `${remainingMinutes} دقيقة`;
                }
                return hourText;
            }

            function formatJod(amount) {
                return `${Number(amount).toFixed(2)} JOD`;
            }

            function formatDate(value) {
                if (!value) return '—';
                const locale = document.documentElement.lang || 'en';
                return new Intl.DateTimeFormat(locale, {
                    weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
                }).format(new Date(`${value}T12:00:00`));
            }

            function formatTime(value) {
                if (!value) return '';
                const [hours, minutes] = value.split(':').map(Number);
                const locale = document.documentElement.lang || 'en';
                return new Intl.DateTimeFormat(locale, { hour: 'numeric', minute: '2-digit' })
                    .format(new Date(2000, 0, 1, hours, minutes));
            }

            function updateLivePreview() {
                const courtOption = court.selectedOptions[0];
                previewCourt.textContent = court.value
                    ? (courtOption.dataset.courtName || courtOption.textContent.trim())
                    : @json(__('Your court goes here'));
                previewSport.textContent = court.value
                    ? (courtOption.dataset.sportName || @json(__('Court booking')))
                    : @json(__('Choose your sport'));

                if (court.value && courtOption.dataset.courtImage) {
                    previewImage.src = courtOption.dataset.courtImage;
                } else if (!court.value) {
                    previewImage.src = @json(asset('courtbook-assets/images/players.png'));
                }

                previewDate.textContent = formatDate(operatingDate.value);
                previewTime.textContent = start.value && end.value
                    ? `${formatTime(start.value)} – ${formatTime(end.value)}`
                    : '—';
                previewDuration.textContent = duration.value
                    ? formatDuration(Number(duration.value))
                    : '—';
            }

            function updatePriceSummary(selectedDuration) {
                const courtOption = court.selectedOptions[0];
                if (!court.value || !date.value || !start.value || !end.value || selectedDuration <= 0 || !courtOption) {
                    durationMessage.textContent = '';
                    previewTotal.textContent = '—';
                    updateLivePreview();
                    return;
                }

                const startHour = Number(start.value.split(':')[0]);
                const bookingDate = new Date(`${date.value}T12:00:00`);
                const isWeekend = bookingDate.getDay() === 5 || bookingDate.getDay() === 6;
                const hourlyPrice = isWeekend
                    ? Number(courtOption.dataset.weekendPrice)
                    : (startHour >= 18
                        ? Number(courtOption.dataset.eveningPrice)
                        : Number(courtOption.dataset.weekdayPrice));
                const total = hourlyPrice * selectedDuration / 60;
                const listedDeposit = Number(courtOption.dataset.depositAmount);
                const deposit = listedDeposit > 0 ? Math.min(listedDeposit, total) : total;
                const balance = Math.max(0, total - deposit);

                durationMessage.textContent = `${bookingCopy.bookingDuration}: ${formatDuration(selectedDuration)}`;
                previewTotal.textContent = formatJod(total);
                updateLivePreview();
                totalPrice.textContent = formatJod(total);
                depositPrice.textContent = formatJod(deposit);
                balancePrice.textContent = formatJod(balance);
            }

            function showPriceSummary() {
                const courtOption = court.selectedOptions[0];
                const selectedDuration = Number(duration.value);
                if (!courtOption || !operatingDate.value || !date.value || !start.value || !end.value || selectedDuration <= 0) {
                    return false;
                }

                updatePriceSummary(selectedDuration);
                summaryCourt.textContent = courtOption.textContent.trim();
                summaryDate.textContent = date.value;
                summaryTime.textContent = `${start.value} – ${end.value}`;
                summaryDuration.textContent = formatDuration(selectedDuration);
                summaryModal.showModal();
                return true;
            }

            async function loadAvailability() {
                const requestNumber = ++availabilityRequest;
                start.innerHTML = '';
                start.disabled = true;
                end.innerHTML = `<option value="">${@json(__('Choose a start time first'))}</option>`;
                end.disabled = true;
                duration.value = '';
                previewTotal.textContent = '—';
                durationMessage.textContent = '';

                date.value = operatingDate.value;
                updateLivePreview();
                if (!court.value || !operatingDate.value) {
                    message.textContent = @json(__('Choose a court and date to load available times.'));
                    return;
                }

                message.textContent = @json(__('Loading available times...'));
                try {
                    const response = await fetch(`/courts/${court.value}/availability?date=${encodeURIComponent(operatingDate.value)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (requestNumber !== availabilityRequest) return;
                    if (!response.ok) throw new Error(data.message || @json(__('Could not load available times.')));

                    duration.value = data.minimum_duration_minutes;
                    data.available_slots.forEach((slot) => {
                        const option = document.createElement('option');
                        option.value = slot.start_time;
                        option.textContent = slot.start_time;
                        option.dataset.endTimes = JSON.stringify(slot.end_times);
                        option.dataset.bookingDate = slot.booking_date;
                        start.appendChild(option);
                    });

                    if (data.available_slots.length) {
                        start.disabled = false;
                        message.textContent = `${@json(__('Minimum booking'))}: ${data.minimum_duration_minutes} ${@json(__('minutes'))}`;
                        start.dispatchEvent(new Event('change'));
                    } else {
                        const noTimesRemainToday = data.date === data.today && data.past_slots_skipped > 0;
                        const emptyStateLabel = noTimesRemainToday
                            ? @json(__('No later times today'))
                            : @json(__('No times available'));
                        start.innerHTML = `<option value="">${emptyStateLabel}</option>`;
                        message.textContent = noTimesRemainToday
                            ? @json(__('No bookable times remain today. Please choose another date.'))
                            : @json(__('No available times for this date.'));
                    }
                } catch (error) {
                    if (requestNumber !== availabilityRequest) return;
                    start.innerHTML = `<option value="">${@json(__('Could not load times'))}</option>`;
                    message.textContent = error.message;
                }
            }

            start.addEventListener('change', () => {
                const selected = start.selectedOptions[0];
                date.value = selected?.dataset.bookingDate || operatingDate.value;
                const endTimes = selected?.dataset.endTimes ? JSON.parse(selected.dataset.endTimes) : [];
                end.innerHTML = '';
                endTimes.forEach((time) => {
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = time;
                    end.appendChild(option);
                });
                end.disabled = endTimes.length === 0;
                end.dispatchEvent(new Event('change'));
            });
            end.addEventListener('change', () => {
                const [startHour, startMinute] = (start.value || '').split(':').map(Number);
                const [endHour, endMinute] = (end.value || '').split(':').map(Number);
                let selectedDuration = (endHour * 60 + endMinute) - (startHour * 60 + startMinute);
                if (selectedDuration <= 0 && start.value && end.value) selectedDuration += 24 * 60;
                duration.value = selectedDuration > 0 ? selectedDuration : '';
                updatePriceSummary(selectedDuration);
            });

            bookingForm.addEventListener('submit', (event) => {
                // The modal's confirmation submits the same form after review.
                if (confirmedForPayment) {
                    confirmedForPayment = false;
                    return;
                }

                event.preventDefault();
                if (!showPriceSummary()) {
                    bookingForm.reportValidity();
                }
            });

            reviewBooking.addEventListener('click', () => {
                const formIsValid = bookingForm.reportValidity();
                if (!formIsValid) {
                    message.textContent = bookingCopy.requiredFields;
                    bookingForm.querySelector(':invalid')?.focus();
                    return;
                }

                if (!showPriceSummary()) {
                    message.textContent = bookingCopy.incompleteFields;
                    start.focus();
                }
            });

            document.getElementById('edit-booking').addEventListener('click', () => summaryModal.close());
            document.getElementById('close-summary').addEventListener('click', () => summaryModal.close());
            confirmBooking.addEventListener('click', () => {
                if (!bookingForm.reportValidity()) return;

                confirmBooking.disabled = true;
                confirmBooking.textContent = @json(__('Opening payment...'));
                summaryModal.close();
                confirmedForPayment = true;
                bookingForm.requestSubmit();
            });

            summaryModal.addEventListener('click', (event) => {
                if (event.target === summaryModal) summaryModal.close();
            });
            court.addEventListener('change', () => {
                updateLivePreview();
                loadAvailability();
            });
            operatingDate.addEventListener('change', () => {
                updateLivePreview();
                loadAvailability();
            });
            updateLivePreview();
            loadAvailability();
        })();
    </script>
@endsection
