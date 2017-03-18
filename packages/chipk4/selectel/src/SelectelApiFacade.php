<?php namespace Chipk4\Selectel;

use Illuminate\Support\Facades\Facade;

class SelectelApiFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'selectel-api';
    }
}