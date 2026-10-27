<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\Classes\Module;

class ModuleFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Module::class;
    }
}
