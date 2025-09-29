<?php
#BEGIN_LICENSE
#
# Module: SmartyExt (c) 2020-2025 by CMS Made Simple Foundation Inc.
# An add-on module for CMS Made Simple to provide useful functions
#  and commonly used gui capabilities to other modules.
# A fork of module: CGSimpleSmarty (c) 2008-2014 by Robert Campbell
#
# CMSMS - CMS Made Simple is (c) 2006 - 2021 by CMS Made Simple Foundation
# CMSMS - CMS Made Simple is (c) 2004 by Ted Kulp (wishy@cmsmadesimple.org)
# Visit the CMSMS Homepage at: https://www.cmsmadesimple.org
#
# This module is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This module is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of that License along with CMSMS
# or if not, read the License online at
# https://www.gnu.org/licenses/old-licenses/gpl-2.0.html.
#
#END_LICENSE

namespace SmartyExt;

use CmsApp;
use cms_config;
use cms_utils;
use function cms_join_path;
use const CMS_DB_PREFIX;

final class smx
{
    /**
     * Get the current URL
     *
     * @return string
     */
    public static function self_url()
    {
        $gCms = CmsApp::get_instance();
        $s = ($gCms->is_https_request()) ? 's' : '';
        $p = strpos($_SERVER['SERVER_PROTOCOL'], '/');
        $protocol = strtolower(substr($_SERVER['SERVER_PROTOCOL'], 0, $p)) . $s;
        $port = ('80' === (string)$_SERVER['SERVER_PORT'] || '443' === (string)$_SERVER['SERVER_PORT']) ? ''
          : (':' . $_SERVER['SERVER_PORT']);
        return $protocol . '://' . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
    }

    /**
     * Test if a module is installed, and active.
     *
     * @param string $module the module name
     *
     * @return bool
     */
    public static function module_installed($module)
    {
        if ($module) {
            return is_object(cms_utils::get_module($module));
        }
        return false;
    }

    /**
     * Get the alias of the parent of the specified page (by alias), if any.
     *
     * @param string $alias The optional alias. If not specified, the current page is assumed.
     *
     * @return string maybe empty
     */
    public static function get_parent_alias($alias = '')
    {
        if ('' === $alias) {
            $alias = cms_utils::get_current_alias();
        }

        $gCms = CmsApp::get_instance();
        $hm = $gCms->GetHierarchyManager();
        $node = $hm->find_by_tag('alias', $alias);
        return ($node) ? (string)$node->getParent()->get_tag('alias') : '';
    }

    /**
     * Test if a page alias or id is a child of another page alias or id.
     *
     * @param string|int $test_parent The parent alias or id to test against
     * @param string|int $test_child  The child alias or id to test against
     *
     * @return bool
     */
    public static function is_child_of($test_parent, $test_child)
    {
        if (!$test_parent) {
            return false;
        }

        if (!$test_child) {
            return false;
        }

        $gCms = CmsApp::get_instance();
        $hm = $gCms->GetHierarchyManager();
        // get the child node
        if ((int)$test_child > 0 && is_numeric($test_child)) {
            $node_child = $hm->find_by_tag('id', $test_child);
        } else {
            $node_child = $hm->find_by_tag('alias', $test_child);
        }

        if (!$node_child) {
            return false;
        }

        // get the parent node, and it's id.
        if ((int)$test_parent > 0 && is_numeric($test_parent)) {
            $node_parent = $hm->find_by_tag('id', $test_parent);
        } else {
            $node_parent = $hm->find_by_tag('alias', $test_parent);
        }

        if (!$node_parent) {
            return false;
        }

        $parent_id = (int)$node_parent->get_tag('id');

        if ($parent_id < 1) {
            return false;
        }

        while ($node_child) {
            if ($node_child->get_tag('id') == $parent_id) {
                return true;
            }

            $node_child = $node_child->get_parent();
        }
        return false;
    }

