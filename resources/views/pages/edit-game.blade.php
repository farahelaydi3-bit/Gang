@extends('layouts.demo')

@section('title', __('Edit Game'))
@section('page', 'edit-game')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Edit Game',
            'description' => 'Update the details of your public game.',
        ])

        @if(session('error'))
            <div class="panel mb-4" style="border-left: 4px solid red;">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="panel mb-4" style="border-left: 4px solid red;">
                <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="panel">
            <form action="{{ route('games.update', $game->id) }}" method="POST" class="stack">
                @csrf
                @method('PUT')

                <div>
                    <label>{{ __('Title') }}</label>
                    <input type="text" name="title" value="{{ old('title', $game->title) }}" required>
                </div>

                <div>
                    <label>{{ __('Description') }}</label>
                    <textarea name="description">{{ old('description', $game->description) }}</textarea>
                </div>

                <div>
                    <label>{{ __('Skill Level') }}</label>
                    <select name="skill_level" required>
                        <option value="beginner" @selected(old('skill_level', $game->skill_level) === 'beginner')>Beginner</option>
                        <option value="intermediate" @selected(old('skill_level', $game->skill_level) === 'intermediate')>Intermediate</option>
                        <option value="advanced" @selected(old('skill_level', $game->skill_level) === 'advanced')>Advanced</option>
                        <option value="professional" @selected(old('skill_level', $game->skill_level) === 'professional')>Professional</option>
                    </select>
                </div>

                <div>
                    <label>{{ __('Player Price (JOD)') }}</label>
                    <input type="number" step="0.01" name="player_price" value="{{ old('player_price', $game->player_price) }}" min="0">
                </div>

                <div>
                    <label>{{ __('Game Date') }}</label>
                    <input type="date" name="game_date" value="{{ old('game_date', $game->game_date->format('Y-m-d')) }}" required>
                </div>

                <div>
                    <label>{{ __('Start Time') }}</label>
                    <input type="time" name="start_time" value="{{ old('start_time', $game->start_time) }}" required>
                </div>

                <div>
                    <label>{{ __('End Time') }}</label>
                    <input type="time" name="end_time" value="{{ old('end_time', $game->end_time) }}" required>
                </div>

                <div>
                    <label>{{ __('Max Players (must equal sum of positions below)') }}</label>
                    <input type="number" name="max_players" id="max-players-input" value="{{ old('max_players', $game->max_players) }}" required min="2" oninput="updateSlots()">
                </div>

                <div>
                    <label>{{ __('Positions') }}</label>
                    <div id="positions-container">
                        @php
                            $existingPositions = old('positions', collect($game->position_slots ?? [])->map(fn($c, $n) => ['name' => $n, 'count' => $c])->values()->toArray());
                        @endphp
                        @foreach($existingPositions as $i => $pos)
                            <div class="position-row" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
                                <input type="text" name="positions[{{ $i }}][name]" value="{{ $pos['name'] }}" required style="flex:1;">
                                <input type="number" name="positions[{{ $i }}][count]" value="{{ $pos['count'] }}" min="1" oninput="updateSlots()" required style="width:80px;">
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addPosition()" class="button outline mt-2">+ Add Position</button>
                    <p class="muted mt-2">Total assigned: <strong><span id="total-slots">0</span> / <span id="max-label">0</span></strong></p>
                </div>

                <button type="submit" class="button mt-4">{{ __('Update Game') }}</button>
            </form>
        </section>
    </div>

    <script>
        let posIndex = {{ count($existingPositions ?? []) }};
        function addPosition() {
            const container = document.getElementById('positions-container');
            const row = document.createElement('div');
            row.className = 'position-row';
            row.style.cssText = 'display:flex;gap:0.5rem;margin-bottom:0.5rem;';
            row.innerHTML = `
                <input type="text" name="positions[${posIndex}][name]" placeholder="e.g. defender" required style="flex:1;">
                <input type="number" name="positions[${posIndex}][count]" placeholder="2" value="1" min="1" oninput="updateSlots()" required style="width:80px;">
                <button type="button" onclick="this.parentElement.remove(); updateSlots();" class="button outline" style="color:red;">×</button>
            `;
            container.appendChild(row);
            posIndex++;
            updateSlots();
        }

        function updateSlots() {
            let total = 0;
            document.querySelectorAll('input[name$="[count]"]').forEach(inp => {
                total += parseInt(inp.value) || 0;
            });
            document.getElementById('total-slots').textContent = total;
            document.getElementById('max-label').textContent = document.getElementById('max-players-input').value;
        }

        updateSlots();
    </script>
@endsection