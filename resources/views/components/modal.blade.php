@props(['name'])

<details {{ $attributes }}>
    <summary class="cursor-pointer">{{ $name }}</summary>
    <div class="mt-4 rounded-lg bg-white p-6 shadow-xl">
        {{ $slot }}
    </div>
</details>
