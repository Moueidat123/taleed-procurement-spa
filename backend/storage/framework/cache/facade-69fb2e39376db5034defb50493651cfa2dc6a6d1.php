<?php

namespace Facades\Statamic\Console\Processes;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Console\Processes\Composer
 */
class Composer extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Console\Processes\Composer';
    }
}
