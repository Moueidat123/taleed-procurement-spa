<?php

namespace Facades\Statamic;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Version
 */
class Version extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Version';
    }
}
