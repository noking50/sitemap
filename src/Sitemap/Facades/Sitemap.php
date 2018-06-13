<?php

namespace Noking50\Sitemap\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see Noking50\Sitemap\Sitemap
 */
class Sitemap extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'sitemap';
    }

}
