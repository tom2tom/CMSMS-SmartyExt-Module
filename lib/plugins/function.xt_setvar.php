<?php
/**
 * Smarty plugin: xt_setvar
 * @deprecated since 1.4.0 instead use cms_utils::set_app_data
 *
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */

function smarty_function_xt_setvar($params, $template)
{
    $C_UNSET = '_unset_';

    foreach ($params as $key => $val) {
        $key = trim($key);
        if ($key) {
            if ($C_UNSET === $val) {
                cms_utils::set_app_data($key, null);
            } else {
                cms_utils::set_app_data($key, $val);
            }
        }
    }
    return '';
}
