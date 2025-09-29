<?php
/**
 * Smarty pPlugin: xt_repeat
 * @see also {repeat}
 * 
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */
function smarty_function_xt_repeat($params, $template)
{
    if (empty($params['text'])) {
        return '';
    }
    $text = $params['text'];

    $num = 1;
    if (isset($params['count'])) {
        $num = (int)$params['count'];
        if ($num < 1) $num = 1;
    }

    $out = '';
    for ($i = 0; $i < $num; ++$i) {
        $out .= $text;
    }

    if (!empty($params['assign'])) {
        $template->assign(trim($params['assign']), $out);
        return '';
    }
    return $out;
}
