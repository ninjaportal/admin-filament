<?php

namespace NinjaPortal\Admin\Concerns;

use NinjaPortal\Portal\Services\BaseService;
use NinjaPortal\Portal\Services\IService;

trait HasNinjaService
{
    abstract public static function service(): IService;
}
