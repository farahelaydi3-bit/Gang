@extends('layouts.super-admin')
@section('title', __('Manage Sports'))
@section('page-title', 'Sports Management')
@section('content')
<x-super-admin.page-header title="{{ __('Manage Sports') }}" description="{{ __('Manage the sports available to players and court owners.') }}">
    <x-slot:actions>
        <a class="admin-button" id="add-sport-trigger" aria-haspopup="dialog" href="{{ route('super_admin.sports.index', ['add' => 1]) }}">
            <span aria-hidden="true">+</span> {{ __('Add sport') }}
        </a>
    </x-slot:actions>
</x-super-admin.page-header>

<x-super-admin.form-errors />

<x-super-admin.list-filters :action="route('super_admin.sports.index')"
    placeholder="Sport name"
    :selects="['usage' => ['Court usage', 'All sports', $usageOptions]]" />

<dialog class="super-admin-dialog" id="add-sport-dialog" aria-labelledby="add-sport-title"
    @if (request()->boolean('add') || $errors->addSport->any()) open @endif>
    <form class="super-admin-dialog-content" action="{{ route('super_admin.sports.store') }}" method="POST">
        @csrf
        <h2 id="add-sport-title">{{ __('Add sport') }}</h2>
        <label class="form-label" for="sport-name">{{ __('Sport name') }}</label>
        <input id="sport-name" type="text" name="name" class="form-control" maxlength="255" autocomplete="off"
            value="{{ $errors->addSport->has('name') ? old('name') : '' }}" placeholder="{{ __('e.g. Padel') }}" required autofocus
            @error('name', 'addSport') aria-invalid="true" aria-describedby="add-sport-error" @enderror>
        @error('name', 'addSport')
            <p class="super-admin-form-errors" id="add-sport-error" role="alert">{{ $message }}</p>
        @enderror
        <div class="super-admin-dialog-actions">
            <a class="admin-button admin-button-outline" id="add-sport-cancel" href="{{ route('super_admin.sports.index') }}">{{ __('Cancel') }}</a>
            <button class="admin-button" type="submit">{{ __('Add sport') }}</button>
        </div>
    </form>
</dialog>

<div class="admin-card super-admin-table-card">
    <div class="admin-card-header">
        <h3>{{ __('All Sports (') }}{{ $sports->total() }})</h3>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>{{ __('ID') }}</th><th>{{ __('Sport') }}</th><th>{{ __('Courts') }}</th><th>{{ __('Actions') }}</th></tr></thead>
            <tbody>
                @forelse ($sports as $sport)
                    <tr>
                        <td>{{ $sport->id }}</td>
                        <td>
                            <form class="super-admin-inline-actions" method="POST" action="{{ route('super_admin.sports.update', $sport->id) }}">
                                @csrf
                                @method('PUT')
                                <input class="form-control" type="text" name="name" value="{{ $sport->name }}" maxlength="255" required aria-label="{{ __('Edit sport name') }}">
                                <button class="admin-button admin-button-outline" type="submit">{{ __('Save') }}</button>
                            </form>
                        </td>
                        <td>{{ $sport->courts_count }}</td>
                        <td>
                            @if ($sport->courts_count == 0)
                                <form method="POST" action="{{ route('super_admin.sports.destroy', $sport->id) }}" onsubmit="return confirm(@js(__('Delete this sport?')))">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-button admin-button-danger" type="submit">{{ __('Delete') }}</button>
                                </form>
                            @else
                                <span class="badge badge-warning" title="{{ __('This sport has linked courts') }}">{{ __('In Use') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="super-admin-empty">{{ __(\App\Support\DashboardList::hasFilters(request()) ? 'No results match your filters.' : 'No sports added yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $sports->links('components.super-admin.pagination') }}
</div>
@endsection

@push('scripts')
    <script>
        (() => {
            const dialog = document.getElementById('add-sport-dialog');
            if (typeof dialog.showModal !== 'function') return;

            const trigger = document.getElementById('add-sport-trigger');
            trigger.addEventListener('click', event => {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                dialog.showModal();
            });
            document.getElementById('add-sport-cancel').addEventListener('click', event => {
                event.preventDefault();
                dialog.close();
            });
            dialog.addEventListener('close', () => {
                trigger.focus();
            });

            if (dialog.open) {
                dialog.removeAttribute('open');
                dialog.showModal();
            }
        })();
    </script>
@endpush
