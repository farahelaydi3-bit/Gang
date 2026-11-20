@extends('layouts.demo')

@section('title', __('Book a court'))
@section('page', 'booking-create')

@section('content')
    <div class="wrap page">
        <a class="muted" href="{{ route('courtbook.bookings') }}">← {{ __('My bookings') }}</a>

        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Book a court',
            'description' => 'Choose a court and available time, then confirm your booking.',
        ])

        @if ($errors->any())
            <div class="panel" role="alert">
                <strong>{{ __('Please fix the following:') }}</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="panel stack" style="max-width: 42rem">
            <form method="POST" action="{{ route('courtbook.bookings.store') }}" class="stack" id="booking-form">
                @csrf
                <label for="court_id">{{ __('Court') }}</label>
                <select id="court_id" name="court_id" required>
                    <option value="">{{ __('Choose a court') }}</option>
                    @foreach ($courts as $court)
                        <option value="{{ $court->id }}"
                            data-weekday-price="{{ $court->weekday_price }}"
                            data-weekend-price="{{ $court->weekend_price }}"
                            data-evening-price="{{ $court->evening_price }}"
                            data-deposit-amount="{{ $court->deposit_amount }}"
                            @selected((string) old('court_id', request('court_id')) === (string) $court->id)>
                            {{ $court->name }} — {{ $court->sports->name }}
                        </option>
                    @endforeach
                </select>

                <label for="booking_type">{{ __('Booking type') }}</label>
                <select id="booking_type" name="booking_type" required>
                    @foreach (['individual', 'group', 'subscription'] as $type)
                        <option value="{{ $type }}" @selected(old('booking_type', 'individual') === $type)>
                            {{ __(ucfirst($type)) }}
                        </option>
                    @endforeach
                </select>

                <label for="availability_date">{{ __('Date') }}</label>
                <input id="availability_date" name="availability_date" type="date"
                    min="{{ now()->format('Y-m-d') }}" value="{{ old('availability_date', old('booking_date', now()->format('Y-m-d'))) }}" required>
                <input id="booking_date" name="booking_date" type="hidden" value="{{ old('booking_date', now()->format('Y-m-d')) }}">

                <label for="start_time">{{ __('Available start time') }}</label>
                <select id="start_time" name="start_time" required disabled>
                    <option value="">{{ __('Choose a court and date first') }}</option>
                </select>

                <label for="end_time">{{ __('End time') }}</label>
                <select id="end_time" name="end_time" required disabled>
                    <option value="">{{ __('Choose a start time first') }}</option>
                </select>
                <input id="duration_minutes" name="duration_minutes" type="hidden">
                <p class="muted" id="availability-message" aria-live="polite">
                    {{ __('Choose a court and date to load available times.') }}
                </p>
                <p class="muted" id="duration-message" aria-live="polite"></p>

                <button class="button" type="button" id="review-booking">{{ __('Continue to payment') }}</button>
            </form>

            <dialog class="booking-summary-modal" id="booking-summary-modal" aria-labelledby="booking-summary-title">
                <div class="row spread dialog-head">
                    <h2 id="booking-summary-title">{{ __('Price summary') }}</h2>
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

                <p class="muted">{{ __('The deposit is paid now to confirm your booking. The remaining balance is due after the booking.') }}</p>
                <div class="row spread">
                    <button class="button outline" type="button" id="edit-booking">{{ __('Back to edit') }}</button>
                    <button class="button" type="button" id="confirm-booking">{{ __('Continue to payment') }}</button>
                </div>
            </dialog>
        </section>
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
            const confirmBooking = document.getElementById('confirm-booking');
            const reviewBooking = document.getElementById('review-booking');
            const bookingForm = document.getElementById('booking-form');
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

            function updatePriceSummary(selectedDuration) {
                const courtOption = court.selectedOptions[0];
                if (!court.value || !date.value || !start.value || !end.value || selectedDuration <= 0 || !courtOption) {
                    durationMessage.textContent = '';
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

                durationMessage.textContent = `مدة الحجز: ${formatDuration(selectedDuration)}`;
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
                durationMessage.textContent = '';

                date.value = operatingDate.value;
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
                    message.textContent = 'Please choose a court, date, start time, and end time before continuing.';
                    bookingForm.querySelector(':invalid')?.focus();
                    return;
                }

                if (!showPriceSummary()) {
                    message.textContent = 'Choose a court, date, start time, and end time before continuing.';
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
            court.addEventListener('change', loadAvailability);
            operatingDate.addEventListener('change', loadAvailability);
            loadAvailability();
        })();
    </script>
@endsection
