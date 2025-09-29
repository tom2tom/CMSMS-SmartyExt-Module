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
function smarty_modifier_protect($str)
{
    if ($str) {
        if (cms_utils::get_app_data('_CP_Allow')) {
            return $str;
        }
        // we add ability to override protected_msg
        // from {'foo':protect protected_msg='some message'}
        $protected_msg = (isset($params['protected_msg'])) ? //TODO $params not defined
            $params['protected_msg'] :
            cms_utils::get_app_data('_CP_Prot_Msg');
        if ($protected_msg) {
            return $protected_msg;
        }
    }
    return '';
}
?>
