<?php

namespace Noking50\Sitemap;

use Noking50\Sitemap\Sitemap;
use Noking50\Sitemap\SitemapAccess;

/**
 * 網站地圖節點
 * 
 */
class SitemapNode {

    /**
     * 節點 dot路徑
     * 
     * @var string
     */
    private $path = null;

    /**
     * 子節點
     * 
     * @var array
     */
    private $children = null;

    /**
     * 長輩節點
     * 
     * @var array
     */
    private $parents = null;

    /**
     * Construct
     * 
     * @param string $node_path 節點dot路徑
     */
    public function __construct($node_path = null) {
        $this->path = $node_path;
    }

    /**
     * 取得節點的屬性
     * 
     * @param string $property 要取得的屬性
     * @param mixed $default 預設值
     * @return mix
     */
    public function prop($property = null, $default = null) {
        if (is_null($this->path)) {
            return $default;
        }
        return config('sitemap.node.' . $this->path . '._prop.' . $property, $default);
    }

    /**
     * 節點是否為空值
     * 
     * @return boolean
     */
    public function isEmpty() {
        return is_null($this->path) || is_null(config('sitemap.node.' . $this->path . '._prop'));
    }

    /**
     * 取得子節點
     * 
     * @param string $key 指定路徑區段, null 為取得全部
     * @param \Closure $filter 自訂篩選條件的 Closure
     * @return array|Noking50\Sitemap\SitemapNode
     */
    public function getChildren($key = null, $filter = null) {
        if (is_null($this->children)) {
            $this->loadChildren();
        }

        if (is_null($key)) {
            if (is_callable($filter)) {
                return array_filter($this->children, $filter);
            } else {
                return $this->children;
            }
        } else if (isset($this->children[$key])) {
            if (!is_callable($filter) || $filter($this->children[$key]) === true) {
                return $this->children[$key];
            } else {
                return new static();
            }
        } else {
            return new static();
        }
    }

    /**
     * 取得長輩節點
     * 
     * @param integer $level 指定第幾層的節點 base0, null 為取得全部
     * @param \Closure $filter 自訂篩選條件的 Closure
     * @return array|Noking50\Sitemap\SitemapNode
     */
    public function getParents($level = null, $filter = null) {
        if (is_null($this->parents)) {
            $this->loadParents();
        }

        if (is_null($level)) {
            if (is_callable($filter)) {
                return array_filter($this->parents, $filter);
            } else {
                return $this->parents;
            }
        } else if (isset($this->parents[$level])) {
            if (!is_callable($filter) || $filter($this->parents[$level]) === true) {
                return $this->parents[$level];
            } else {
                return new static();
            }
        } else {
            return new static();
        }
    }

    /**
     * 取得父節點
     * 
     * @return Noking50\Sitemap\SitemapNode
     */
    public function getParent() {
        if (is_null($this->parents)) {
            $this->loadParents();
        }

        if (count($this->parents) > 0) {
            return last($this->parents);
        } else {
            return new static();
        }
    }

    /**
     * 取得有設定權限的節點
     * 
     * @return Noking50\Sitemap\SitemapNode
     */
    public function getPermissionNode() {
        if ($this->prop('permission') == SitemapAccess::INHERIT) {
            $tmp_node = last($this->getParents(null, function($node) {
                        return $node->prop('permission') != SitemapAccess::INHERIT;
                    }));
            if ($tmp_node !== false && !$tmp_node->isEmpty()) {
                return $tmp_node;
            }
        }
        return $this;
    }

    /**
     * 取得節點的權限值
     * 
     * @return string
     */
    public function getPermissionKey() {
        $permission_node = $this->getPermissionNode();
        $permission_key = null;
        if ($permission_node->prop('permission', SitemapAccess::LOGIN_NOT_REQUIRED) >= SitemapAccess::ACCESS_REQUIRED) {
            $permission_action = $this->prop('permission_action', '');
            $permission_key = $permission_node->getPath() . ($permission_action == '' ? '' : '@' . $permission_action);
        }

        return $permission_key;
    }

    /**
     * 取得包含自己 路徑上的節點
     * 
     * @param integer $level_start 子路徑的索引開始位置 base 0
     * @param integer|null $level_end 子路徑的索引結束位置 
     * @return array
     */
    public function getPathNodes($level_start = 0, $level_end = null) {
        $path_nodes = $this->getParents();
        $path_nodes[] = $this;

        return array_slice($path_nodes, $level_start, $level_end, true);
    }

    /**
     * 取得根路徑節點
     * 
     * @return Noking50\Sitemap\SitemapNode
     */
    public function getRoot() {
        $root = head($this->getPathNodes());
        if ($root === false) {
            $root = new static();
        }

        return $root;
    }

    /**
     * 取的 dot路徑
     * 
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * 取得節點的 dot路徑區段
     * 
     * @param integer|null $segment 區段位置 1-base null為最後區段
     * @return string
     */
    public function getKey($segment = null) {
        $path_seg = explode('.', $this->path);
        $key = '';
        if (is_null($segment)) {
            if (count($path_seg) > 0) {
                $key = last($path_seg);
            }
        } else {
            $index = $segment - 1;
            if (isset($path_seg[$index])) {
                $key = $path_seg[$index];
            }
        }

        return $key;
    }

    /**
     * 取得多語言文字
     * 
     * @param string $trail 多語言尾端路徑 $path . "._$trail"
     * @param string $locale 指定語言
     * @return string
     */
    public function getTrans($trail, $locale = null) {
        return Sitemap::getTrans($this->path, $trail, $locale);
    }

    /**
     * 取得節點名稱
     * 
     * @param string $locale 指定語言
     * @return string
     */
    public function getName($locale = null) {
        return Sitemap::getName($this->path, $locale);
    }

    /**
     * 取得節點標題
     * 
     * @param string $locale 指定語言
     * @return string
     */
    public function getTitle($locale = null) {
        return Sitemap::getTitle($this->path, $locale);
    }

    /**
     * 取的節點網址
     * 
     * @param array $param 路由網址參數
     * @param boolean $absolute 是否為絕對路徑網址
     * @return string
     */
    public function getUrl($param = [], $absolute = true) {
        return Sitemap::getUrl($this->path, $param, $absolute);
    }

    ##

    /**
     * 尋找子節點
     * 
     * @return void
     */
    private function loadChildren() {
        $item_keys = array_keys(config('sitemap.node.' . $this->path, []));
        if (($key = array_search('_prop', $item_keys)) !== false) {
            unset($item_keys[$key]);
        }

        $children = [];
        foreach ($item_keys as $k => $v) {
            $children[$v] = new static($this->path . '.' . $v);
        }
        $this->children = $children;
    }

    /**
     * 尋找長輩節點
     * 
     * @return void
     */
    private function loadParents() {
        $seg_path = array_filter(explode('.', $this->path));
        $seg_path = array_slice($seg_path, 0, -1);

        $parents = [];
        $key = '';
        foreach ($seg_path as $k => $v) {
            $key = trim($key . '.' . $v, '.');
            $parents[] = new static($key);
        }
        $this->parents = $parents;
    }

}