    /**
     * Get the alias of the root page of the specified page (by alias).
     * That page is the closest ancestor in the pages hierarchy which
     * has child page(s), not necessarily the whole-site-root page (if
     * there is such a page).
     *
     * @param string $alias The desired page alias. If not specified, the current page is assumed.
     *
     * @return string maybe empty
     */
    public static function get_root_alias($alias = '')
    {
        if ('' === $alias) {
            $alias = cms_utils::get_current_alias();
        }

        $stack = null;
        $gCms = CmsApp::get_instance();
        $hm = $gCms->GetHierarchyManager();
        $node = $hm->find_by_tag('alias', $alias);

        while ($node && $node->get_tag('id') > 0) {
            $stack = $node;
            $node = $node->getParent();
        }
        return ($stack) ? $stack->get_tag('alias') : '';
    }

    /**
     * Get the title of the specified page (by alias).
     *
     * @param string $alias Optional alias. If not specified, the current page is assumed.
     *
     * @return string maybe empty
     */
    public static function get_page_title($alias = '')
    {
        if ('' === $alias) {
            $alias = cms_utils::get_current_alias();
        }

        $gCms = CmsApp::get_instance();
        $contentops = $gCms->GetContentOperations();
        $content = $contentops->LoadContentFromAlias($alias);
        return (!is_object($content)) ? (string)$content->Name() : '';
    }

    /**
     * Get the menu text of the specified page (by alias).
     *
     * @param string $alias Optional alias Default ''. If not specified, the current page is assumed.
     *
     * @return string maybe empty
     */
    public static function get_page_menutext($alias = '')
    {
        if ('' === $alias) {
            $alias = cms_utils::get_current_alias();
        }

        $gCms = CmsApp::get_instance();
        $contentops = $gCms->GetContentOperations();
        $content = $contentops->LoadContentFromAlias($alias);
        return (is_object($content)) ? (string)$content->MenuText() : '';
    }

    /**
     * Get the type of the specified page (by alias).
     *
     * @param string $alias Optional alias. Default ''. If not specified, the current page is assumed.
     *
     * @return string maybe empty
     */
    public static function get_page_type($alias = '')
    {
        if ('' == $alias) {
            $alias = cms_utils::get_current_alias();
        }

        $gCms = CmsApp::get_instance();
        $contentops = $gCms->GetContentOperations();
        $content = $contentops->LoadContentFromAlias($alias);
        return (is_object($content)) ? (string)$content->Type() : '';
    }

    /**
     * Report whether the specified page (by alias) has children
     * (other than hidden or inactive children).
     *
     * @param string $alias Optional alias. Default ''. If not specified, the current page is assumed.
     *
     * @return bool
     */
    public static function has_children($alias = '')
    {
        if ('' === $alias) {
            $alias = cms_utils::get_current_alias();
        }

        $gCms = CmsApp::get_instance();
        $hm = $gCms->GetHierarchyManager();
        $node = $hm->find_by_tag('alias', $alias);
        return ($node) ? $node->has_children() : false;
    }

    /**
     * Return an array containing the page ids of all of the specified page's children.
     *
     * @param string $alias   Optional alias. Default ''. If not specified, the current page is assumed.
     * @param bool   $showall Whether to include inactive children. Default false.
     *
     * @return array maybe empty
     */
    public static function get_children($alias = '', $showall = false)
    {
        if ('' === $alias) {
            $alias = cms_utils::get_current_alias();
        }

        if ('' == $alias) {
            return [];
        }

        $gCms = CmsApp::get_instance();
        $hm = $gCms->GetHierarchyManager();
        $parent = $hm->find_by_tag('alias', $alias);

        if (!$parent) {
            return [];
        }

        $child_nodes = $parent->getChildren(false, $showall);

        if (!$child_nodes || !is_array($child_nodes)) {
            return [];
        }

        $results = [];

        foreach ($child_nodes as $node) {
            $content = $node->getContent();

            if (!is_object($content)) {
                continue;
            }
            if (!($showall || $content->Active())) {
                continue;
            }

            $results[] = [
                'id' => $content->Id(),
                'alias' => $content->Alias(),
                'title' => $content->Name(),
                'menutext' => $content->MenuText(),
                'show_in_menu' => $content->ShowInMenu(),
                'active' => $content->Active(),
                'type' => $content->Type()
            ];
        }
        return $results;
    }

