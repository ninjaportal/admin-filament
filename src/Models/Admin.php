<?php

namespace NinjaPortal\Admin\Models;

use Althinect\FilamentSpatieRolesPermissions\Concerns\HasSuperAdmin;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasSuperAdmin, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
