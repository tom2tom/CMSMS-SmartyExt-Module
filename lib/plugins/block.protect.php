<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:	 block.content_protect.php
 * Type:	 block
 * Name:	 content_protect
 * Purpose:	 protect a block of text
 * -------------------------------------------------------------
 */
function smarty_block_protect($params, $content, Smarty_Internal_Template $template, &$repeat)
{
    // only output on the closing tag
    if (!$repeat && $content) {
        if (cms_utils::get_app_data('_CP_Allow')) {
            if (!empty($params['assign'])) {
                $template->assign($params['assign'], $content);
                return '';
            }
            return $content;
        }

        // we can override protected_msg from {protect protected_msg='some message'}
        $protected_msg = (!empty($params['protected_msg'])) ?
            $params['protected_msg'] :
            cms_utils::get_app_data('_CP_Prot_Msg');

        if ($protected_msg) {
            if (!empty($params['assign'])) {
                $template->assign($params['assign'], $protected_msg);
                return '';
            }
            return $protected_msg;
        }
    }
    return '';
}
