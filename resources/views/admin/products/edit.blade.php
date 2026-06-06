@extends('admin.layouts.app')

@section('title', __('admin.edit_product') . ': ' . $product->name)

@section('content')
<div class="edit-product-header">
    <h1>{{ __('admin.edit_product') }}: <em>{{ $product->name }}</em></h1>
    <a href="{{ route('admin.products.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_products') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('admin.products.update', $product) }}" method="POST"
      enctype="multipart/form-data" class="product-form" id="productForm">
    @csrf
    @method('PUT')

    {{-- ══ Basic info ══ --}}
    <div class="form-section">
        <h3 class="section-label">{{ __('admin.basic_information') }}</h3>

        <div class="form-row">
            <div class="form-group">
                <label for="name">{{ __('admin.product_name') }} *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required>
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="slug">{{ __('admin.slug') }} *</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $product->slug) }}" required>
                @error('slug')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-group">
            <label for="description">{{ __('admin.description') }}</label>
            <textarea name="description" id="description" rows="4">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="category_id">{{ __('admin.category') }}</label>
                <select name="category_id" id="category_id">
                    <option value="">{{ __('admin.none') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="status">{{ __('admin.status') }}</label>
                <select name="status" id="status">
                    <option value="active"   {{ old('status', $product->status) == 'active'   ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                    <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="buy_price">{{ __('admin.buy_price') }} *</label>
                <input type="number" step="0.01" name="buy_price" id="buy_price"
                       value="{{ old('buy_price', $product->buy_price) }}" required>
            </div>
            <div class="form-group">
                <label for="price">{{ __('admin.selling_price') }} *</label>
                <input type="number" step="0.01" name="price" id="price"
                       value="{{ old('price', $product->price) }}" required>
            </div>
            <div class="form-group">
                <label for="stock">{{ __('admin.stock_unlimited') }}</label>
                <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="is_new" value="1"
                           {{ old('is_new', $product->is_new) ? 'checked' : '' }}>
                    {{ __('admin.mark_as_new') }}
                </label>
            </div>
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="bestseller" value="1"
                           {{ old('bestseller', $product->bestseller) ? 'checked' : '' }}>
                    {{ __('admin.mark_as_bestseller') }}
                </label>
            </div>
        </div>
    </div>

    {{-- ══ Colors ══ --}}
    <div class="form-section">
        <div class="section-header-row">
            <h3 class="section-label">{{ __('admin.product_colors') }}</h3>
            <button type="button" class="btn-secondary btn-sm" id="addColorBtn">
                <i class="fas fa-plus"></i> {{ __('admin.add_color') }}
            </button>
        </div>
        <p class="hint">{{ __('admin.colors_hint') }}</p>

        <div id="colorsContainer">
            @foreach($product->colors as $color)
            <div class="color-row" data-idx="{{ $loop->index }}" data-existing-id="{{ $color->id }}">
                <input type="hidden"  name="colors[{{ $loop->index }}][id]" value="{{ $color->id }}">
                <input type="color"   class="hex-picker" value="{{ $color->hex_code }}"
                       style="background-color: {{ $color->hex_code }};">
                <input type="hidden"  name="colors[{{ $loop->index }}][hex_code]" class="hex-val" value="{{ $color->hex_code }}">
                <input type="text"    name="colors[{{ $loop->index }}][name]"    value="{{ $color->name }}"    placeholder="Name (EN)" required>
                <input type="text"    name="colors[{{ $loop->index }}][name_ar]" value="{{ $color->name_ar }}" placeholder="الاسم (AR)">
                <button type="button" class="btn-remove-color" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
                {{-- Hidden delete field, activated when row is removed --}}
                <input type="hidden" class="delete-color-flag" name="delete_color_ids[]"
                       value="{{ $color->id }}" disabled>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══ Existing Images ══ --}}
    @if($product->images->isNotEmpty())
    <div class="form-section">
        <h3 class="section-label">{{ __('admin.existing_images') }}</h3>
        <p class="hint">{{ __('admin.existing_images_hint') }}</p>

        <div class="gallery-preview" id="existingGallery">
            @foreach($product->images as $img)
            <div class="gallery-item {{ $img->is_primary ? 'is-primary' : '' }}" data-image-id="{{ $img->id }}">
                <img src="{{ asset($img->image_path) }}" alt="">
                <div class="gallery-item-controls">
                    {{-- Color assignment --}}
                    <select name="existing_image_color[{{ $img->id }}]" class="existing-color-assign">
                        <option value="">— No color —</option>
                        @foreach($product->colors as $color)
                            <option value="{{ $color->id }}"
                                {{ $img->color_id == $color->id ? 'selected' : '' }}>
                                {{ $color->name }}
                            </option>
                        @endforeach
                    </select>
                    {{-- Primary radio --}}
                    <label>
                        <input type="radio" name="primary_image_id" value="{{ $img->id }}"
                               {{ $img->is_primary ? 'checked' : '' }}>
                        Primary
                    </label>
                    {{-- Delete --}}
                    <label class="delete-label">
                        <input type="checkbox" class="delete-img-checkbox"
                               name="delete_image_ids[]" value="{{ $img->id }}">
                        <span>Delete</span>
                    </label>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ══ Upload New Images ══ --}}
    
    <div class="form-section">
        <h3 class="section-label">{{ __('admin.add_more_images') }}</h3>
        <div class="form-group">
            <input type="file" name="new_images[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp">
            <small>You can select multiple images. Hold Ctrl (Windows) or Cmd (Mac) to choose several.</small>
        </div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">{{ __('admin.update_product') }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection

