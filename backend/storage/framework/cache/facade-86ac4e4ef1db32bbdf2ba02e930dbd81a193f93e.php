<?php

namespace Facades\Statamic\Marketplace;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Marketplace\Marketplace
 */
class Marketplace extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Marketplace\Marketplace';
    }
}
