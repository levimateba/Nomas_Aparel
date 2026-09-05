@props([
    'title' => '',
    'subtitle' => null,
])

<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between mb-5">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $title }}</h1>
        @if($subtitle)
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
