<?php

namespace NinjaPortal\Admin\Models;

use Filament\Models\Contracts\FilamentUser;
use NinjaPortal\FilamentShield\Traits\HasPanelShield;
use NinjaPortal\Portal\Models\Admin as BaseAdmin;

class Admin extends BaseAdmin implements FilamentUser
{
    use HasPanelShield;
}
