<?php
/**
 * Smarty plugin: mod_action_link
 * @deprecated since 1.4.0 instead use {cms_action_url}
 *
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */
function smarty_function_mod_action_link($params, $template)
{
    $module = $template->get_template_vars('module');

    if (!$module) {
        $module = $template->get_template_vars('actionmodule');
    }

    $module = get_parameter_value($params, 'module', $module);

    if (!$module) {
        $module = $template->getTemplateVars('module');
    }
    if (!$module) {
        $module = $template->getTemplateVars('actionmodule');
    }
    if (!$module) {
        $module = $template->getTemplateVars('_module');
    }
    if (!$module) {
        return '';
    }

    $obj = cms_utils::get_module($module);
    if (!is_object($obj)) {
        return '';
    }
    unset($params['module']);

    $text = $module;
    if (isset($params['text'])) {
        $text = trim($params['text']);
        unset($params['text']);
    }

    $title = '';
    if (isset($params['title'])) {
        $title = trim($params['title']);
        unset($params['title']);
    }

    $confmessage = '';
    if (isset($params['confmessage'])) {
        $confmessage = trim($params['confmessage']);
        unset($params['confmessage']);
    }

    $image = '';
    if (isset($params['image'])) {
        $image = trim($params['image']);
        unset($params['image']);
    }

    $class = 'systemicon';
    if (isset($params['class'])) {
        $class = trim($params['class']);
        unset($params['class']);
    }

    $action = 'default';
    if (isset($params['action'])) {
        $action = $params['action'];
        unset($params['action']);
    }

    $gCms = cmsms();
    $mid = $template->getTemplateVars('actionid');
    if (!$mid) {
        $mid = 'm1_';
        if ($gCms->is_frontend_request()) {
            $mid = 'cntnt01';
        }
    }

    $inline = false;
    if (isset($params['id'])) {
        $mid = $params['id'];
        $inline = true;
        unset($params['id']);
    }

    $imageonly = false;
    if (isset($params['imageonly'])) {
        $imageonly = true;
        unset($params['imageonly']);
    }

    $pageid = cms_utils::get_current_pageid();
    if (isset($params['page'])) {
        // convert the page alias to an id
        $manager = $gCms->GetHierarchyManager();
        $node = $manager->sureGetNodeByAlias($params['page']);
        if (isset($node)) {
            $content = $node->GetContent();
            if (isset($content)) {
                $pageid = $content->Id();
            }
        } else {
            $node = $manager->sureGetNodeById($params['page']);
            if (isset($node)) {
                $pageid = $params['page'];
            }
        }
        unset($params['page']);
    }

    $urlonly = !empty($params['urlonly']);
    if ($urlonly) {
        $urlonly = true;
        unset($params['urlonly']);
    }

    $jsfriendly = !empty($params['jsfriendly']);
    if ($jsfriendly) {
        $jsfriendly = true;
        $urlonly = true;
        unset($params['jsfriendly']);
    }

    $forjs = !empty($params['forjs']);
    if ($forjs) {
        $jsfriendly = true;
        $urlonly = true;
        unset($params['forjs']);
    }

    $forajax = !empty($params['forajax']);

    if ($forajax) {
        $jsfriendly = true;
        $urlonly = true;
        $forajax = true;
        unset($params['forajax'], $params['for_ajax']);
    }

    $assign = '';
    if (isset($params['assign'])) {
        $assign = trim($params['assign']);
        unset($params['assign']);
    }

    $addtext = '';
    if ($title) {
        $addtext = 'title="' . $title . '"';
    }

    if (!empty($image) && method_exists($obj, 'CreateImageLink') && false == $urlonly) {
        $output = $obj->CreateImageLink(
            $mid,
            $action,
            $pageid,
            $text,
            $image,
            $params,
            $class,
            $confmessage,
            $imageonly,
            false,
            $addtext
        );
    } else {
        $output = $obj->CreateLink($mid, $action, $pageid, $text, $params, $confmessage, $urlonly, $inline, $addtext);
        if ($urlonly && $jsfriendly) {
            $output = str_replace('amp;', '', $output);
        }
        if ($forajax) {
            if (false !== strpos($output, '?')) {
                $output .= '?showtemplate=false';
            } else {
                $output .= '&showtemplate=false';
            }
        }
    }

    // all done
    if (!empty($assign)) {
        $template->assign($assign, $output);
        return '';
    }
    return $output;
}
