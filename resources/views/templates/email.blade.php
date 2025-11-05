<x-mail::message>
    {{-- Header --}}
    @if (isset($header))
        # {{ $header }}
    @endif

    {{-- Main Content --}}
    {!! $content !!}

    {{-- Call to Action Button --}}
    @if (isset($actionUrl) && isset($actionText))
        <x-mail::button :url="$actionUrl">
            {{ $actionText }}
        </x-mail::button>
    @endif

    {{-- Footer --}}
    @if (isset($footer))
        {{ $footer }}
    @endif

    {{-- Signature --}}
    @if (isset($signature))
        Regards,<br>
        {{ $signature }}
    @endif
</x-mail::message>
