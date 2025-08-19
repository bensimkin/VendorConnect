{{-- Modern Form Input Component --}}
{{-- Usage: @include('components.form-input', ['label' => 'Task Title', 'name' => 'title', 'type' => 'text', 'required' => true]) --}}

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if(isset($required) && $required)
            <span class="text-danger">*</span>
        @endif
    </label>
    
    <div class="input-group">
        @if(isset($icon))
        <span class="input-group-text">
            <i class="bx {{ $icon }}"></i>
        </span>
        @endif
        
        @if(isset($type) && $type === 'textarea')
            <textarea 
                class="form-control @error($name) is-invalid @enderror" 
                id="{{ $name }}" 
                name="{{ $name }}"
                rows="{{ $rows ?? 3 }}"
                placeholder="{{ $placeholder ?? '' }}"
                {{ isset($required) && $required ? 'required' : '' }}
            >{{ old($name, $value ?? '') }}</textarea>
        @elseif(isset($type) && $type === 'select')
            <select 
                class="form-select @error($name) is-invalid @enderror" 
                id="{{ $name }}" 
                name="{{ $name }}"
                {{ isset($required) && $required ? 'required' : '' }}
            >
                <option value="">{{ $placeholder ?? 'Select an option' }}</option>
                @foreach($options as $key => $option)
                    <option value="{{ $key }}" {{ old($name, $value ?? '') == $key ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endforeach
            </select>
        @else
            <input 
                type="{{ $type ?? 'text' }}" 
                class="form-control @error($name) is-invalid @enderror" 
                id="{{ $name }}" 
                name="{{ $name }}"
                value="{{ old($name, $value ?? '') }}"
                placeholder="{{ $placeholder ?? '' }}"
                {{ isset($required) && $required ? 'required' : '' }}
                {{ isset($readonly) && $readonly ? 'readonly' : '' }}
                {{ isset($disabled) && $disabled ? 'disabled' : '' }}
            >
        @endif
        
        @if(isset($append))
        <span class="input-group-text">
            {{ $append }}
        </span>
        @endif
    </div>
    
    @if(isset($help))
    <small class="form-text text-muted">{{ $help }}</small>
    @endif
    
    @error($name)
    <div class="invalid-feedback d-block">
        {{ $message }}
    </div>
    @enderror
</div>

<style>
.form-label {
    font-weight: 500;
    color: var(--gray-700);
    margin-bottom: 0.5rem;
}

.input-group-text {
    background: var(--gray-50);
    border: 2px solid var(--gray-300);
    color: var(--gray-600);
}

.form-control:focus + .input-group-text,
.input-group-text:has(+ .form-control:focus) {
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.invalid-feedback {
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>
