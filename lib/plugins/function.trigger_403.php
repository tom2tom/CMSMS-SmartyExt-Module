<?php
/**
 * Smarty plugin: trigger_403
 *
 * Copyright (C) 2023 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */
function smarty_function_trigger_403($params, $template)
{
    if (!empty($params['active'])) {
        $msg = (!empty($params['msg'])) ? $params['msg'] : 'Permission denied!';
        throw new CmsError403Exception($msg);
    }
    return '';
}
