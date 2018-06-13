<?php

namespace Noking50\Sitemap;

use Noking50\Sitemap\Facades\Sitemap;
use Noking50\Sitemap\SitemapAccess;

/**
 * 網站地圖權限
 * 
 * @package App\Classes\Sitemap
 */
trait SitemapPermission {

    /**
     * 取得所有有權限的 網址dot路徑
     * 
     * @param string|\App\Classes\Sitemap\SitemapNode $root_node 根節點的SitemapNode or dot路徑
     * @param type $max_level
     * @return array
     */
    public function getPermissionAll($root_node, $max_level = SitemapAccess::ACCESS_REQUIRED) {
        if (is_string($root_node)) {
            $root_node = Sitemap::node($root_node);
        }
        $data = $this->buildPermissionAll($root_node, $max_level);

        return $data;
    }

    /**
     * 取得所有有權限的 tree array
     * 
     * @param string|\App\Classes\Sitemap\SitemapNode $root_node 根節點的SitemapNode or dot路徑
     * @return array
     */
    public function getPermissionTree($root_node) {
        if (is_string($root_node)) {
            $root_node = Sitemap::node($root_node);
        }
        $data = $this->buildPermissionTree($root_node, true);

        return $data;
    }

    /**
     * 取得root開始幾層內的節點網址dot路徑, 用來設定當有新增子權限可否自動啟用
     * 
     * @param string|\App\Classes\Sitemap\SitemapNode $root_node 根節點的SitemapNode or dot路徑
     * @param integer $deep 深度幾層
     * @return array
     */
    public function getAutoFunctionAll($root_node, $deep = 3) {
        if (is_string($root_node)) {
            $root_node = Sitemap::node($root_node);
        }
        $data = [];
        if ($root_node->prop('permission') <= SitemapAccess::ACCESS_REQUIRED) {
            $data = $this->buildAutoFunctionAll($root_node, $deep);
        }

        return $data;
    }

    /**
     * 取得root開始幾層內的節點的 tree array, 用來設定當有新增子權限可否自動啟用
     * 
     * @param string|\App\Classes\Sitemap\SitemapNode $root_node 根節點的SitemapNode or dot路徑
     * @param integer $deep 深度幾層
     * @return array
     */
    public function getAutoFunctionTree($root_node, $deep = 3) {
        if (is_string($root_node)) {
            $root_node = Sitemap::node($root_node);
        }
        $data = [];
        if ($root_node->prop('permission') <= SitemapAccess::ACCESS_REQUIRED) {
            $data = $this->buildAutoFunctionTree($root_node, $deep);
        }

        return $data;
    }

    ##

    /**
     * 建立所有有權限的 網址dot路徑
     * 
     * @param \App\Classes\Sitemap\SitemapNode $node 目前節點
     * @param integer $max_level SitemapAccess分級, 篩選包含 最高分級以下的權限
     * @return array
     */
    private function buildPermissionAll($node, $max_level) {
        $permissions = [];
        if ($node->prop('permission') >= SitemapAccess::ACCESS_REQUIRED && $node->prop('permission') <= $max_level) {
            $permissions[] = $node->getPath();
        }

        $node_all_children = $node->getChildren();
        foreach ($node_all_children as $k => $v) {
            $children_perm = $this->buildPermissionAll($v, $max_level);
            $permissions = array_merge($permissions, $children_perm);
        }

        return $permissions;
    }

    /**
     * 建立所有有權限的 tree array
     * 
     * @param \App\Classes\Sitemap\SitemapNode $node 目前節點
     * @param boolean $is_head 是否為遞迴的開頭
     * @return array
     */
    private function buildPermissionTree($node, $is_head) {
        $permissions = [];
        if ($node->prop('permission') == SitemapAccess::ACCESS_REQUIRED) {
            $permissions[] = $node->getPath();
        }

        $node_all_children = $node->getChildren();
        $children = [];
        foreach ($node_all_children as $k => $v) {
            $node_child = $this->buildPermissionTree($v, false);
            if (!is_null($node_child)) {
                $children[] = $node_child;
            }
        }
        if (empty($permissions) && empty($children)) {
            return null;
        }

        if (!$is_head && !is_null($node->prop('route.method')) && count($children) > 0 && $node->prop('permission') != SitemapAccess::INHERIT) {
            array_unshift($children, [
                'id' => 'jstreeNodePerm_' . str_replace('.', '_', $node->getPath()) . '_index',
                'text' => trans($node->getTrans('index')),
                'path' => $node->getPath(),
                'permission' => $permissions,
                'children' => [],
            ]);
            $data = [
                'id' => 'jstreeNodePerm_' . str_replace('.', '_', $node->getPath()),
                'text' => $node->getName(),
                'path' => $node->getPath(),
                'permission' => [],
                'children' => $children,
            ];
            return $data;
        } else {
            $data = [
                'id' => 'jstreeNodePerm_' . str_replace('.', '_', $node->getPath()),
                'text' => $node->getName(),
                'path' => $node->getPath(),
                'permission' => $permissions,
                'children' => $children,
            ];
            return $data;
        }
    }

    /**
     * 建立root開始幾層內的節點網址dot路徑, 用來設定當有新增子權限可否自動啟用
     * 
     * @param \App\Classes\Sitemap\SitemapNode $node 目前節點
     * @param integer $deep 深度幾層
     * @return array
     */
    private function buildAutoFunctionAll($node, $deep) {
        $deep--;
        $permissions = [];

        if ($deep > 0) {
            $node_all_children = $node->getChildren(null, function($node) {
                return ($node->prop('menu', false) == true && $node->prop('permission') <= SitemapAccess::ACCESS_REQUIRED);
            });
            foreach ($node_all_children as $k => $v) {
                $children_perm = $this->buildAutoFunctionAll($v, $deep);
                $permissions = array_merge($permissions, $children_perm);
            }
        } else {
            $permissions[] = $node->getPath();
        }

        return $permissions;
    }

    /**
     * 建立root開始幾層內的節點的 tree array, 用來設定當有新增子權限可否自動啟用
     * 
     * @param \App\Classes\Sitemap\SitemapNode $node 目前節點
     * @param integer $deep 深度幾層
     * @return array
     */
    private function buildAutoFunctionTree($node, $deep) {
        $deep--;
        $permissions = null;
        $children = [];

        if ($deep > 0) {
            $node_all_children = $node->getChildren(null, function($node) {
                return ($node->prop('menu', false) == true && $node->prop('permission') <= SitemapAccess::ACCESS_REQUIRED);
            });
            foreach ($node_all_children as $k => $v) {
                $children[] = $this->buildAutoFunctionTree($v, $deep);
            }
        } else {
            $permissions = $node->getPath();
        }

        $data = [
            'id' => 'jstreeNodeAtFunc_' . str_replace('.', '_', $node->getPath()),
            'text' => $node->getName(),
            'path' => $node->getPath(),
            'permission' => $permissions,
            'children' => $children
        ];
        return $data;
    }

}
