<?php
/**
 * Smarty plugin: xt_getvar
 * @deprecated since 1.4.0 instead use cms_utils::get_app_data
 *
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */

function smarty_function_xt_getvar($params, $template)
{
    $key = (isset($params['var'])) ? $params['var'] : ((isset($params['v'])) ? $params['v'] : '');
    if ($key) {
        $val = cms_utils::get_app_data($key);
        if ($val === null) {
            if (isset($params['dflt'])) { $val = $params['dflt']; }
        }
    } else {
        $val = null;
    }
    $assign = get_parameter_value($params, 'assign');
    if ($assign) {
        $scope = strtolower(get_parameter_value($params, 'scope', 'local'));
        if ($scope == 'global') {
            $template->assignGlobal($assign, $val);
        } else {
            $template->assign($assign, $val);
        }
        return '';
    }
    return $val;
}
