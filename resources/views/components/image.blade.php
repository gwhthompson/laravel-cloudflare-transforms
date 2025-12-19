<img
    src="{{ $srcAttribute() }}"
    @if($srcsetAttribute())
        srcset="{{ $srcsetAttribute() }}"
    @endif
    @if($sizes)
        sizes="{{ $sizes }}"
    @endif
    {{ $attributes }}
>
