<?php

namespace Facades\Statamic\Preferences;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Statamic\Preferences\CorePreferences
 */
class CorePreferences extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'Statamic\Preferences\CorePreferences';
    }
}
