<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['setting_key', 'setting_value'];

    // Optional: if your table name is not the plural 'settings', define it
    protected $table = 'settings';
}