<?php

namespace App\Services;

use App\Models\Address;

class AddressService
{
    public function createOrUpdateAddress($userId, array $data, $isDefault = false)
    {
        $address = Address::create([
            'user_id' => $userId,
            'address_line1' => $data['address'],
            'address_line2' => $data['address_line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['region'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => 'Algeria',
            'is_default' => $isDefault,
        ]);
        return $address;
    }
}