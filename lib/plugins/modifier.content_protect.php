<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:	 modifier.content_protect.php
 * Type:	 modifier
 * Name:	 content_protect
 * Purpose:	 protect contents
 * -------------------------------------------------------------
 */
function smarty_modifier_content_protect($str)
{
    if (isset($str)) {
        if (cms_utils::get_app_data('_CP_Allow')) {
            return $str;
        }
        // the displayed protected-content message can be from the template
        // {'foo':content_protect protected_msg='some message'}
        $protected_msg = (!empty($params['protected_msg'])) ? $params['protected_msg'] : cms_utils::get_app_data('_CP_Prot_Msg'); //TODO $params not defined
        if ($protected_msg) {
            return $protected_msg;
        }
    }
    return '';
}
?>
