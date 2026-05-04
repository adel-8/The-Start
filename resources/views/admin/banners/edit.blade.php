@extends('admin.layouts.app')

@section('title', __('admin.edit_banner') . ': ' . ($banner->title ?? $banner->id))

@section('content')
<div class="edit-banner-header">
    <h1>{{ __('admin.edit_banner') }}: {{ $banner->title ?? '#' . $banner->id }}</h1>
    <a href="{{ route('admin.banners.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_banners') }}
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

<form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="banner-form">
    @csrf
    @method('PUT')

    <div class="form-row">
        <div class="form-group">
            <label for="title">{{ __('admin.title_optional') }}</label>
            <input type="text" name="title" id="title" value="{{ old('title', $banner->title) }}">
            @error('title') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="link">{{ __('admin.link_url') }}</label>
            <input type="url" name="link" id="link" value="{{ old('link', $banner->link) }}" placeholder="https://...">
            @error('link') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="position">{{ __('admin.position') }}</label>
            <input type="number" name="position" id="position" value="{{ old('position', $banner->position) }}" min="0">
            <small>{{ __('admin.position_help') }}</small>
            @error('position') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="status">{{ __('admin.status') }}</label>
            <select name="status" id="status">
                <option value="1" {{ old('status', $banner->status) == '1' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                <option value="0" {{ old('status', $banner->status) == '0' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
            </select>
        </div>

        <div class="form-group">
            <label for="device_type">{{ __('admin.device_targeting') }}</label>
            <select name="device_type" id="device_type">
                <option value="all" {{ old('device_type', $banner->device_type) == 'all' ? 'selected' : '' }}>{{ __('admin.all_devices') }}</option>
                <option value="mobile" {{ old('device_type', $banner->device_type) == 'mobile' ? 'selected' : '' }}>{{ __('admin.mobile') }}</option>
                <option value="desktop" {{ old('device_type', $banner->device_type) == 'desktop' ? 'selected' : '' }}>{{ __('admin.desktop') }}</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="starts_at">{{ __('admin.starts_at') }}</label>
            <input type="datetime-local" name="starts_at" id="starts_at" 
                   value="{{ old('starts_at', $banner->starts_at ? $banner->starts_at->format('Y-m-d\TH:i') : '') }}">
            <small>{{ __('admin.leave_blank_always') }}</small>
            @error('starts_at') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="ends_at">{{ __('admin.ends_at') }}</label>
            <input type="datetime-local" name="ends_at" id="ends_at" 
                   value="{{ old('ends_at', $banner->ends_at ? $banner->ends_at->format('Y-m-d\TH:i') : '') }}">
            <small>{{ __('admin.leave_blank_always') }}</small>
            @error('ends_at') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-group">
        <label>{{ __('admin.current_image') }}</label>
        <div class="current-image">
            <img src="{{ asset($banner->image_url) }}" class="current-image-preview" alt="{{ $banner->title }}">
        </div>
    </div>

    <div class="form-group">
        <label for="image">{{ __('admin.replace_image_optional') }}</label>
        <input type="file" name="image" id="image" accept="image/*">
        <small>{{ __('admin.image_help') }}</small>
        @error('image') <span class="error">{{ $message }}</span> @enderror
    </div>

    <div class="form-group" id="imagePreviewContainer" style="display: none;">
        <label>{{ __('admin.new_image_preview') }}</label>
        <img id="imagePreview" class="image-preview">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">{{ __('admin.update_banner') }}</button>
        <a href="{{ route('admin.banners.index') }}" class="btn-secondary">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection

@push('styles')
<style>
    .edit-banner-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .banner-form {
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
        position: relative;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--color-text);
    }
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 0.6rem 0.8rem;
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        font-family: inherit;
        font-size: 0.9rem;
        transition: 0.2s;
    }
    .form-group input:focus,
    .form-group select:focus {
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
    .current-image {
        margin-top: 0.5rem;
    }
    .current-image-preview {
        max-width: 200px;
        max-height: 150px;
        border-radius: 0.5rem;
        border: 1px solid var(--color-border);
    }
    .image-preview {
        max-width: 200px;
        max-height: 150px;
        border-radius: 0.5rem;
        margin-top: 0.5rem;
        border: 1px solid var(--color-border);
    }
    small {
        display: block;
        font-size: 0.7rem;
        color: var(--color-muted);
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
    // Image preview on file select
    const imageInput = document.getElementById('image');
    const previewContainer = document.getElementById('imagePreviewContainer');
    const previewImage = document.getElementById('imagePreview');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImage.src = event.target.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
                previewImage.src = '';
            }
        });
    }
</script>
@endpush