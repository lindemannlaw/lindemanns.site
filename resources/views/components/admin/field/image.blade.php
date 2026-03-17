@props([
    'name' => '',
    'required' => true,
    'placeholder' => null,
    'fieldAttrs' => null,
    'accept' => '.png, .jpeg, .jpg, .webp',
    'ratio' => '4x3',
    'src' => null,
    'fit' => 'cover',
    'rounded' => 'rounded',
    'shadow' => false,
    'viewImageClasses' => '',
    'viewImageAttributes' => '',
    'youtubeVideoId' => null,
    'compact' => false,
])

@php
    $hasExistingSrc = (bool)$src;
    if (!$compact) {
        if (!$src) {
            if ($youtubeVideoId) {
                $src = 'https://i3.ytimg.com/vi/' . $youtubeVideoId . '/maxresdefault.jpg';
            } else {
                $ratioExplode = explode('x', $ratio);
                $ratioWidth = $ratioExplode[0];
                $ratioHeight = $ratioExplode[1];
                $src = '/img/default' . ($ratioWidth < $ratioHeight ? '-vertical' : '') . '.svg';
            }
        }
    }
@endphp

@if($compact)
    {{-- Compact mode: small inline button, small preview after upload --}}
    <label data-preview-image-file {{ $attributes->merge(['class' => 'd-inline-flex cursor-pointer align-items-center']) }}>
        <div data-pif-picture data-pif-dropzone data-pif-compact
             class="d-flex align-items-center gap-2 rounded border px-2 py-1 {{ $hasExistingSrc ? 'border-solid bg-white' : 'border-dashed bg-light' }}"
             style="border-color: rgba(0,0,0,.2);">
            <img
                data-pif-image
                src="{{ $src ?: '' }}"
                alt="preview"
                class="rounded img-cover {{ $hasExistingSrc ? '' : 'd-none' }}"
                style="width: 64px; height: 42px; object-fit: cover;"
            >
            <span data-pif-compact-placeholder class="d-flex align-items-center gap-1 text-primary small {{ $hasExistingSrc ? 'd-none' : '' }}">
                <span class="d-flex align-items-center justify-content-center rounded-circle border border-primary" style="width:22px;height:22px;flex-shrink:0;font-size:14px;line-height:1;">+</span>
                {{ $placeholder ?: 'Bild hinzufügen' }}
            </span>
            @if($hasExistingSrc)
                <span class="small text-muted" data-pif-compact-change>Ändern</span>
            @endif
        </div>

        <input
            data-form-control
            data-pif-field
            type="file"
            name="{{ $name }}"
            class="visually-hidden"
            accept="{{ $accept }}"
            {{ $required ? 'required' : null }}
            {{ $hasExistingSrc ? 'data-pif-has-image' : null }}
        />
    </label>
@else
    <label data-preview-image-file {{ $attributes->merge(['class' => 'd-block position-relative cursor-pointer']) }}>
        <span class="d-block ratio ratio-{{ $ratio }}">
            <picture data-pif-picture data-pif-dropzone class="dropzone d-flex w-100 h-100 position-absolute border border-dark rounded border-opacity-25 bg-white {{ $src ? 'p-3 border-solid' : 'p-4 border-dashed' }}">
                <img
                    data-pif-image
                    data-pif-view-classes="{{ $viewImageClasses }}"
                    {{ $viewImageAttributes }}
                    src="{{ $src }}"
                    alt="image"
                    class="{{ $rounded . ' img-' . $fit }}"
                    style="filter: {{ $shadow ? 'drop-shadow(0 0 5px rgba(0, 0, 0, 0.5))' : 'none' }}"
                />
            </picture>
        </span>

        <input
            data-form-control
            data-pif-field
            type="file"
            name="{{ $name }}"
            class="visually-hidden start-0 bottom-0 w-100 h-100 {{ $src ? 'inited' : null }}"
            accept="{{ $accept }}"
            {{ $required ? 'required' : null }}
            {{ $src ? 'data-pif-has-image' : null }}
        />

        <span class="form-control-placeholder {{ $src ? 'glued' : null }}">{{ $placeholder }} {!! $required ? '<span class="text-danger opacity-75"> *</span>' : null !!}</span>
    </label>
@endif
