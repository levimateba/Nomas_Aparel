@props([
    'title' => null,
    'desc' => null,
    'padded' => true,
])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]']) }}>
    @if($title || isset($actions) || $desc)
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <div class="min-w-0">
                @if($title)
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">{{ $title }}</h3>
                @endif
                @if($desc)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $desc }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div @class([
        'p-4 sm:p-5' => $padded,
        'pt-0' => ! $padded && ! ($title || isset($actions) || $desc),
    ])>
        {{ $slot }}
    </div>
</div>
