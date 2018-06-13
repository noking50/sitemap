<?php

namespace Noking50\Sitemap;

use Noking50\Sitemap\SitemapNode;
use Noking50\Sitemap\SitemapPermission;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

/**
 * 網站地圖 配合Route使用
 * 
 */
class Sitemap {

    use SitemapPermission;

    /**
     * 目前網址的節點
     * 
     * @var \App\Classes\Sitemap\SitemapNode
     * @access private
     */
    private $currentNode = null;

    /**
     * 取得網站地圖節點
     * 
     * @param string|null $path 網址dot路徑，null 取得目前網址的節點
     * @return \App\Classes\Sitemap\SitemapNode
     * @access public
     */
    public function node($path = null) {
        if (is_null($path)) {
            return $this->getCurrentNode();
        } else {
            return new SitemapNode($path);
        }
    }

    /**
     * 取得目前網址節點
     * 
     * @return \App\Classes\Sitemap\SitemapNode
     * @access public
     */
    public function getCurrentNode() {
        if (is_null($this->currentNode)) {
            $this->currentNode = new SitemapNode(Route::currentRouteName());
        }

        return $this->currentNode;
    }

    /**
     * 產生路由給Laravel 的 Route
     * 
     * @param string|null $rootPath 起始(根)節點的網址dot路徑
     * @return void
     * @access public
     */
    public function route($root_path) {
        $node = $this->node($root_path);
        $this->routeGroup($node);
    }

    /**
     * 取得多語言文字 dot路徑
     * 
     * @param string $path 網址dot路徑
     * @param string $trail 多語言尾端路徑 $path . "._$trail"
     * @return string
     * @access public
     */
    public function getTransPath($path, $trail) {
        $locale_path_arr = array_filter([$path, '_' . $trail]);
        $locale_path = implode('.', $locale_path_arr);

        return 'sitemap.' . $locale_path;
    }

    /**
     * 取得多語言文字
     * 
     * @param string $path 網址dot路徑
     * @param string $trail 多語言尾端路徑 $path . "._$trail"
     * @param string $locale 指定語言
     * @return string
     * @access public
     */
    public function getTrans($path, $trail, $locale = null) {
        $path_trans = $this->getTransPath($path, $trail);

        return trans($path_trans, [], $locale);
    }

    /**
     * 取得節點名稱
     * 
     * @param string $path 網址dot路徑
     * @param string $locale 指定語言
     * @return string
     * @access public
     */
    public function getName($path, $locale = null) {
        return $this->getTrans($path, 'name', $locale);
    }

    /**
     * 取得節點標題
     * 
     * @param string $path 網址dot路徑
     * @param string $locale 指定語言
     * @return string
     * @access public
     */
    public function getTitle($path, $locale = null) {
        $title = $this->getTrans($path, 'title', $locale);
        if ($title == $this->getTransPath($path, 'title')) {
            $title = $this->getName($path, $locale);
        }

        return $title;
    }

    /**
     * 取的節點網址
     * 
     * @param string $path dot路徑
     * @param array $param 路由網址參數
     * @param boolean $absolute 是否為絕對路徑網址
     * @return string
     * @access public
     */
    public function getUrl($path, $param = [], $absolute = true) {
        $route = Route::getRoutes()->getByName($path);
        if (is_null($route)) {
            return null;
        }

        // param optional
        if (isset($param['optional']) && is_array($param['optional'])) {
            $param['optional'] = $this->formatOptionalParam($param['optional']);
        } else {
            unset($param['optional']);
        }

        $default_param = array_intersect_key(Route::current()->parameters(), array_flip($route->parameterNames()));
        unset($default_param['optional']);
        $route_param = array_merge($default_param, $param);

        return route($path, $route_param, $absolute);
    }

    /**
     * 格式化多語言編碼
     * 
     * @param string $lang 多語言代碼
     * @return string
     * @access public
     */
    public function formatLangCode($lang) {
        return str_replace('_', '-', strtolower($lang));
    }

    /**
     * Optional 參數陣列 轉成 網址字串
     * 
     * @param array $optional Optional 參數陣列
     * @return string Optional 網址字串
     * @access public
     */
    public function formatOptionalParam($optional) {
        $tmp_optional = [];
        foreach ($optional as $k => $v) {
            if (is_null($v)) {
                continue;
            }
            $tmp_optional[] = $k . '-' . $v;
        }
        if (count($tmp_optional) <= 0) {
            return '';
        }
        return implode('/', $tmp_optional);
    }

    /**
     * 取的目前節點的多語言網址
     * 
     * @param string $lang 指定語言
     * @return string
     * @access public
     */
    public function getCurrentUrlChangeLang($lang) {
        $lang = $this->formatLangCode($lang);
        if (!in_array($lang, array_keys(config('language', [])))) {
            $lang = config('app.locale');
        }

        $prefix = Route::current()->getPrefix();
        $pathinfo = Request::getPathInfo();
        $qs = Request::getQueryString();
        Request::path();
        if ($prefix == '') {
            $pathinfo = '/' . $lang . $pathinfo;
        } else {
            $pathinfo = preg_replace('/' . $prefix . '/', $lang, $pathinfo, 1);
        }
        $url = Request::getBasePath() . $pathinfo . ($qs ? '?' . $qs : '');

        return $url;
    }

    ##

    /**
     * 產生路由 group
     * 
     * @param \App\Classes\Sitemap\SitemapNode $node 處理節點
     * @return void
     * @access private
     */
    private function routeGroup(SitemapNode $node) {
        $group = $node->prop('route.group');
        if (!is_null($group)) {
            Route::group($group, function() use($node) {
                $this->routeRoute($node);
            });
        } else {
            $this->routeRoute($node);
        }
    }

    /**
     * 產生路由 route
     * 
     * @param \App\Classes\Sitemap\SitemapNode $node 處理節點
     * @return void
     * @access private
     */
    private function routeRoute(SitemapNode $node) {
        if (!is_null($node->prop('route.method'))) {
            $match = $node->prop('route.method', 'get');

            $path = $node->getPath();
            $url = trim(str_replace('.', '/', $path), '/') . '/';
            $main_site = config('sitemap.main', '') . '/';
            if (starts_with($url, $main_site)) {
                $url = substr($url, strlen($main_site));
            }
            $param = $node->prop('route.param', '');
            $url .= trim($param, '/');

            $attr = $node->prop('route.attr', []);
            $attr['as'] = $path;
            Route::match($match, $url, $attr);
        }

        foreach ($node->getChildren() as $k => $v) {
            $this->routeGroup($v);
        }
    }

}
