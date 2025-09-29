<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     block.content_protect.php
 * Type:     block
 * Name:     content_protect
 * Purpose:  protect a block of text
 * -------------------------------------------------------------
 */
function smarty_block_protect($params, $content, Smarty_Internal_Template $template, &$repeat)
{
  //$current_page_alias = cms_utils::get_current_alias();
  $allow = cms_utils::get_app_data('_CP_Allow');
  
  // only output on the closing tag
  if(!$repeat && isset($content))
  {
    if($allow)
    {
      return isset($params['assign'])
        ? $template->assign($params['assign'], $content)
        : $content;
    }
  
    $protected_msg = (  # we add ability to override protected_msg from {protect protected_msg='some message'}
      $params['protected_msg'] ?? cms_utils::get_app_data('_CP_Prot_Msg')
    );
  
    if(isset($protected_msg))
    {
      return isset($params['assign'])
        ? $template->assign($params['assign'], $protected_msg)
        : $protected_msg;
    }
  }
}
?>