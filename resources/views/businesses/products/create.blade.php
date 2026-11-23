@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Add Product</h2>
            <p class="text-muted mb-0">Create a product and configure its variations.</p>
        </div>
        <a href="{{ route('business.products.index', ['business' => $business->slug]) }}"
           class="btn btn-outline-secondary">Back to Products</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('business.products.store', ['business' => $business->slug]) }}"
          enctype="multipart/form-data"
          id="productForm">
        @csrf


        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <label class="form-label">Branch for Opening Stock <span class="text-danger">*</span></label>
                <select name="branch_id" class="form-select" required>
                    <option value="">Select branch</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}"
                            @selected(old('branch_id', $branches->firstWhere('is_main', true)?->id ?? $branches->first()?->id) == $branch->id)>
                            {{ $branch->name }}{{ $branch->is_main ? ' (Main Branch)' : '' }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Opening stock for all variations will be recorded at this branch.</small>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name') }}" required maxlength="255">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control"
                               value="{{ old('brand') }}" maxlength="255">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Product Type <span class="text-danger">*</span></label>
                        <select name="product_type" id="productType" class="form-select" required>
                            <option value="simple" @selected(old('product_type', 'simple') === 'simple')>Simple Product</option>
                            <option value="variable" @selected(old('product_type') === 'variable')>Variable Product</option>
                        </select>
                        <small class="text-muted">Simple products have one variation. Variable products can have multiple variations.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" id="productImage"
                               class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <small class="text-muted">JPG, JPEG, PNG or WEBP. Maximum 2MB.</small>
                        <div class="mt-3" id="imagePreviewWrap" style="display:none;">
                            <img id="imagePreview" src="" alt="Product preview"
                                 style="width:130px;height:130px;object-fit:cover;border-radius:10px;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" @selected(old('is_active', '1') == '1')>Active</option>
                            <option value="0" @selected(old('is_active') == '0')>Inactive</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Product Variations</h5>
                    <small class="text-muted">Each variation has its own SKU and pricing.</small>
                </div>
                <button type="button" class="btn btn-outline-success btn-sm" id="addVariation">
                    + Add Variation
                </button>
            </div>
            <div class="card-body">
                <div id="variationsContainer"></div>
                <div class="text-muted small mt-2">
                    Enter a unique SKU for every variation. Prices can be zero if not yet configured.
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('business.products.index', ['business' => $business->slug]) }}"
               class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn btn-success px-4">Save Product</button>
        </div>
    </form>
</div>

<style>
    .card { border-radius: 12px; }
    .card-header { border-bottom: 1px solid #edf0ed; }
    .form-control, .form-select { min-height: 43px; }
    .variation-item {
        border: 1px solid #e5eae5;
        border-radius: 10px;
        padding: 18px;
        margin-bottom: 15px;
        background: #fcfdfc;
    }
    .variation-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('variationsContainer');
    const typeSelect = document.getElementById('productType');
    const addButton = document.getElementById('addVariation');
    const imageInput = document.getElementById('productImage');
    let variationIndex = 0;

    function variationTemplate(index) {
        return `
            <div class="variation-item" data-variation="${index}">
                <div class="variation-heading">
                    <strong>Variation ${index + 1}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-variation">Remove</button>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Variation Name <span class="text-danger">*</span></label>
                        <input name="variations[${index}][name]" class="form-control"
                               value="${typeSelect.value === 'simple' ? 'Default' : ''}"
                               maxlength="255" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SKU <span class="text-danger">*</span></label>
                        <input name="variations[${index}][sku]" class="form-control"
                               maxlength="255" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cost Price <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" min="0"
                               name="variations[${index}][cost_price]"
                               class="form-control" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Selling Price <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" min="0"
                               name="variations[${index}][selling_price]"
                               class="form-control" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Opening Quantity <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" min="0"
                               name="variations[${index}][opening_quantity]"
                               class="form-control" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Minimum Stock <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" min="0"
                               name="variations[${index}][minimum_stock]"
                               class="form-control" value="0" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tax Status</label>
                        <select name="variations[${index}][tax_status]" class="form-select">
                            <option value="taxable">Taxable</option>
                            <option value="exempt">Exempt</option>
                            <option value="zero_rated">Zero Rated</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tax Rate (%)</label>
                        <input type="number" step="0.0001" min="0" max="100"
                               name="variations[${index}][tax_rate]"
                               class="form-control" value="0">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input type="hidden" name="variations[${index}][tax_inclusive]" value="0">
                            <input type="checkbox" class="form-check-input"
                                   name="variations[${index}][tax_inclusive]" value="1"
                                   id="taxInclusive${index}">
                            <label class="form-check-label" for="taxInclusive${index}">
                                Price includes tax
                            </label>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    function addVariation() {
        container.insertAdjacentHTML('beforeend', variationTemplate(variationIndex++));
        updateVariationControls();
    }

    function updateVariationControls() {
        const items = container.querySelectorAll('.variation-item');
        addButton.style.display = typeSelect.value === 'variable' ? '' : 'none';
        items.forEach((item, index) => {
            item.querySelector('.variation-heading strong').textContent = `Variation ${index + 1}`;
            const remove = item.querySelector('.remove-variation');
            remove.style.display = typeSelect.value === 'variable' && items.length > 1 ? '' : 'none';
        });
    }

    addButton.addEventListener('click', addVariation);

    container.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-variation')) {
            event.target.closest('.variation-item').remove();
            updateVariationControls();
        }
    });

    typeSelect.addEventListener('change', function () {
        if (typeSelect.value === 'simple') {
            container.innerHTML = '';
            variationIndex = 0;
            addVariation();
        } else if (container.querySelectorAll('.variation-item').length === 0) {
            addVariation();
        }
        updateVariationControls();
    });

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert('The product image must not exceed 2MB.');
            this.value = '';
            document.getElementById('imagePreviewWrap').style.display = 'none';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            document.getElementById('imagePreview').src = event.target.result;
            document.getElementById('imagePreviewWrap').style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    addVariation();
    updateVariationControls();
});
</script>
@endsection
