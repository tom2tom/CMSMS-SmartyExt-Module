<?php
/**
 * Smarty plugin: sess_put
 *
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */
function smarty_function_sess_put($params, $template)
{
    if (!empty($params['var']) && (isset($params['value']) || is_null($params['value']))) {
        $var = trim($params['var']);
        $_SESSION[$var] = $params['value'];
    }
    return '';
}
