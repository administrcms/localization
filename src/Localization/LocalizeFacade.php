<?php

namespace Administr\Localization;


use Illuminate\Support\Facades\Facade;

class LocalizeFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'administr.localizator'; }
}