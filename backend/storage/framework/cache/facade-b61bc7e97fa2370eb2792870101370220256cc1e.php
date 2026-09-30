<?php

namespace Facades\Statamic\CP\Utilities;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\CP\Utilities\CoreUtilities
 */
class CoreUtilities extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\CP\Utilities\CoreUtilities';
    }
}
