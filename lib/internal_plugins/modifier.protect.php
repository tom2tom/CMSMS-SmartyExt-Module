
<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.content_protect.php
 * Type:     modifier
 * Name:     content_protect
 * Purpose:  protect contents
 * -------------------------------------------------------------
 */
function smarty_modifier_protect($string)
{
  $allow = cms_utils::get_app_data('_CP_Allow');
  
  if(isset($string))
  {
    if($allow) { return $string; }
    # we add ability to override protected_msg
    # from {'foo':protect protected_msg='some message'}
    $protected_msg = (
      $params['protected_msg'] ?? cms_utils::get_app_data('_CP_Prot_Msg')
    );
    
    if(isset($protected_msg)) { return $protected_msg; }
  }
}
?>