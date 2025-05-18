<?php

namespace NinjaPortal\Admin\Concerns;

use NinjaPortal\Portal\Services\BaseServiceInterface;
use NinjaPortal\Portal\Contracts\Services\ServiceInterface;

trait HasNinjaService
{
    abstract public static function service(): ServiceInterface;
}
