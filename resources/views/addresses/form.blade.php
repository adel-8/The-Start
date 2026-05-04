<div class="form-group">
    <label for="address_line1">{{ __('messages.address_line1') }} *</label>
    <input type="text" name="address_line1" id="address_line1" class="form-input" value="{{ old('address_line1', $address->address_line1 ?? '') }}" required>
    @error('address_line1') <span class="error">{{ $message }}</span> @enderror
</div>

<div class="form-group">
    <label for="address_line2">{{ __('messages.address_line2') }}</label>
    <input type="text" name="address_line2" id="address_line2" class="form-input" value="{{ old('address_line2', $address->address_line2 ?? '') }}">
</div>

<div class="form-row">
    <div class="form-group">
        <label for="city">{{ __('messages.city') }} *</label>
        <input type="text" name="city" id="city" class="form-input" value="{{ old('city', $address->city ?? '') }}" required>
    </div>
    <div class="form-group">
        <label for="state">{{ __('messages.state_region') }}</label>
        <input type="text" name="state" id="state" class="form-input" value="{{ old('state', $address->state ?? '') }}">
    </div>
    <div class="form-group">
        <label for="postal_code">{{ __('messages.postal_code') }}</label>
        <input type="text" name="postal_code" id="postal_code" class="form-input" value="{{ old('postal_code', $address->postal_code ?? '') }}">
    </div>
</div>

<div class="form-group">
    <label for="country">{{ __('messages.country') }} *</label>
    <input type="text" name="country" id="country" class="form-input" value="{{ old('country', $address->country ?? 'Algeria') }}" required>
</div>

<div class="form-group checkbox-group">
    <label>
        <input type="checkbox" name="is_default" value="1" {{ old('is_default', isset($address) && $address->is_default ? 'checked' : '') }}>
        {{ __('messages.set_as_default_address') }}
    </label>
</div>