<form id="game-filters" class="row filters spread">
    <select aria-label="{{ __('Sport') }}" name="sport"></select>
    <select aria-label="{{ __('Area') }}" name="area"></select>
    <input type="date" name="date" aria-label="{{ __('Game date') }}">
    <input type="number" name="price" min="0" placeholder="{{ __('Max JOD') }}" aria-label="{{ __('Maximum price per player') }}" style="width:110px">
    <select name="distance" aria-label="{{ __('Distance from central Amman') }}">
        <option value="">{{ __('Any distance') }}</option>
        @foreach ([5, 10, 25] as $distance)
            <option value="{{ $distance }}">{{ $distance }} km</option>
        @endforeach
    </select>
    <label><input type="checkbox" name="spots"> {{ __('Spots available') }}</label>
    <a class="button" href="{{ route('courtbook.create-game') }}">{{ __('Create a game') }}</a>
</form>
<div id="game-results" class="grid"></div>

