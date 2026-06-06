@extends('admin.layouts.app')

@section('title', __('admin.add_product'))

@section('content')
<div class="create-product-header">
    <h1>{{ __('admin.add_product') }}</h1>
    <a href="{{ route('admin.products.index') }}" class="btn-secondary">
        <i class="fas fa-arrow-left"></i> {{ __('admin.back_to_products') }}
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul>@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ route('admin.products.store') }}" method="POST"
      enctype="multipart/form-data" class="product-form" id="productForm">
    @csrf

    {{-- ══ Basic info ══ --}}
    <div class="form-section">
        <h3 class="section-label">{{ __('admin.basic_information') }}</h3>

        <div class="form-row">
            <div class="form-group">
                <label for="name">{{ __('admin.product_name') }} *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
                @error('name')<span class="error">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="slug">{{ __('admin.slug') }} *</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required>
                <small>{{ __('admin.slug_help') }}</small>
                @error('slug')<span class="error">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-group">
            <label for="description">{{ __('admin.description') }}</label>
            <textarea name="description" id="description" rows="4">{{ old('description') }}</textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="category_id">{{ __('admin.category') }}</label>
                <select name="category_id" id="category_id">
                    <option value="">{{ __('admin.none') }}</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="status">{{ __('admin.status') }}</label>
                <select name="status" id="status">
                    <option value="active"   {{ old('status','active')   == 'active'   ? 'selected' : '' }}>{{ __('admin.active') }}</option>
                    <option value="inactive" {{ old('status','active')   == 'inactive' ? 'selected' : '' }}>{{ __('admin.inactive') }}</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="buy_price">{{ __('admin.buy_price') }} *</label>
                <input type="number" step="0.01" name="buy_price" id="buy_price" value="{{ old('buy_price') }}" required>
            </div>
            <div class="form-group">
                <label for="price">{{ __('admin.selling_price') }} *</label>
                <input type="number" step="0.01" name="price" id="price" value="{{ old('price') }}" required>
            </div>
            <div class="form-group">
                <label for="stock">{{ __('admin.stock_unlimited') }}</label>
                <input type="number" name="stock" id="stock" value="{{ old('stock') }}">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="is_new" value="1" {{ old('is_new') ? 'checked' : '' }}>
                    {{ __('admin.mark_as_new') }}
                </label>
            </div>
            <div class="form-group checkbox-group">
                <label>
                    <input type="checkbox" name="bestseller" value="1" {{ old('bestseller') ? 'checked' : '' }}>
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
            {{-- Rows injected by JS --}}
        </div>
    </div>

    {{-- ══ Gallery Images ══ --}}
    <div class="form-section">
        <h3 class="section-label">{{ __('admin.product_images') }}</h3>
        <p class="hint">{{ __('admin.images_hint') }}</p>

        <div class="upload-zone" id="uploadZone">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>{{ __('admin.click_or_drag_images') }}</p>
            <input type="file" name="new_images[]" id="galleryInput"
                   multiple accept="image/jpeg,image/png,image/jpg,image/webp">
        </div>

        <div class="gallery-preview" id="galleryPreview"></div>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-primary">{{ __('admin.save_product') }}</button>
        <a href="{{ route('admin.products.index') }}" class="btn-secondary">{{ __('admin.cancel') }}</a>
    </div>
</form>
@endsection

@push('styles')
@vite('resources/css/admin-product-form.css')
<style>
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
.form-actions{display:flex;gap:1rem;flex-wrap:wrap;margin-top:.5rem}

