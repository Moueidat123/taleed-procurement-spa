<?php

namespace Facades\Statamic\Fields;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Fields\FieldtypeRepository
 */
class FieldtypeRepository extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Fields\FieldtypeRepository';
    }
}