@push('styles')
<style>
/* Reuse same styles as create form */
.form-section{background:var(--color-surface);padding:1.5rem;border-radius:1rem;border:1px solid var(--color-border);box-shadow:var(--shadow-sm);margin-bottom:1.5rem}
.section-label{font-size:1rem;font-weight:600;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--color-border)}
.section-header-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem}
.section-header-row .section-label{margin-bottom:0;padding-bottom:0;border-bottom:none}
.hint{font-size:.8rem;color:var(--color-text-secondary);margin-bottom:1rem}
.form-row{display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:.5rem}
.form-group{flex:1;min-width:180px;margin-bottom:1rem}
.checkbox-group{display:flex;align-items:center;margin-top:1.5rem}
.checkbox-group label{margin-bottom:0;font-weight:normal}
.form-group label{display:block;margin-bottom:.4rem;font-weight:500}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:.55rem .8rem;border:1px solid var(--color-border);border-radius:.5rem;font-family:inherit;font-size:.875rem;transition:.2s}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--color-primary);box-shadow:0 0 0 2px rgba(100,95,125,.1)}
.error{color:var(--color-danger);font-size:.75rem;display:block;margin-top:.2rem}
.form-actions{display:flex;gap:1rem;flex-wrap:wrap}
.color-row{display:flex;gap:10px;align-items:center;padding:10px;background:var(--color-bg,#f9fafb);border:1px solid var(--color-border);border-radius:.5rem;margin-bottom:8px;flex-wrap:wrap}
.color-row input[type="text"]{flex:1;min-width:120px}
.color-row input[type="color"]{width:40px;height:36px;padding:2px;border-radius:4px;border:1px solid var(--color-border);cursor:pointer}
.btn-remove-color{background:var(--color-danger,#ef4444);color:#fff;border:none;border-radius:4px;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.upload-zone{border:2px dashed var(--color-border);border-radius:.75rem;padding:2rem;text-align:center;cursor:pointer;transition:.2s;position:relative}
.upload-zone:hover,.upload-zone.drag-over{border-color:var(--color-primary);background:rgba(100,95,125,.04)}
.upload-zone i{font-size:2rem;color:var(--color-text-secondary);margin-bottom:.5rem}
.upload-zone p{color:var(--color-text-secondary);font-size:.875rem}
.upload-zone input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer}
.gallery-preview{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-top:1rem}
.gallery-item{border:1px solid var(--color-border);border-radius:.5rem;overflow:hidden;position:relative;background:#fff}
.gallery-item img{width:100%;aspect-ratio:1/1;object-fit:cover;display:block}
.gallery-item-controls{padding:8px;font-size:.75rem;display:flex;flex-direction:column;gap:6px}
.gallery-item-controls select{font-size:.75rem;padding:3px 6px;border:1px solid var(--color-border);border-radius:4px;width:100%}
.gallery-item-controls label{display:flex;align-items:center;gap:5px;cursor:pointer;font-size:.75rem}
.gallery-item-remove{position:absolute;top:5px;right:5px;background:rgba(239,68,68,.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.gallery-item.is-primary{border-color:var(--gold,#C9A96E);border-width:2px}
.gallery-item.marked-delete{opacity:.4;outline:2px dashed var(--color-danger)}
.delete-label{color:var(--color-danger)}
</style>
@endpush

@push('scripts')
<script>
(function(){
    /* ── Colors ── */
    let colorCount = {{ $product->colors->count() }};
    const colorsContainer = document.getElementById('colorsContainer');

    // Wire up existing color rows
    colorsContainer.querySelectorAll('.color-row').forEach(row => {
        const picker    = row.querySelector('.hex-picker');
        const hiddenHex = row.querySelector('.hex-val');
        picker.addEventListener('input', () => {
            hiddenHex.value = picker.value;
            picker.style.background = picker.value;
            refreshExistingColorDropdowns();
        });
        row.querySelector('.btn-remove-color').addEventListener('click', () => {
            // Enable hidden delete flag input, then hide the row
            const flag = row.querySelector('.delete-color-flag');
            if (flag) flag.disabled = false;
            row.style.display = 'none';
            refreshExistingColorDropdowns();
        });
    });

    // Add new color row
    document.getElementById('addColorBtn').addEventListener('click', () => {
        const idx = colorCount++;
        const row = document.createElement('div');
        row.className = 'color-row';
        row.dataset.idx = idx;
        row.innerHTML = `
            <input type="color"  class="hex-picker" value="#6b7280" style="background:#6b7280">
            <input type="hidden" name="colors[${idx}][hex_code]" class="hex-val" value="#6b7280">
            <input type="text"   name="colors[${idx}][name]"    placeholder="Name (EN)" required>
            <input type="text"   name="colors[${idx}][name_ar]" placeholder="الاسم (AR)">
            <button type="button" class="btn-remove-color"><i class="fas fa-times"></i></button>
        `;
        const picker = row.querySelector('.hex-picker');
        const hiddenHex = row.querySelector('.hex-val');
        picker.addEventListener('input', () => {
            hiddenHex.value = picker.value;
            picker.style.background = picker.value;
            refreshAllColorDropdowns();
        });
        row.querySelector('.btn-remove-color').addEventListener('click', () => {
            row.remove();
            refreshAllColorDropdowns();
        });
        colorsContainer.appendChild(row);
        refreshAllColorDropdowns();
    });

    function getVisibleColorRows() {
        return [...colorsContainer.querySelectorAll('.color-row')]
            .filter(r => r.style.display !== 'none');
    }

    function refreshExistingColorDropdowns() {
        const rows = getVisibleColorRows();
        document.querySelectorAll('.existing-color-assign').forEach(sel => {
            const current = sel.value;
            // Keep the static options already rendered by Blade
            // Just ensure they're still valid (don't re-render, existing IDs are DB IDs)
        });
    }

    function refreshAllColorDropdowns() {
        const rows = getVisibleColorRows();
        document.querySelectorAll('.new-color-assign').forEach(sel => {
            const current = sel.value;
            sel.innerHTML = '<option value="">— No color —</option>';
            rows.forEach((row, i) => {
                const realIdx = row.dataset.idx;
                const name    = row.querySelector('input[placeholder="Name (EN)"]').value || `Color ${i+1}`;
                const hex     = row.querySelector('.hex-val').value;
                const opt     = document.createElement('option');
                opt.value = realIdx;
                opt.textContent = name;
                if (String(current) === String(realIdx)) opt.selected = true;
                sel.appendChild(opt);
            });
        });
    }

    /* ── Existing images — mark for deletion ── */
    document.querySelectorAll('.delete-img-checkbox').forEach(cb => {
        cb.addEventListener('change', () => {
            const item = cb.closest('.gallery-item');
            item.classList.toggle('marked-delete', cb.checked);
        });
    });

    /* ── New images upload ── */
    const galleryInput   = document.getElementById('galleryInput');
    const newGallery     = document.getElementById('newGalleryPreview');
    const uploadZone     = document.getElementById('uploadZone');
    let newFiles = [];
    let newPrimaryIdx = null;

    uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
    uploadZone.addEventListener('drop', e => {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');
        addNewFiles([...e.dataTransfer.files]);
    });
    galleryInput.addEventListener('change', () => {
        addNewFiles([...galleryInput.files]);
        galleryInput.value = '';
    });

    function addNewFiles(files) {
        files.forEach(f => {
            if (!f.type.startsWith('image/')) return;
            newFiles.push(f);
            renderNewItem(f, newFiles.length - 1);
        });
        syncNewInput();
    }

    function renderNewItem(file, idx) {
        const reader = new FileReader();
        reader.onload = e => {
            const item = document.createElement('div');
            item.className = 'gallery-item';
            item.dataset.idx = idx;
            item.innerHTML = `
                <img src="${e.target.result}" alt="">
                <button type="button" class="gallery-item-remove"><i class="fas fa-times"></i></button>
                <div class="gallery-item-controls">
                    <select class="new-color-assign" name="new_image_color_idx[${idx}]">
                        <option value="">— No color —</option>
                    </select>
                    <label>
                        <input type="radio" name="new_primary_idx" value="${idx}">
                        Set as primary
                    </label>
                </div>
            `;
            item.querySelector('.gallery-item-remove').addEventListener('click', () => {
                newFiles.splice(idx, 1);
                renderAllNew();
                syncNewInput();
            });
            newGallery.appendChild(item);
            refreshAllColorDropdowns();
        };
        reader.readAsDataURL(file);
    }

    function renderAllNew() {
        newGallery.innerHTML = '';
        newFiles.forEach((f, i) => renderNewItem(f, i));
    }

    function syncNewInput() {
        const dt = new DataTransfer();
        newFiles.forEach(f => dt.items.add(f));
        galleryInput.files = dt.files;
    }
})();
</script>
@endpush