/* Color rows */
.color-row{display:flex;gap:10px;align-items:center;padding:10px;background:var(--color-bg,#f9fafb);border:1px solid var(--color-border);border-radius:.5rem;margin-bottom:8px;flex-wrap:wrap}
.color-row .color-preview{width:36px;height:36px;border-radius:6px;border:1px solid var(--color-border);flex-shrink:0;cursor:pointer}
.color-row input[type="text"]{flex:1;min-width:120px}
.color-row input[type="color"]{width:40px;height:36px;padding:2px;border-radius:4px;border:1px solid var(--color-border);cursor:pointer}
.btn-remove-color{background:var(--color-danger,#ef4444);color:#fff;border:none;border-radius:4px;width:30px;height:30px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* Upload zone */
.upload-zone{border:2px dashed var(--color-border);border-radius:.75rem;padding:2rem;text-align:center;cursor:pointer;transition:.2s;position:relative}
.upload-zone:hover,.upload-zone.drag-over{border-color:var(--color-primary);background:rgba(100,95,125,.04)}
.upload-zone i{font-size:2rem;color:var(--color-text-secondary);margin-bottom:.5rem}
.upload-zone p{color:var(--color-text-secondary);font-size:.875rem}
.upload-zone input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer}

/* Gallery preview grid */
.gallery-preview{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;margin-top:1rem}
.gallery-item{border:1px solid var(--color-border);border-radius:.5rem;overflow:hidden;position:relative;background:#fff}
.gallery-item img{width:100%;aspect-ratio:1/1;object-fit:cover;display:block}
.gallery-item-controls{padding:8px;font-size:.75rem;display:flex;flex-direction:column;gap:6px}
.gallery-item-controls select{font-size:.75rem;padding:3px 6px;border:1px solid var(--color-border);border-radius:4px;width:100%}
.gallery-item-controls label{display:flex;align-items:center;gap:5px;cursor:pointer}
.gallery-item-remove{position:absolute;top:5px;right:5px;background:rgba(239,68,68,.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:11px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.gallery-item.is-primary{border-color:var(--gold,#C9A96E);border-width:2px}
</style>
@endpush

@push('scripts')
<script>
(function(){
    /* ── Auto-slug ── */
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', () => {
            if (!slugInput.dataset.manual) {
                slugInput.value = nameInput.value
                    .toLowerCase()
                    .replace(/[\s_]+/g, '-')
                    .replace(/[^a-z0-9-]/g, '')
                    .replace(/-+/g, '-');
            }
        });
        slugInput.addEventListener('input', () => slugInput.dataset.manual = '1');
    }

    /* ── Colors ── */
    let colorCount = 0;
    const colorsContainer = document.getElementById('colorsContainer');
    const addColorBtn = document.getElementById('addColorBtn');

    function addColorRow(data = {}) {
        const idx = colorCount++;
        const row = document.createElement('div');
        row.className = 'color-row';
        row.dataset.idx = idx;
        row.innerHTML = `
            <input type="color" class="hex-picker" value="${data.hex || '#6b7280'}"
                   title="Pick color">
            <input type="hidden" name="colors[${idx}][hex_code]" class="hex-val" value="${data.hex || '#6b7280'}">
            <input type="text" name="colors[${idx}][name]" placeholder="Name (EN)" value="${data.name||''}" required>
            <input type="text" name="colors[${idx}][name_ar]" placeholder="الاسم (AR)" value="${data.name_ar||''}">
            <button type="button" class="btn-remove-color" title="Remove"><i class="fas fa-times"></i></button>
        `;
        const picker = row.querySelector('.hex-picker');
        const hiddenHex = row.querySelector('.hex-val');
        picker.addEventListener('input', () => {
            hiddenHex.value = picker.value;
            picker.style.background = picker.value;
            refreshColorDropdowns();
        });
        picker.style.background = picker.value;
        row.querySelector('.btn-remove-color').addEventListener('click', () => {
            row.remove();
            refreshColorDropdowns();
        });
        colorsContainer.appendChild(row);
        refreshColorDropdowns();
    }

    addColorBtn.addEventListener('click', () => addColorRow());

    /* ── Gallery ── */
    const galleryInput = document.getElementById('galleryInput');
    const galleryPreview = document.getElementById('galleryPreview');
    const uploadZone = document.getElementById('uploadZone');
    let galleryFiles = []; // DataTransfer-based file list
    let primaryIdx = null;

    uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('drag-over'));
    uploadZone.addEventListener('drop', e => {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');
        addFiles([...e.dataTransfer.files]);
    });
    galleryInput.addEventListener('change', () => {
        addFiles([...galleryInput.files]);
        galleryInput.value = '';
    });

    function addFiles(files) {
        files.forEach(file => {
            if (!file.type.startsWith('image/')) return;
            galleryFiles.push(file);
            renderGalleryItem(file, galleryFiles.length - 1);
        });
        syncFileInput();
    }

    function renderGalleryItem(file, idx) {
        const reader = new FileReader();
        reader.onload = e => {
            const item = document.createElement('div');
            item.className = 'gallery-item' + (primaryIdx === idx ? ' is-primary' : '');
            item.dataset.idx = idx;
            item.innerHTML = `
                <img src="${e.target.result}" alt="">
                <button type="button" class="gallery-item-remove"><i class="fas fa-times"></i></button>
                <div class="gallery-item-controls">
                    <select class="color-assign" name="new_image_color_idx[${idx}]">
                        <option value="">— No color —</option>
                    </select>
                    <label>
                        <input type="radio" name="new_primary_idx" value="${idx}"
                               ${primaryIdx === idx ? 'checked' : ''}>
                        Set as primary
                    </label>
                </div>
            `;
            item.querySelector('.gallery-item-remove').addEventListener('click', () => {
                galleryFiles.splice(idx, 1);
                renderAllGallery();
                syncFileInput();
            });
            item.querySelector('input[type="radio"]').addEventListener('change', () => {
                primaryIdx = idx;
                document.querySelectorAll('.gallery-item').forEach(el => el.classList.remove('is-primary'));
                item.classList.add('is-primary');
            });
            galleryPreview.appendChild(item);
            refreshColorDropdowns();
        };
        reader.readAsDataURL(file);
    }

    function renderAllGallery() {
        galleryPreview.innerHTML = '';
        galleryFiles.forEach((f, i) => renderGalleryItem(f, i));
    }

    function syncFileInput() {
        const dt = new DataTransfer();
        galleryFiles.forEach(f => dt.items.add(f));
        galleryInput.files = dt.files;
    }

    /* ── Sync color dropdowns ── */
    function refreshColorDropdowns() {
        const rows = colorsContainer.querySelectorAll('.color-row');
        document.querySelectorAll('.color-assign').forEach(sel => {
            const current = sel.value;
            sel.innerHTML = '<option value="">— No color —</option>';
            rows.forEach((row, i) => {
                const name = row.querySelector('input[placeholder="Name (EN)"]').value || `Color ${i}`;
                const hex  = row.querySelector('.hex-val').value;
                const opt  = document.createElement('option');
                opt.value = i;
                opt.textContent = name;
                opt.style.borderLeft = `12px solid ${hex}`;
                if (String(current) === String(i)) opt.selected = true;
                sel.appendChild(opt);
            });
        });
    }
})();
</script>
@endpush