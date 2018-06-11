<?php

namespace Noking50\Sitemap;

use Illuminate\Support\Facades\Facade;

/**
 * @see Noking50\Sitemap\Sitemap
 */
class SitemapFacade extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'sitemap';
    }

}
