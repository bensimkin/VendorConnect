{{-- Modern Button Component --}}
{{-- Usage: @include('components.modern-button', ['text' => 'Save', 'type' => 'submit', 'variant' => 'primary', 'icon' => 'bx-save']) --}}

<button 
    type="{{ $type ?? 'button' }}" 
    class="btn btn-{{ $variant ?? 'primary' }} {{ $size ?? '' }} {{ $class ?? '' }}"
    {{ isset($loading) && $loading ? 'data-loading' : '' }}
    {{ isset($disabled) && $disabled ? 'disabled' : '' }}
    @if(isset($onclick)) onclick="{{ $onclick }}" @endif
    @if(isset($id)) id="{{ $id }}" @endif
>
    @if(isset($icon))
    <i class="bx {{ $icon }} {{ isset($text) ? 'me-2' : '' }}"></i>
    @endif
    
    @if(isset($text))
    <span>{{ $text }}</span>
    @endif
</button>

<style>
.btn {
    position: relative;
    overflow: hidden;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all var(--transition-fast);
}

.btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn:active::before {
    width: 300px;
    height: 300px;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.125rem;
}

.btn i {
    font-size: 1.125em;
}

/* Button with loading state */
.btn[data-loading]:disabled {
    cursor: wait;
    opacity: 0.8;
}

.btn .spinner-border {
    width: 1rem;
    height: 1rem;
    border-width: 0.15em;
}
</style>
