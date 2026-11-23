<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BusinessProfileController extends Controller
{
    public function show(Request $request): View
    {
        $business = $request->attributes->get('current_business');

        abort_unless($business, 404);

        return view('businesses.profile', [
            'business' => $business,
            'canEdit' => (int) $business->owner_id === (int) $request->user()->id,
        ]);
    }

    public function edit(Request $request): View
    {
        $business = $request->attributes->get('current_business');

        abort_unless($business, 404);
        abort_unless((int) $business->owner_id === (int) $request->user()->id, 403);

        return view('businesses.profile-edit', [
            'business' => $business,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $business = $request->attributes->get('current_business');

        abort_unless($business, 404);
        abort_unless((int) $business->owner_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_type' => [
                'required',
                Rule::in(['retail', 'wholesale', 'restaurant', 'pharmacy', 'supermarket', 'other']),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'currency' => ['required', Rule::in(['GHS', 'USD', 'GBP', 'EUR'])],
            'address' => ['nullable', 'string', 'max:2000'],
            'tax_number' => ['nullable', 'string', 'max:100'],
        ]);

        $business->update($validated);

        return redirect()
            ->route('business.profile.show', ['business' => $business->slug])
            ->with('success', 'Business profile updated successfully.');
    }
}
