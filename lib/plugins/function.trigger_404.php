<?php
/**
 * Smarty plugin: trigger_404
 *
 * Copyright (C) 2023 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */
function smarty_function_trigger_404($params, $template)
{
    if (!empty($params['active'])) {
        $msg = (!empty($params['msg'])) ? $params['msg'] : 'This content is not available';
        throw new CmsError404Exception($msg);
    }
    return '';
}
