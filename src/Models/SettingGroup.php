<?php

namespace NinjaPortal\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SettingGroup extends Model
{
    protected $fillable = ['name'];

    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class);
    }

}
