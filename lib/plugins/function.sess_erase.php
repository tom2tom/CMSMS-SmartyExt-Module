<?php
/**
 * Smarty plugin: sess_erase
 *
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */
function smarty_function_sess_erase($params, $template)
{
    if (!empty($params['var'])) {
        $var = trim($params['var']);
        if ($var && (isset($_SESSION[$var]) || is_null($_SESSION[$var]))) {
            unset($_SESSION[$var]);
        }
    }
    return '';
}