    /**
     * Return a module's version
     *
     * @param string $name The module name
     *
     * @return string maybe empty
     */
    public static function module_version($name)
    {
        if ($name) {
            $obj = cms_utils::get_module($name);
            if (is_object($obj)) {
                return $obj->GetVersion();
            }
        }
        return '';
    }

    /**
     * Get a property of the specified page (by alias)
     *
     * @param mixed $alias Optional alias. Default ''. If not specified, the current page is assumed.
     * @param mixed $block The property name. Default ''. If not specified, 'content_en' is assumed.
     *
     * @return string maybe empty
     */
    public static function get_page_content($alias = '', $block = 'content_en')
    {
        $block = trim((string)$block);
        if (!$block) {
            $block = 'content_en';
        }

        $gCms = CmsApp::get_instance();
        if ('' === $alias) {
            $content = $gCms->get_content_object();
        } else {
            $contentops = $gCms->GetContentOperations();
            $content = $contentops->LoadContentFromAlias($alias);
        }
        return (is_object($content)) ? (string)$content->GetPropertyValue($block) : '';
    }

    /**
     * Get the closest sibling (if any) of the specified page (by alias)
     *
     * @param mixed $dir The direction (recognized values are -1, 'prev', 1, 'next'). Default 1.
     * @param mixed $alias Optional alias. Default ''. If not specified, the current page is assumed.
     *
     * @return string maybe empty The alias of the sibling page
     */
    public static function get_sibling($dir = 1, $alias = '')
    {
        if ('' === $alias) {
            $alias = cms_utils::get_current_alias();
        }
        // global_caches content_tree etc are oganised by page id, minimally useful here
        $gCms = CmsApp::get_instance();
/* this tree-nodes logic works if the nodes provided by get_children() are guaranteed to be in hierarchy-order
        $hm = $gCms->GetHierarchyManager();
        $node = $hm->find_by_tag('alias', $alias);
        if (is_object($node)) {
            if ($node->count_siblings() < 2) {
                return '';
            }
            // interrogate the parent's children (i.e. the siblings)
            //TODO confirm child nodes are reported in hierarchy-order
            $peers = $node->get_parent()->get_children();
            $idx = array_search($node, $peers);
            if (is_string($dir)) { $dir = strtolower($dir); }
            switch ($dir) {
                case -1:
                case '-1':
                case 'prev':
                    if (--$idx < 0) {
                        return '';
                    }
                    break;
                default:
                    if (++$idx >= count($peers)) {
                        return '';
                    }
            }
            $node = $peers[$idx];
            return $node->get_tag('alias');
        }
        return '';
*/
        $contentops = $gCms->GetContentOperations();
        $content = $contentops->LoadContentFromAlias($alias);

        if (!is_object($content)) {
            return false;
        }

       if (is_string($dir)) { $dir = strtolower($dir); }
       switch ($dir) {
            case -1:
            case '-1':
            case 'prev':
                $thechar = '<';
                $order = 'DESC';
                break;
            default:
                $thechar = '>';
                $order = 'ASC';
                break;
        }

        // get the last item out of the hierarchy and rebuild
        $query = 'SELECT content_alias FROM ' . CMS_DB_PREFIX .
        "content WHERE parent_id = ? AND item_order $thechar ? AND active = 1 ORDER BY item_order $order";
        $db = CmsApp::get_instance()->GetDb();
        return $db->GetOne($query, [$content->ParentId(), $content->ItemOrder()]);
    }

    /**
     * Get a file list for the specified sub-directory (in the site's uploads tree)
     *
     * @param string $dir The sub-directory path (ignored if absolute)
     * @param string $excludeprefix Optionally exclude files with this prefix.
     *
     * @return array maybe empty
     */
    public static function get_file_listing($dir, $excludeprefix = '')
    {
        //$dir might be Windoze-style absolute path and included separator(s) might be \ or /
        if (!preg_match('~^ *(?:\/|\\\\|\w:\\\\|\w:\/)~',$dir)) { //general test for a non-absolute path
            $config = cms_config::get_instance();
            $dir = cms_join_path($config['uploads_path'], $dir);
            $fileprefix = $excludeprefix ?: '';
            return get_matching_files($dir, '', true, true, $fileprefix, 1);
        }
        return [];
    }

