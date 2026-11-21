@extends('layouts.demo')

@section('title', __('Create a Game'))
@section('page', 'create-game')

@section('content')
    <div class="wrap page">
        @include('pages.heading', [
            'eyebrow' => 'CourtBook',
            'heading' => 'Create a public game',
            'description' => 'Organize a pickup game with players in Amman.',
        ])

        @if($errors->any())
            <div class="panel mb-4" style="border-left: 4px solid red;">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="panel">
            <form action="{{ route('games.store') }}" method="POST" class="stack">
                @csrf

                <div>
                    <label>{{ __('Court') }}</label>
                    <select name="court_id" required>
                        <option value="">— Select court —</option>
                        @foreach($courts as $court)
                            <option value="{{ $court->id }}" @selected(old('court_id') == $court->id)>
                                {{ $court->name }} ({{ $court->sports->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>{{ __('Title') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" required>
                </div>

                <div>
                    <label>{{ __('Description') }}</label>
                    <textarea name="description">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label>{{ __('Skill Level') }}</label>
                    <select name="skill_level" required>
                        <option value="">— Select skill —</option>
                        <option value="beginner" @selected(old('skill_level') === 'beginner')>Beginner</option>
                        <option value="intermediate" @selected(old('skill_level') === 'intermediate')>Intermediate</option>
                        <option value="advanced" @selected(old('skill_level') === 'advanced')>Advanced</option>
                        <option value="professional" @selected(old('skill_level') === 'professional')>Professional</option>
                    </select>
                </div>

                <div>
                    <label>{{ __('Player Price (JOD)') }}</label>
                    <input type="number" step="0.01" name="player_price" value="{{ old('player_price', 0) }}" min="0">
                </div>

                <div>
                    <label>{{ __('Game Date') }}</label>
                    <input type="date" name="game_date" value="{{ old('game_date', now()->toDateString()) }}" required>
                </div>

                <div>
                    <label>{{ __('Start Time') }}</label>
                    <input type="time" name="start_time" value="{{ old('start_time') }}" required>
                </div>

                <div>
                    <label>{{ __('End Time') }}</label>
                    <input type="time" name="end_time" value="{{ old('end_time') }}" required>
                </div>

                <div>
                    <label>{{ __('Max Players (must equal sum of positions below)') }}</label>
                    <input type="number" name="max_players" id="max-players-input" value="{{ old('max_players', 10) }}" required min="2" oninput="updateSlots()">
                </div>

                <div>
                    <label>{{ __('Positions') }}</label>
                    <div id="positions-container">
                        <div class="position-row" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
                            <input type="text" name="positions[0][name]" placeholder="e.g. goalkeeper" value="goalkeeper" required style="flex:1;">
                            <input type="number" name="positions[0][count]" placeholder="2" value="2" min="1" oninput="updateSlots()" required style="width:80px;">
                        </div>
                    </div>
                    <button type="button" onclick="addPosition()" class="button outline mt-2">+ Add Position</button>
                    <p class="muted mt-2">Total assigned: <strong><span id="total-slots">2</span> / <span id="max-label">10</span></strong></p>
                </div>

                <button type="submit" class="button mt-4">{{ __('Create Game') }}</button>
            </form>
        </section>
    </div>

    <script>
        let posIndex = 1;
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

        // initialize
        updateSlots();
    </script>
@endsection