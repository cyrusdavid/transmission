<?php namespace Vohof;

use Illuminate\Support\Facades\Facade;

class TransmissionFacade extends Facade {

    protected static function getFacadeAccessor() { return 'transmission'; }
}
