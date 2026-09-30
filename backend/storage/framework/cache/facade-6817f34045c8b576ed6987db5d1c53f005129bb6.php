<?php

namespace Facades\Statamic\Stache;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Stache\Traverser
 */
class Traverser extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Stache\Traverser';
    }
}
