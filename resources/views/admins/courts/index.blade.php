
@extends('admins.layout.app')

@section('title', 'My Courts')

@section('content')
    <x-super-admin.page-header title="My Courts" description="Manage your courts, pricing, and approval status.">
        <x-slot:actions>
            <a href="{{ route('admin.courts.create') }}" class="admin-button">
                <span aria-hidden="true">+</span> Add court
            </a>
        </x-slot:actions>
    </x-super-admin.page-header>

    <x-super-admin.form-errors />

    <section class="admin-card super-admin-table-card" aria-labelledby="owner-court-list">
        <div class="admin-card-header">
            <h2 id="owner-court-list">Courts ({{ $courts->total() }})</h2>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Court</th>
                        <th scope="col">Sport</th>
                        <th scope="col">Address</th>
                        <th scope="col">Listing status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($courts as $court)
                        @php
                            $statusClass = match ($court->status) {
                                'approved' => 'badge-success',
                                'pending' => 'badge-warning',
                                'inactive', 'rejected' => 'badge-danger',
                                default => '',
                            };

                            $imageUrl = $court->image
                                ? (\Illuminate\Support\Str::startsWith($court->image, 'images/')
                                    ? asset($court->image)
                                    : asset('storage/' . $court->image))
                                : asset('courtbook-assets/images/hero-football.png');
                        @endphp

                        <tr>
                            <td>
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $court->name }}"
                                    style="width: 85px; height: 60px; object-fit: cover; border-radius: 8px;">
                            </td>

                            <td>
                                <a class="super-admin-record-link"
                                   href="{{ route('admin.courts.show', $court) }}"
                                   dir="auto">
                                    {{ $court->name }}
                                </a>
                            </td>

                            <td>{{ $court->sports?->name ?? '—' }}</td>

                            <td dir="auto">{{ $court->address ?: '—' }}</td>

                            <td>
                                <span class="badge {{ $statusClass }}">
                                    {{ ucfirst($court->status) }}
                                </span>
                            </td>

                            <td>
                                <div class="super-admin-inline-actions">
                                    <a href="{{ route('admin.courts.edit', $court) }}"
                                       class="admin-button admin-button-outline">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.courts.destroy', $court) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this court?');">
                                        @csrf
                                        @method('DELETE')

                                        <button class="admin-button admin-button-danger" type="submit">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="super-admin-empty">
                                You haven’t added any courts yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $courts->links('components.super-admin.pagination') }}
    </section>
@endsection
