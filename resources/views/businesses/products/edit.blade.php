@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Product</h2>
            <p class="text-muted mb-0">Update product information and variation pricing.</p>
        </div>
        <a href="{{ route('business.products.show', ['business' => $business->slug, 'product' => $product->id]) }}"
           class="btn btn-outline-secondary">Back to Product</a>
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
          action="{{ route('business.products.update', ['business' => $business->slug, 'product' => $product->id]) }}"
          enctype="multipart/form-data"
          id="productForm">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" class="form-control"
                               value="{{ old('name', $product->name) }}" required maxlength="255">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select">
                            <option value="">Select category</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected(old('category_id', $product->category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" class="form-control"
                               value="{{ old('brand', $product->brand) }}" maxlength="255">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Product Type</label>
                        <input type="text" class="form-control"
                               value="{{ ucfirst($product->product_type) }} Product" disabled>
                        <input type="hidden" name="product_type" value="{{ $product->product_type }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Product Image</label>
                        <input type="file" name="image" id="productImage"
                               class="form-control" accept=".jpg,.jpeg,.png,.webp">
                        <small class="text-muted">JPG, JPEG, PNG or WEBP. Maximum 2MB.</small>

                        @if($product->image)
                            <div class="mt-3">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($product->image) }}"
                                     alt="{{ $product->name }}"
                                     style="width:110px;height:110px;object-fit:cover;border-radius:10px;">
                            </div>
                        @endif

                        <div class="mt-3" id="imagePreviewWrap" style="display:none;">
                            <img id="imagePreview" src="" alt="New product preview"
                                 style="width:110px;height:110px;object-fit:cover;border-radius:10px;">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status *</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" @selected(old('is_active', (string) (int) $product->is_active) == '1')>Active</option>
                            <option value="0" @selected(old('is_active', (string) (int) $product->is_active) == '0')>Inactive</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Product Variations</h5>
                    <small class="text-muted">Update names, SKUs, prices and tax settings.</small>
                </div>
                @if($product->product_type === 'variable')
                    <button type="button" class="btn btn-outline-success btn-sm" id="addVariation">
                        + Add Variation
                    </button>
                @endif
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    Stock quantities are not editable here. Use Stock Management to adjust inventory.
                    Existing variations cannot be removed here, so their stock and movement history remain protected.
                </div>

                <div id="variationsContainer">
                    @foreach($product->variations as $index => $variation)
                        <div class="variation-item" data-existing="1">
                            <div class="variation-heading">
                                <strong>Variation {{ $index + 1 }}</strong>
                                <span class="badge bg-secondary">Existing variation</span>
                            </div>

                            <input type="hidden" name="variations[{{ $index }}][id]"
                                   value="{{ $variation->id }}">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Variation Name *</label>
                                    <input name="variations[{{ $index }}][name]"
                                           class="form-control"
                                           value="{{ old("variations.$index.name", $variation->name) }}"
                                           maxlength="255" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">SKU *</label>
                                    <input name="variations[{{ $index }}][sku]"
                                           class="form-control"
                                           value="{{ old("variations.$index.sku", $variation->sku) }}"
                                           maxlength="255" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Cost Price *</label>
                                    <input type="number" step="0.0001" min="0"
                                           name="variations[{{ $index }}][cost_price]"
                                           class="form-control"
                                           value="{{ old("variations.$index.cost_price", $variation->cost_price) }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Selling Price *</label>
                                    <input type="number" step="0.0001" min="0"
                                           name="variations[{{ $index }}][selling_price]"
                                           class="form-control"
                                           value="{{ old("variations.$index.selling_price", $variation->selling_price) }}" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Tax Status</label>
                                    <select name="variations[{{ $index }}][tax_status]" class="form-select">
                                        <option value="taxable" @selected(old("variations.$index.tax_status", $variation->tax_status) === 'taxable')>Taxable</option>
                                        <option value="exempt" @selected(old("variations.$index.tax_status", $variation->tax_status) === 'exempt')>Exempt</option>
                                        <option value="zero_rated" @selected(old("variations.$index.tax_status", $variation->tax_status) === 'zero_rated')>Zero Rated</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Tax Rate (%)</label>
                                    <input type="number" step="0.0001" min="0" max="100"
                                           name="variations[{{ $index }}][tax_rate]"
                                           class="form-control"
                                           value="{{ old("variations.$index.tax_rate", $variation->tax_rate) }}">
                                </div>

                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input type="hidden" name="variations[{{ $index }}][tax_inclusive]" value="0">
                                        <input type="checkbox" class="form-check-input"
                                               name="variations[{{ $index }}][tax_inclusive]" value="1"
                                               id="taxInclusive{{ $index }}"
                                               @checked(old("variations.$index.tax_inclusive", $variation->tax_inclusive))>
                                        <label class="form-check-label" for="taxInclusive{{ $index }}">
                                            Price includes tax
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-muted small mt-2">
                    New variations will be created with zero stock at each active branch.
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('business.products.show', ['business' => $business->slug, 'product' => $product->id]) }}"
               class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn btn-success px-4">Save Changes</button>
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
    const addButton = document.getElementById('addVariation');
    const imageInput = document.getElementById('productImage');
    let variationIndex = {{ $product->variations->count() }};

    function variationTemplate(index) {
        return `
            <div class="variation-item" data-new="1">
                <div class="variation-heading">
                    <strong>New Variation</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-variation">Remove</button>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Variation Name *</label>
                        <input name="variations[${index}][name]" class="form-control"
                               maxlength="255" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SKU *</label>
                        <input name="variations[${index}][sku]" class="form-control"
                               maxlength="255" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cost Price *</label>
                        <input type="number" step="0.0001" min="0"
                               name="variations[${index}][cost_price]"
                               class="form-control" value="0" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Selling Price *</label>
                        <input type="number" step="0.0001" min="0"
                               name="variations[${index}][selling_price]"
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

    if (addButton) {
        addButton.addEventListener('click', function () {
            container.insertAdjacentHTML('beforeend', variationTemplate(variationIndex++));
        });
    }

    container.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-variation')) {
            event.target.closest('[data-new="1"]').remove();
        }
    });

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        if (file.size > 2 * 1024 * 1024) {
            alert('The product image must not exceed 2MB.');
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            document.getElementById('imagePreview').src = event.target.result;
            document.getElementById('imagePreviewWrap').style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endsection
