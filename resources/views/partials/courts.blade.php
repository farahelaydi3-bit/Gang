<div class="row filters">
    <div class="chips" id="court-sports" role="group" aria-label="Sport filter"></div>
    <select aria-label="{{ __('Area') }}" name="area" id="court-area"></select>
    <input aria-label="{{ __('Booking date') }}" name="date" type="date" id="court-date" required>
    <button type="button" class="chip" data-action="available" aria-pressed="false">{{ __('Available only') }}</button>
    <button type="button" class="dark" data-action="filters">{{ __('Filters') }}</button>
</div>
<div id="court-results"></div>

