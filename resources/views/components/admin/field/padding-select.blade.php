@props(['name' => '', 'value' => 0, 'placeholder' => 'Padding'])

@php
$paddingOptions = [
    0   => '0',
    4   => 'xs (4px)',
    8   => 'sm (8px)',
    12  => 'md (12px)',
    16  => 'lg (16px)',
    20  => 'xl (20px)',
    24  => '2xl (24px)',
    32  => '3xl (32px)',
    48  => '4xl (48px)',
    64  => '5xl (64px)',
    72  => '6xl (72px)',
    96  => '7xl (96px)',
    144 => '8xl (144px)',
    192 => '9xl (192px)',
];
$intValue = (int)($value ?? 0);
$isCustom = $value !== null && $value !== '' && !array_key_exists($intValue, $paddingOptions);
@endphp

<div data-padding-select-wrapper>
    <label class="form-label small">{{ $placeholder }}</label>
    {{-- Hidden input always carries the actual value + name (gets reindexed by reindexBuilder) --}}
    <input type="hidden" name="{{ $name }}" value="{{ $intValue }}" data-padding-value>
    <select class="form-select form-select-sm" data-padding-select>
        @foreach($paddingOptions as $px => $label)
            <option value="{{ $px }}" {{ !$isCustom && $intValue === $px ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
        <option value="custom" {{ $isCustom ? 'selected' : '' }}>Eigener Wert…</option>
    </select>
    <input
        type="number"
        class="form-control form-control-sm mt-1 {{ $isCustom ? '' : 'd-none' }}"
        value="{{ $isCustom ? $intValue : '' }}"
        min="0"
        max="500"
        placeholder="px"
        data-padding-custom-input
    >
</div>
