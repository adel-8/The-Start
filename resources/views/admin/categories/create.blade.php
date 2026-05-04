@extends('admin.layouts.app')

@section('title', __('admin.add_category'))

@section('content')
<div class="create-category-header">
    <h1>{{ __('admin.add_new_category') }}</h1>
    <a href="{{ route('admin.categories.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_categories') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.categories.store') }}" method="POST" class="category-form">
    @csrf

    <div class="form-row">
        <div class="form-group">
            <label for="name">{{ __('admin.name') }} *</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required>
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="slug">{{ __('admin.slug') }}</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug') }}">
            <small>{{ __('admin.slug_help') }}</small>
            @error('slug') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-group">
        <label for="description">{{ __('admin.description') }}</label>
        <textarea name="description" id="description" rows="4">{{ old('description') }}</textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="parent_id">{{ __('admin.parent_category') }}</label>
            <select name="parent_id" id="parent_id">
                <option value="">{{ __('admin.none') }}</option>
                @foreach($parentCategories as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            @error('parent_id') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="position">{{ __('admin.position') }}</label>
            <input type="number" name="position" id="position" value="{{ old('position', 0) }}" min="0">
            <small>{{ __('admin.position_help') }}</small>
        </div>

        <div class="form-group">
            <label for="status">{{ __('admin.status') }}</label>
            <select name="status" id="status">
                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
            </select>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">{{ __('admin.save_category') }}</button>
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection

@push('styles')
<style>
    .create-category-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .category-form {
        background: var(--color-surface);
        padding: 1.5rem;
        border-radius: 1rem;
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-sm);
    }
    .form-row {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    .form-group {
        flex: 1;
        min-width: 200px;
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--color-text);
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 0.6rem 0.8rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        font-family: inherit;
        font-size: 0.9rem;
        transition: 0.2s;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 2px rgba(100,95,125,0.1);
    }
    .error {
        color: var(--color-danger);
        font-size: 0.75rem;
        display: block;
        margin-top: 0.25rem;
    }
    .form-actions {
        margin-top: 1.5rem;
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-generate slug from name if slug field is empty
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    if (nameInput && slugInput) {
        nameInput.addEventListener('blur', function() {
            if (!slugInput.value) {
                let slug = this.value.toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            }
        });
    }
</script>
@endpush