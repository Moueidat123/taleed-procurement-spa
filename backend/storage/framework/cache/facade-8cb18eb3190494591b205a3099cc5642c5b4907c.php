<?php

namespace Facades\Statamic\Marketplace;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Marketplace\Client
 */
class Client extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Marketplace\Client';
    }
}
