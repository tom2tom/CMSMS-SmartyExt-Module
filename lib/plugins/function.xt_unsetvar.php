<?php
/**
 * Smarty plugin: xt_unsetvar
 * @deprecated since 1.4.0 instead use cms_utils::set_app_data
 *
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */

function smarty_function_xt_unsetvar($params, $template)
{
    foreach ($params as $key => $val) {
        $key = trim($key);
        if ($key) {
            if ('unset' == $key) {
                if ($val) {
                    $list = explode(',', $val);
                    foreach ($list as $one) {
                        $one = trim($one);
                        if ($one) {
                            cms_utils::set_app_data($one, null);
                        }
                    }
                }
            } else {
                cms_utils::set_app_data($key, null);
            }
        }
    }
    return '';
}
