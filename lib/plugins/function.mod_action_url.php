<?php
/**
 * Smarty plugin: mod_action_url
 * @deprecated since 1.4.0 instead use {cms_action_url}
 *
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */
function smarty_function_mod_action_url($params, $template)
{
    $assign = !empty($params['assign']) ? trim($params['assign']) : '';
    unset($params['imageonly'], $params['text'], $params['title'], $params['image'], $params['class'], $params['assign']);
    $params['urlonly'] = 1;
    require_once __DIR__ . DIRECTORY_SEPARATOR . 'function.mod_action_link.php';
    $out = smarty_function_mod_action_link($params, $tpl);

    if ($assign) {
        $tpl->assign($assign, $out);
        return '';
    }
    return $out;
}
