<?php
/**
 * Smarty plugin: xt_anchor_link
 * @see also {anchor}
 *
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */

use SmartyExt\smx;

function smarty_function_xt_anchor_link($params, $template)
{
    $name = get_parameter_value($params, 'n');
    $name = get_parameter_value($params, 'name', $name);
    $text = get_parameter_value($params, 'text', $name);
    $assign = get_parameter_value($params, 'assign');
    $urlonly = get_parameter_value($params, 'u');
    $urlonly = cms_to_bool(get_parameter_value($params, 'urlonly', $urlonly));

    unset($params['name'], $params['n'], $params['assign'], $params['u'], $params['urlonly'], $params['text']);

    $url = '';
    if ($name) {
        $url = smx::anchor_url($name);
    }

    if ($urlonly) {
        $out = $url;
    } else {
        // build a link with all the leftover params (don't filter them, there are lots of valid params for a link).
        $out = sprintf('<a href="%s"', $url);

        foreach ($params as $key => $val) {
            $out .= " $key=\"$val\"";
        }

        $out .= '>' . $text . '</a>';
    }

    if ($assign) {
        $template->assign($assign, $out);
        return '';
    }
    return $out;
}
