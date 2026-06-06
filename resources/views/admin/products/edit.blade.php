@extends('admin.layouts.app')

@section('title', __('admin.edit_product') . ': ' . $product->name)

@section('content')
<div class="edit-product-header">
    <h1>{{ __('admin.edit_product') }}: {{ $product->name }}</h1>
    <a href="{{ route('admin.products.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_products') }}
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

<form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="product-form">
    @csrf
    @method('PUT')

    <!-- Basic product fields (name, slug, description, etc.) – same as before -->
    <div class="form-row">
        <div class="form-group">
            <label for="name">{{ __('admin.product_name') }} *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required>
        </div>
        <div class="form-group">
            <label for="slug">{{ __('admin.slug') }} *</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" required>
        </div>
    </div>

    <div class="form-group">
        <label for="description">{{ __('admin.description') }}</label>
        <textarea name="description" id="description" rows="5">{{ old('description', $product->description) }}</textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="category_id">{{ __('admin.category') }}</label>
            <select name="category_id" id="category_id">
                <option value="">{{ __('admin.none') }}</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="buy_price">{{ __('admin.buy_price') }} *</label>
            <input type="number" step="0.01" name="buy_price" id="buy_price" value="{{ old('buy_price', $product->buy_price) }}" required>
        </div>
        <div class="form-group">
            <label for="price">{{ __('admin.selling_price') }} *</label>
            <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $product->price) }}" required>
        </div>
        <div class="form-group">
            <label for="stock">{{ __('admin.stock_unlimited') }}</label>
            <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group checkbox-group">
            <label><input type="checkbox" name="is_new" value="1" {{ old('is_new', $product->is_new) ? 'checked' : '' }}> {{ __('admin.mark_as_new') }}</label>
        </div>
        <div class="form-group checkbox-group">
            <label><input type="checkbox" name="bestseller" value="1" {{ old('bestseller', $product->bestseller) ? 'checked' : '' }}> {{ __('admin.mark_as_bestseller') }}</label>
        </div>
        <div class="form-group">
            <label for="status">{{ __('admin.status') }}</label>
            <select name="status" id="status">
                <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
            </select>
        </div>
    </div>

    <!-- Main image -->
    <div class="form-group">
        <label>{{ __('admin.current_image') }}</label>
        <div class="current-image">
            @if($product->image_url)
                <img src="{{ asset($product->image_url) }}" alt="{{ $product->name }}">
            @else
                <span class="no-image">{{ __('admin.no_image') }}</span>
            @endif
        </div>
    </div>

    <div class="form-group">
        <label for="image">{{ __('admin.new_image_optional') }}</label>
        <input type="file" name="image" id="image" accept="image/*">
    </div>

    <div class="form-group" id="imagePreviewContainer" style="display: none;">
        <label>{{ __('admin.new_image_preview') }}</label>
        <img id="imagePreview" style="max-width: 200px; max-height: 200px;">
    </div>

    <!-- ========== COLOR VARIATIONS (FIXED) ========== -->
    <div class="form-group">
        <h3>{{ __('admin.color_variations') }}</h3>
        <div id="variations-container">
            @if($product->colorVariations && $product->colorVariations->count())
                @foreach($product->colorVariations as $var)
                    <div class="variation-row" data-id="{{ $var->id }}">
                        <!-- Existing variation: send its ID as separate array for deletion tracking -->
                        <input type="hidden" name="variation_ids[]" value="{{ $var->id }}">
                        <!-- Use variation ID as array key to avoid index mismatch -->
                        <div class="form-group">
                            <label>{{ __('admin.color_name') }}</label>
                            <input type="text" name="variations[{{ $var->id }}][attribute_value]" value="{{ $var->attribute_value }}" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('admin.sku') }}</label>
                            <input type="text" name="variations[{{ $var->id }}][sku]" value="{{ $var->sku }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __('admin.price_override') }}</label>
                            <input type="number" step="0.01" name="variations[{{ $var->id }}][price]" value="{{ $var->price }}">
                        </div>
                        <div class="form-group">
                            <label>{{ __('admin.stock') }}</label>
                            <input type="number" name="variations[{{ $var->id }}][stock]" value="{{ $var->stock }}" required>
                        </div>
                        <div class="form-group">
                            <label>{{ __('admin.current_color_image') }}</label>
                            @if($var->image_url)
                                <div><img src="{{ asset($var->image_url) }}" style="max-height: 50px;"></div>
                            @endif
                            <input type="file" name="variation_images[{{ $var->id }}]" accept="image/*">
                        </div>
                        <button type="button" class="remove-variation btn-sm btn-danger">{{ __('admin.remove') }}</button>
                    </div>
                @endforeach
            @endif
        </div>
        <button type="button" id="addVariation" class="btn-secondary btn-sm">+ {{ __('admin.add_color') }}</button>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">{{ __('admin.update_product') }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection

@push('styles')
<style>
    /* Your existing styles */
    .variation-row {
        background: var(--color-background);
        border: 1px solid var(--color-border);
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
    }
    .variation-row .form-group {
        flex: 1;
        min-width: 150px;
        margin-bottom: 0;
    }
    .remove-variation {
        background: var(--color-danger);
        color: white;
        border: none;
        padding: 0.5rem 0.8rem;
        border-radius: 0.5rem;
        cursor: pointer;
        height: 38px;
        align-self: flex-end;
    }
</style>
@endpush

@push('scripts')
<script>
    // Image preview for main image
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

    // Variation management – new rows use a timestamp as a unique placeholder key
    const variationsContainer = document.getElementById('variations-container');
    const addVariationBtn = document.getElementById('addVariation');

    if (variationsContainer && addVariationBtn) {
        addVariationBtn.addEventListener('click', () => {
            const timestamp = Date.now();
            const newRow = document.createElement('div');
            newRow.className = 'variation-row';
            // New rows do NOT have a variation_id input – they will be created as new records
            newRow.innerHTML = `
                <div class="form-group">
                    <label>{{ __('admin.color_name') }}</label>
                    <input type="text" name="variations[${timestamp}][attribute_value]" placeholder="{{ __('admin.color_example') }}" required>
                </div>
                <div class="form-group">
                    <label>{{ __('admin.sku_optional') }}</label>
                    <input type="text" name="variations[${timestamp}][sku]" placeholder="SKU">
                </div>
                <div class="form-group">
                    <label>{{ __('admin.price_override') }}</label>
                    <input type="number" step="0.01" name="variations[${timestamp}][price]" placeholder="{{ __('admin.leave_blank') }}">
                </div>
                <div class="form-group">
                    <label>{{ __('admin.stock') }}</label>
                    <input type="number" name="variations[${timestamp}][stock]" value="0" required>
                </div>
                <div class="form-group">
                    <label>{{ __('admin.color_image') }}</label>
                    <input type="file" name="variation_images[${timestamp}]" accept="image/*">
                </div>
                <button type="button" class="remove-variation btn-sm btn-danger">{{ __('admin.remove') }}</button>
            `;
            variationsContainer.appendChild(newRow);
            // Attach remove handler
            newRow.querySelector('.remove-variation').addEventListener('click', () => newRow.remove());
        });

        // Attach remove handler to existing rows
        document.querySelectorAll('.remove-variation').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.variation-row').remove();
            });
        });
    }
</script>
@endpush