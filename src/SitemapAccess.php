<?php

namespace Noking50\Sitemap;

/**
 * 網站地圖 存取權限分級
 */
class SitemapAccess {

    /**
     * 繼承上層分級
     */
    const INHERIT = -1;
    
    /**
     * 不需登入
     */
    const LOGIN_NOT_REQUIRED = 0;
    
    /**
     * 需要登入
     */
    const LOGIN_REQUIRED = 1;
    
    /**
     * 需要登入且有權限
     */
    const ACCESS_REQUIRED = 2;
    
    /**
     * 需要登入且有超級管理員權限
     */
    const SUPER_REQUIRED = 3;

}
