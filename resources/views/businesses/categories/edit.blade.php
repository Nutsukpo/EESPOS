@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="container-fluid" style="max-width:950px;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Edit Product Category</h4>
            <p class="text-muted small mb-0">Update category information and settings.</p>
        </div>
        <a href="{{ route('business.categories.index', ['business' => $business->slug]) }}" class="btn btn-light btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to Categories
        </a>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius:15px;">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('business.categories.update', ['business' => $business->slug, 'category' => $category->id]) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Category Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Parent Category</label>
                    <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
                        <option value="">None — Main Category</option>
                        @foreach($parentCategories as $parent)
                            <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Choose a parent category or leave blank to make this a main category.</div>
                    @error('parent_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold small">Description</label>
                    <textarea name="description" rows="4" maxlength="5000" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" required>
                        <option value="1" @selected((string) old('is_active', (int) $category->is_active) === '1')>Active</option>
                        <option value="0" @selected((string) old('is_active', (int) $category->is_active) === '0')>Inactive</option>
                    </select>
                    @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn text-white px-4" style="background:#173b2d;">
                        <i class="fas fa-save me-1"></i> Update Category
                    </button>
                    <a href="{{ route('business.categories.index', ['business' => $business->slug]) }}" class="btn btn-light px-4">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