    /**
     * Get the content object of a 'parallel' page relative to a different root alias.
     * If, say, the specified 'current_page' alias (or else the default page)
     * has a hierarchy id 4.1.1 and the 'new root' alias has a hierarchy id 5
     * this method will return the object for the page having hierarchy id
     * 5.1.1 (if that exists).
     * Useful for multi-lang sites.
     *
     * @param string $new_root     The alias of the new root page (e.g. fr)
     * @param mixed $current_page An optional page alias. Default ''. If not specified the current page is used.
     *
     * @return mixed ContentBase the parallel content object | null
     */
    public static function get_parallel_content($new_root, $current_page = '')
    {
        if (!$new_root) {
            return null;
        }

        if (!$current_page) {
            $current_page = cms_utils::get_current_alias();
        }

        $gCms = CmsApp::get_instance();
        $contentops = $gCms->GetContentOperations();
        $cur_content = $contentops->LoadContentFromAlias($current_page);

        if (!is_object($cur_content)) {
            return null;
        }

        $tmp = self::get_root_alias($new_root); // make sure we go to the root
        if ($tmp) {
            $new_root = $tmp;
        }

        $new_root_content = $contentops->LoadContentFromAlias($new_root);
        if (!is_object($new_root_content)) {
            return null;
        }

        $hier1 = $cur_content->Hierarchy();
        $hier2 = $new_root_content->Hierarchy();
        if (!$hier1 || !$hier2) {
            return null;
        }

        $a_hier1 = explode('.', $hier1);
        $a_hier2 = explode('.', $hier2);
        $a_hier1[0] = $a_hier2[0];
        $hier3 = implode('.', $a_hier1);

        // we have the new hierarchy... just gotta find the right page for it.
        $new_pageid = $contentops->GetPageIDFromHierarchy($hier3);
        return ($new_pageid) ? $contentops->LoadContentFromAlias($new_pageid) : null;
    }

    /**
     * Generate an URL for an anchor on the current page
     *
     * @param string $name The name of the anchor
     *
     * @return string maybe empty
     */
    public static function anchor_url($name)
    {
        if (!$name) {
            return '';
        }

        $name = trim($name);
        if (!$name) {
            return '';
        }

        $content_obj = cms_utils::get_current_content();

        if (!is_object($content_obj)) {
            return '';
        }

        $base = $content_obj->GetURL();
        if (!$base) {
            return '';
        }
        return $base . '#' . $name;
    }

    /**
     * Get the alias of a 'parallel' page relative to a different root alias.
     * If, say, the specified 'current_page' alias (or else the default page)
     * has a hierarchy id 4.1.1 and the 'new root' alias has a hierarchy id 5
     * this method will return the alias of the page having hierarchy
     * id 5.1.1 (if that exists).
     * Useful for multi-lang sites.
     *
     * @param string $new_root The alias of the new root page (e.g. fr)
     * @param mixed $current_page An optional page alias. Default ''. If not specified the current page is used.
     *
     * @return string the alias of the parallel page or ''
     */
    public static function get_parallel_page($new_root, $current_page = '')
    {
        $content = self::get_parallel_content($new_root, $current_page);
        return ($content) ? (string)$content->Alias() : '';
    }

    /**
     * Get the URL of a 'parallel' page relative to a different root alias.
     * If, say, the specified 'current_page' alias (or else the default page)
     * has a hierarchy id 4.1.1 and the 'new root' alias has a hierarchy id 5
     * this method will return the URL of the page having hierarchy id
     * 5.1.1 (if that exists).
     * Useful for multi-lang sites.
     *
     * @param string $new_root     The alias of the new root page (e.g. fr)
     * @param string $current_page An optional page alias. Default ''. If not specified the current page is used.
     *
     * @return string the url of the parallel page or ''
     */
    public static function get_parallel_url($new_root, $current_page = '')
    {
        $content = self::get_parallel_content($new_root, $current_page);
        return ($content) ? (string)$content->GetURL() : '';
    }
}
