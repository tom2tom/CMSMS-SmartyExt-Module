<?php
#----------------------------------------------------------------------
# Plugin: page_protect - Single Page Simple Password Protection
# Version 1.2.0
# (c) 2012 -2014 Fernando Morgado (JoMorg) jomorg.morg@gmail.com
# 
# Simple plugin to allow to protect a page with a password
# 
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple.  You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin 
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------

function smarty_function_content_protect($params, $smarty)
{
  # initialize a few vars
  $current_page_alias = cms_utils::get_current_alias();
  $logout_alias       = $params['logout_alias'] ?? $current_page_alias;
  $login_alias        = $params['login_alias'] ?? NULL;
  $assign_var         = $params['assign'] ?? 'cp_logged_in';
  
  # some of the params have to be persistent through all the page request,
  # so we use the same method as ModuleHint i.e. cms_utils Data Storage
  
  # flag to check if current user is authenticated
  $allow  = cms_utils::get_app_data('_CP_Allow');
  
  if( !isset($allow) ) { $allow = false; }
  
  # timeout in minutes (cookie lifecycle). Null or 0 (zero) means off
  $timeout     = cms_utils::get_app_data('_CP_TOut');
  $use_timeout = cms_utils::get_app_data('_CP_useTOut');
  
  if(!$timeout)
  {
    if(isset($params['timeout']))
    {
      $interval = $params['timeout'] * 60;
    }
    
    $timeout = ( isset($interval) ? time() + $interval : 0 );           
    $use_timeout = ( $timeout > 0 );
    cms_utils::set_app_data('_CP_TOut', $timeout);
    cms_utils::set_app_data('_CP_useTOut', $use_timeout);
  }
  
  $cookie_name  = cms_utils::get_app_data('_CP_CookieN');
  
  if( !isset($cookie_name) )
  {
    $cookie_name = $params['cookie_name'] ?? 'cp_auth';
                   
    cms_utils::set_app_data('_CP_CookieN', $cookie_name );
  } 
  
  ##### messages #####  
  $protected_msg = cms_utils::get_app_data('_CP_Prot_Msg');
  
  if(!isset($protected_msg))
  {
    $protected_msg = isset($params['protected_msg']) 
                     ? $params['$protected_msg'] 
                     : $protected_msg;
                     
    cms_utils::set_app_data('_CP_Prot_Msg', $protected_msg );
  } 
    
  $error_msg = cms_utils::get_app_data('_CP_Error_Msg');
  
  if(!isset($error_msg))
  {
    $error_msg = $params['error_msg'] ?? 'The password is not correct.';
                 
    cms_utils::set_app_data('_CP_Error_Msg', $error_msg );
  } 
  
  $welcome_msg = cms_utils::get_app_data('_CP_Welcome_Msg');
  
    
  if(!isset($welcome_msg))
  {
    $welcome_msg = $params['welcome_msg'] ?? 'Please enter the password to access this page.';
                   
    cms_utils::set_app_data('_CP_Welcome_Msg', $welcome_msg );
  } 
  ##### messages end #####
  
  if(!isset($params['passwords']))
  {
    $passwords = cms_utils::get_app_data('_CP_Pass');
  }
  else
  {
    $passwords = &$params['passwords'];
  }

  if(!is_array($passwords))
  {
    if(false === strpos($passwords, ','))
    {
      $passwords = [$passwords];
    }
    else
    {
      $passwords = explode(',', $passwords);
    }
  }
   
  cms_utils::set_app_data('_CP_Pass', $passwords);

  ##### process requests and Cookies #####
  $cookie_found = false;
  
  # logout
  if(isset($_POST['cp_logout']))
  {
    # clear pass from cookie and redirect
    setcookie($cookie_name, '', 0, '/'); 
    $allow = false;
    redirect_to_alias($logout_alias);
  }
  
  # process pass
  if(isset($_POST['cp_password']))
  {
    $pass = $_POST['cp_password'];
    
    if( !in_array($pass, $passwords, TRUE))
    {
      $msg = $error_msg;
      $allow = false;
    }
    else 
    {
      if($use_timeout)
      {
        # we set cookie here
        #setcookie($cookie_name, md5($username . '%cp%' . $pass), $timeout, '/'); # todo
        setcookie($cookie_name, md5($pass), $timeout, '/');
      }
      
      $allow = true;
      
      # @since 1.1
      if( !isset($login_alias) )
      {
        redirect_to_alias($login_alias);
      }
    }

  }
  else if($use_timeout)
  {
    # check if cookie exists and is set
    if(!isset($_COOKIE[$cookie_name]))
    {
      $msg   = '';
      $allow = FALSE;
    }
    
    foreach($passwords as $one)
    {
      if( isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] === md5($one) )
      {
        $cookie_found = TRUE;
        $allow        = TRUE;
        
        # so we have a valid request: extend timeout
        setcookie($cookie_name, md5($one), $timeout, '/');
        break;
      }
    }
    
    if(!$cookie_found)
    {
      $allow = false;
      # may very well be redundant but hey!!! we clear any pending messages anyway
      $msg = '';
    }
  }
  ##### end process requests and Cookies #####
  
  cms_utils::set_app_data('_CP_Allow', $allow);
  
  ##### actions ##### 
  # @since 1.2
  $action = $params['action'] ?? 'default';

  switch ($action) 
  {
    case 'form':
    {
      # common to both buttons
      $button_id = isset($params['button_id']) 
                   ? 'id="' . $params['button_id'] . '" '
                   : '';        
      
      $button_class = isset($params['button_class']) 
                      ? 'class="' . $params['button_class'] . '" '
                      : '';
                          
      if(!$allow)
      {
        $login_btn = $params['login_btn'] ?? 'Login';
        
        $form_id = isset($params['form_id']) 
                   ? 'id="' . $params['form_id'] . '" '
                   : '';        
        
        $form_class = isset($params['form_class']) 
                      ? 'class="' . $params['form_class'] . '" '
                      : '';
                    
                            
        $in_pass_id = isset($params['in_pass_id']) 
                      ? 'id="' . $params['in_pass_id'] . '" '
                      : '';        
        
        $in_pass_class = isset($params['in_pass_class']) 
                         ? 'class="' . $params['in_pass_class'] . '" '
                         : '';
                         
        # @since 1.2 -> avoid warnings
        $msg = $msg ?? '';
  
        $html = '<form ' . $form_id . $form_class . ' method="post">';
        $html .= '<h3>'. $welcome_msg . '</h3>';
        $html .= '<p style="color: red ">' . $msg . '</p>';
        $html .= '<input ' . $in_pass_id . $in_pass_class . 'type="password" name="cp_password" autocomplete="off" />';
        $html .= '<input ' . $button_id . $button_class . 'type="submit" name="Submit" value="' . $login_btn . '" />';
  
      }
      else
      {
        $logout_btn = $params['logout_btn'] ?? 'Logout';
  
        $html = '<form method="post">';
        $html .= '<input' . $button_id . $button_class . ' type="submit" name="cp_logout" value="' . $logout_btn . '" />';
      }
      
      $html .= '</form>';
  
      # @since 1.2
        if(isset($params['assign_output']))
        {
          $smarty->assign($params['assign_output'], $html); 
        }
        else
        {
          $ret = $html;
        }   
    
    }
    break;
    
    # default actions:
    case 'set':
    case 'default':
    default:
    {
      # dummy actions: so one can set persistent parameters without triggering other actions
    }

  }
  
  # send a var to the calling template, either assigned name or defaults to {$cp_logged_in}
  $smarty->assign($assign_var, $allow);
  
  return $ret ?? NULL; # @since 1.2 -> if we come from forms action we may have $ret set
}

##### Docs #####
function smarty_cms_help_function_content_protect()
{
  $txt = <<<'EOT'
<!--/tab header: General/-->

<div id="page_tabs">
  <div id="general">
    General
  </div>
   
 <!--/tab header: The Quick Way/-->
     
  <div id="quick">
    The Quick Way
  </div>
  
  <!--/tab header: Initializing/-->
  
  <div id="init">
    Initializing
  </div>  
  
  <!--/tab header: Form Action/-->
  
  <div id="form">
    The Form Action
  </div>
   
  <!--/tab header: Set Action/-->
  
  <div id="set">
    The Set Action
  </div>
   
  <!--/tab header: Protect Tags/-->
  
  <div id="protect">
    The Protect Tags
  </div>
  
  <!--/tab header: Snippets/-->
         
  <div id="snippets">
    Snippets
  </div>
    
  <!--/tab header: Final Notes/-->
         
  <div id="notes">
    Final Notes
  </div>
  
</div>

<div class="clearb"></div>

<!--/tab: General/-->

<div id="general_c">

  <h3>What does this do?</h3>
  <p>This plugin allows you to protect a number pages with one or more passwords.</p>
  <p>This plugin can be used on any number of pages, either by being set once per each page you want to protect, or by being set on a page template, allowing you to protect all pages connected to that template</p>
  <h3>How to use it?</h3>
  <p>As of version 1.2, this plugin can work in two different ways:</p>
  <ul>
    <li>the typical way where you initialize the plugin either on the template, using the following tag <strong>{page_protect}</strong> on the very beginning of the template, and right before the<strong>{process_pagedata}</strong> if it exists, or on the field <strong>Smarty data or logic that is specific to this page</strong>* found on each page <em><strong>options tab</strong></em>;</li>
    <li>a different faster way where you don't have to initialize the plugin, but with which you lose flexibility: use a minimal tag call with the form and check for a <strong>Smarty</strong> variable, <strong>$pp_logged_in</strong> by default, with some <strong>Smarty</strong> logic;</li>
  </ul>
  <p>Note that these two methods are not mutually exclusive: one adds to the other.</p>
  
  <div class="warning" style="display:block;">
  <p><strong>* Note: </strong>this only works if the tag <strong>{process_pagedata}</strong> is present on the template and is called before any other content block.</p>
  </div>
  
  <h3>What parameters does it take?</h3>
  <ul>
    <li><em>(optional) <strong>action</strong></em> - possible values:
      <ul>
        <li><em><strong>'default</strong></em><strong>'</strong>: the default action of the plugin which is to initialize itself;</li>
        <li><em><strong>'form</strong></em><strong>'</strong>: show either the <strong>login</strong> form or the <strong>logout</strong> form, depending on current user state;</li>
        <li><em><strong>'set</strong></em><strong>'</strong>: use this action to distribute persistent parameters though different tags on the same page (helps readability);</li>
      </ul>
    </li>
    <li><em>(optional) (persistent) <strong>login_alias</strong></em> - an existing page alias (defaults to no redirection): this will be used to redirect after login if needed;</li>  
    <li><em>(optional) (persistent) <strong>logout_alias</strong></em> - an existing page alias (defaults to current page i.e. the page where the plugin is being used): this will be used to redirect after logout;</li>
    <li><em>(optional) (persistent) <strong>timeout</strong></em> - if set to a value higher then 0 it will set a cookie and use this value as minutes before login times out;</li>
    <li><em>(optional) (persistent) <strong>cookie_name</strong></em> - the name of the cookie (defaults to <strong>pp_auth</strong>);</li>
    <li><em>(optional) (persistent) <strong>welcome_msg</strong></em> - a message to be shown as a welcome text on the login form;</li>
    <li><em>(optional) (persistent) <strong>protected_msg</strong></em> - a message to replace the protected content (defaults to no message at all);</li>
    <li><em>(optional) (persistent) <strong>error_msg</strong></em> - an error message to be shown on password errors;</li>
  </ul>
  <h5>Exclusive to form action:</h5>
  <ul>
    <li><em>(optional) <strong>login_btn</strong></em> - text of the login button caption (defaults to Login);</li>
    <li><em>(optional) <strong>logout_btn</strong></em> - text of the logout button caption(defaults to Logout;</li>      
    <li><em>(optional) <strong>form_id</strong></em> - login form id;</li>
    <li><em>(optional) <strong>form_class</strong></em> - login form class;</li>
    <li><em>(optional) <strong>in_pass_id</strong></em> - password input id;</li>
    <li><em>(optional) <strong>in_pass_class</strong></em> - password input class;</li>
    <li><em>(optional) <strong>button_id</strong></em> - both buttons id;</li>
    <li><em>(optional) <strong>button_class</strong></em> - both buttons class;</li>
  </ul>
    <h5>Protect tag accepts only one parameter:</h5>
  <ul>
    <li><em>(optional) <strong>protected_msg</strong></em> - a message to replace the protected content (defaults to whatever was set on a previous persistent tag call, typically on the <em><strong>default</strong></em> action);</li>
  </ul>
  <div class="warning" style="display:block;">
    <p><strong>Note: </strong>With the <strong>CMSMS 2.x branch</strong> the block plugin doesn't currently work, so <strong>{protect}{/protect}</strong> will break.</p>
  </div>     
  <div class="warning" style="display:block;">
    <p><strong>Note: </strong>Because of the way CMSMS processes some requests, the <strong>ONLY SECURE</strong> use of this plugin is the one on the first example of the <strong>The Quick Use</strong> example, that is the one with all the logic inside the content block(s).</p>
  </div>       
  <div class="warning" style="display:block;">
    <p><strong>Note: </strong>This should be the last implementation of this plugin. It's functionality will be implemented in a module, about to be released. Thanks.</p>
  </div>   
</div>

<!--/tab: The Quick Way/-->

<div id="quick_c">

  <h3>The Quick Use</h3>
  <p>As of version 1.2 the plugin has a new functionality which allows for a faster deployment of a secure page. Page Protect will assign a <strong>Smarty</strong> variable, <strong>$pp_logged_in</strong> by default, with a boolean value flagging whether the current user is logged in or not.</p>
 <div class="information" style="display:block;">
 <p>As an example:</p> 
 <p><strong>{page_protect action='form' passwords='pass1'}</strong></p> 
 </div>
 <hr />
  <p>This is a simple way to hide the content of a page from non authorized users:</p>
  <div class="information" style="display:block;">
  <p>Use this on the page content!</p>
  </div>
  <p>
    <pre>
    
{page_protect action='form' passwords='pass1'}
{if $pp_logged_in}
  The allowed content....
{else}
  Not Logged in!
{/if}
    </pre>
  </p>
  <p>The previous snippet will show a form for non logged in users, along with the text "Not Logged in!"</p>
  <p>For the logged in user will present a logout button, and "The allowed content....".</p>
  <hr />
  <p>This is a simple way to hide the content of all pages with this template from non authorized users:</p>
  <div class="information" style="display:block;">
  <p>Use this on the main template!</p>
  </div>
  <p>
    <pre>

{content assign='content'}
{page_protect action='form' passwords='pass1'}
{if $pp_logged_in}
  {$content}
{else}
  Not Logged in!
{/if}
    </pre>
  </p>
  <p>The previous snippet will show a form for non logged in users, along with the text "Not Logged in!"</p>
  <p>For the logged in user will present a logout button, and the content of the page.</p>
  <div class="warning" style="display:block;">
  <p><strong>Note:</strong> by using only this method, without initializing the plugin, you <strong>cannot</strong> use the <strong>{protect}{/protect}</strong> tags. To use them you need to initialize the plugin!</p>
  </div>
  
</div>

<!--/tab: Initializing/-->

<div id="init_c">
  
  <h3>Initializing</h3>
  <p>In previous versions, the plugin needed to be initialized to work at all. As of version 1.2 the initialization is optional. However if the plugin is not initialized it will lose versatility. That doesn't mean that the quick setup, without the initialization, is not a valid setup. It is however a setup with less functionality, thus having its drawbacks.</p>
  <h4>Why Initialize At All</h4>
  <p>By initializing the plugin you will have registered with the <strong>Smarty engine</strong> a block tag, <strong>{protect}{/protect}</strong>, which allow 
  you to protect large blocks of text easily.</p>
  <h4>How To Initialize</h4>
  <p>The initialization can be made through one of two methods, depending on what you want to protect. The most common scenarios are the following:</p>
  <ul>
    <li>protecting a single page with a set of passwords;</li>
    <li>protecting a number of pages with specific passwords (one or more but different sets) per page;</li>
    <li>protecting a number of pages with the same set of passwords;</li>
    <li>protecting different groups of pages with the same set of passwords per group, but different from group to group;</li>
  </ul>
  <p>In the first two cases, the initialization should be made on each page, preferably on the field <strong>Smarty data or logic that is specific to this page</strong>* found on the page <em><strong>options tab</strong></em>.</p>
  
  <div class="information" style="display:block;">
    <p>Use one of these tags:</p>
    <ul>
      <li><strong>{page_protect}</strong></li>
      <li><strong>{page_protect action='</strong>default<strong>'}</strong></li>
    </ul> 
    <p>wich are equivalent.</p>    
    <p>You can also use this tag to add a few parameters except for action='set' and action='form' (<em>and all form exclusive parameters as they are only pertinent to the form action and not persistent</em>). Use:</p>    
    <ul>
      <li><strong>{page_protect passwords='</strong>pass1[,pass<em><strong>2</strong></em>]...[,pass<em><strong>n</strong></em>]<strong>'}</strong></li>
      <li><strong>{page_protect action='</strong>default<strong>' passwords='</strong>pass1[,pass<em><strong>2</strong></em>]...[,pass<em><strong>n</strong></em>]<strong>'}</strong></li>
    </ul>
  </div>
  
  <div class="warning" style="display:block;">
  <p><strong>* Note: </strong>this only works if the tag <strong>{process_pagedata}</strong> is present on the template and is called before any other content block.</p>
  </div>
  
  <p>Put this on the field <strong>Smarty data or logic that is specific to this page</strong> found on the page <em><strong>options tab</strong></em>:
    <pre>
    
<strong>{page_protect passwords='password1,passwor2,password3,passwordn'}</strong>
    </pre>
  </p>
  
  <p>Alternatively, if you are not using the <strong>{process_pagedata}</strong> on your templates, you can still initialize the plugin, for a given page, by using the initialization tag on an extra content block which should be placed at the top of the template as the very first call.   
  <p>Also alternatively, if you are not using the <strong>{process_pagedata}</strong> on your templates, you can still initialize the plugin, for a given template, by using the initialization tag on the top of the template as the very first. Keep in mind that all pages using that template will recognize the <strong>{protect}{/protect}</strong> tags. Also note that this method is also used for the 3rd and 4th scenarios, as it will be explained later.</p>  
  
  <p>Although you can, you don't need to set the passwords, nor any other parameters on the initialization tag: as most of the parameters are persistent through the same request, there is a special action, <strong>set</strong>, that can be used specifically to set persistent parameters anywhere on a template or content block (<em>see more on the set action help tab</em>).</p>
  
  <div class="warning" style="display:block;">
    <p><strong>Note:</strong> you <strong>cannot</strong> use <strong>{protect}{/protect}</strong> on the same content block where you initialized the plugin. The initialization won't be in effect yet and Smarty will not recognize the new tags at that point.</p>
  </div>  
  <div class="warning" style="display:block;">
      <p><strong>Note:</strong> you <strong>can</strong> use <strong>{protect}{/protect}</strong> on the same template where you initialized the plugin. However the <strong>{protect}{/protect}</strong> tags will have to be placed inside the <strong>&lt;BODY&gt;...&lt;/BODY&gt;</strong> tags or the <strong>&lt;BODY&gt;...&lt;/BODY&gt;</strong> tags, while the <strong>{page_protect}</strong> needs to be outside the <strong>&lt;HTML&gt;...&lt;/HTML&gt;</strong> tags, or processed through <strong>{process_pagedata}</strong>.</p>
    <p>It is important to understand the sequence by which CMSMS parses templates:</p>
    <ul>
      <li><strong>1st:</strong> everything outside the <strong>&lt;HTML&gt;...&lt;/HTML&gt;</strong> tags;</li>
      <li><strong>2nd:</strong> everything inside the <strong>&lt;BODY&gt;...&lt;/BODY&gt;</strong> tags;</li>
      <li><strong>3rd:</strong> everything inside the <strong>&lt;HEAD&gt;...&lt;/HEAD&gt;</strong> tags;</li>
    </ul> 
    <p>This means that each of these template areas is considered by CMSMS as a different block, and parsed tags and variables will be carried forward to the next in that sequence. It also guarantees that the tags <strong>{protect}{/protect}</strong> will be usable inside the <strong>&lt;BODY&gt;...&lt;/BODY&gt;</strong> tags and the <strong>&lt;BODY&gt;...&lt;/BODY&gt;</strong> tags, if properly initialized outside the <strong>&lt;HTML&gt;...&lt;/HTML&gt;</strong> tags, particularly at the top of the template.</p>
  </div>
  
  <p>Finally in the last two scenarios, the initialization should be made on the template, preferably on the top of the template and outside the <strong>&lt;HTML&gt;...&lt;/HTML&gt;</strong> tags.</p>  
  <p>If you only need to protect a number of pages with the same set of passwords, the 3rd case, you'll need only one extra template with the initialization tag. In the 4th case, where you need to protect different groups of pages with the same set of passwords per group, but different passwords from group to group, you may still use one template and set the passwords with some Smarty logic, but if you are not experienced with Smarty, then just create a template for each set of passwords, and assign the templates to the pages accordingly.</p>
    <p>Put this on the top of your templates:
    <pre>
    
<strong>{page_protect passwords='password1,passwor2,password3,passwordn'}</strong>
    </pre>
  </p>
   <div class="information" style="display:block;">
    <p>For specific help on the other <strong>actions</strong>, and the <strong>protect</strong> block tags, check the respective <em><strong>action help tabs</strong></em>.</p>  
  </div>

</div>

<!--/tab: Form Action/-->


<div id="form_c">

  <h3>The Form Action</h3>
  
  <p>As of version 1.2, you can set all options on the tag call with this action. If you are using the quick way, this is all you need to set a Smarty variable which you can check anywhere on the template after this call.</p>
  <p>Other than that just place the tag where you want a login/logout form. The default <strong><em>form</em> action</strong> has default values for all form parameters, so the minimal tag is <strong>{page_protect action='</strong><em>form</em><strong>'}</strong>.</p>
  
  <div class="information" style="display:block;">
    <p>use <strong>{page_protect action='</strong><em>form</em><strong>'}</strong> to display the default login/logout form.</p>  
  </div>

  <p> An example with all the parameters you can use to customize the form:
    <pre>
    
{* Do you really need all this?!!! A complete form call *}
{page_protect action='form' login_btn='Let Me In!' logout_btn='Bye Bye!' form_class='css_form' form_id='css_my_form' in_pass_id='css_passwrdid' in_pass_class='css_passwrd_class' button_id='css_btn_id' button_class='css_btn_class'}
    </pre>
  </p>
   <div class="information" style="display:block;">
    <p>As of <strong>version 1.2</strong> any call to {page_protect action='<em>...</em>'}</strong>, with any action, including the default, will set a Smarty variable, which can be customized by the <em>assign</em> parameter, but which will default to <strong>$pp_logged_in</strong> by omission. This will be set to boolean <strong>TRUE</strong> whenever the current visitor is logged in and <strong>FALSE</strong> otherwise.</p>
  </div>
  
  <div class="information" style="display:block;">
    <p>For specific help on the <strong>The Quick Way</strong> check the respective <em><strong>action help tab</strong></em>.</p>  
  </div>

  <p>You can use any of the persistent parameters with the form action if needed. However the form specific parameters <strong>are not persistent</strong>.</p>
  
  <h5>Parameters exclusive to form action:</h5>
  <ul>
    <li><em>(optional) <strong>login_btn</strong></em> - text of the login button caption (defaults to Login);</li>
    <li><em>(optional) <strong>logout_btn</strong></em> - text of the logout button caption(defaults to Logout;</li>      
    <li><em>(optional) <strong>form_id</strong></em> - login form id;</li>
    <li><em>(optional) <strong>form_class</strong></em> - login form class;</li>
    <li><em>(optional) <strong>in_pass_id</strong></em> - password input id;</li>
    <li><em>(optional) <strong>in_pass_class</strong></em> - password input class;</li>
    <li><em>(optional) <strong>button_id</strong></em> - both buttons id;</li>
    <li><em>(optional) <strong>button_class</strong></em> - both buttons class;</li>
  </ul>

        
</div> 

<!--/tab: Set Action/-->

<div id="set_c">

  <h3>The Set Action</h3>

  <p>This is a special action with the sole purpose of allowing you to set persistent parameters on different tag calls, helping a bit with the readability of the tags: just keep in mind that if you call it using the same parameter with different values, the last value will override all previous.</p>
  <p>
    <pre>
{* Using the 'set' action to spread parameters through multiple calls *}
{* redirect Home *}
{page_protect action='set' logout_alias='home'}
{* set the time before a login expires *} 
{page_protect action='set' timeout=10}
{* set the message to show in case the authentication fails *}
{page_protect action='set' error_msg='Oops! Wrong pass, mate! Check your notes...'}
{* setting all the above in a single tag call could lead to errors *}
    </pre>
  </p>
  
  <h4>What parameters does it take?</h4>
  <ul>
    <li><em>(optional) (persistent) <strong>login_alias</strong></em> - an existing page alias (defaults to no redirection): this will be used to redirect after login if needed;</li>  
    <li><em>(optional) (persistent) <strong>logout_alias</strong></em> - an existing page alias (defaults to current page i.e. the page where the plugin is being used): this will be used to redirect after logout;</li>
    <li><em>(optional) (persistent) <strong>timeout</strong></em> - if set to a value higher then 0 it will set a cookie and use this value as minutes before login times out;</li>
    <li><em>(optional) (persistent) <strong>cookie_name</strong></em> - the name of the cookie (defaults to <strong>pp_auth</strong>);</li>
    <li><em>(optional) (persistent) <strong>welcome_msg</strong></em> - a message to be shown as a welcome text on the login form;</li>
    <li><em>(optional) (persistent) <strong>protected_msg</strong></em> - a message to replace the protected content (defaults to no message at all);</li>
    <li><em>(optional) (persistent) <strong>error_msg</strong></em> - an error message to be shown on password errors;</li>
  </ul>
       
</div> 

<!--/tab: Protect Tags/-->

<div id="protect_c">

  <h3>The Protect Tags</h3>
  <p>These tags are block smarty tags, and can be used several times on the page in pairs, i.e: an opening tag and a closing tag. The opening tag accepts only one parameter, the <strong>protected_msg</strong> which overrides the default one if set. This is a per occurrence tag, meaning that if it is set on the <em><strong>default</strong></em> or  <em><strong>set</strong></em> actions it is persistent, but if set on a <strong>{protect}</strong> tag it affects only the tag where it is used and doesn't persist to the next occurrence.</p>

  <h4><strong>Content wrapping tags example.</strong></h4>
  <p>Use one of the following tags:
    <pre>
    
<strong>{protect}</strong><em>whatever content you want protected.</em><strong>{/protect}</strong> 
<strong>{protect protected_msg='</strong><em>well, you really should be logged in if you what to see the content</em><strong>'}</strong><em>whatever content you want protected.</em><strong>{/protect}</strong>
    </pre>
  </p>
  <div class="warning" style="display:block;">
      <p><strong>Note:</strong> without initializing the plugin, you <strong>cannot</strong> use the <strong>{protect}{/protect}</strong> tags.</p>
  </div> 
  <div class="warning" style="display:block;">
    <p><strong>Note:</strong> you <strong>cannot</strong> use <strong>{protect}{/protect}</strong> on the same content block where you initialized the plugin. The initialization won't be in effect yet and Smarty will not recognize the new tags at that point.</p>
</div>  
  <h5>Protect tag accepts only one parameter:</h5>
  <ul>
    <li><em>(optional) <strong>protected_msg</strong></em> - a message to replace the protected content (defaults to whatever was set on a previous persistent tag call);</li>
  </ul>
       
</div> 


<!--/tab: Snippets/-->

<div id="snippets_c">

  <h3>Code Snippets</h3>
  <h4>The Quick Way</h4> 
  
  <p>Use this anywhere on a template or a page content block:
    <pre>
    
{* This snippet of code can be used standalone; no need for an initialization tag *}    
<strong>{page_protect action='form' passwords='pass1'}
{if $pp_logged_in}</strong>
  The allowed content....
<strong>{else}</strong>
  Not Logged in!
<strong>{/if}</strong>
    </pre>
  </p>
  
  <h4>Initialization</h4>
  
  <p>Use this anywhere on the top of a template or on a page content block, preferably on the field <strong>Smarty data or logic that is specific to this page</strong>:
    <pre>
    
{* This tag initializes the plugin so that you can use the {protect}{/protect} tags *}    
<strong>{page_protect}</strong>
    </pre>
  </p>  
  <p>A more complex example. Use this anywhere on the top of a template or on a page content block, preferably on the field <strong>Smarty data or logic that is specific to this page</strong>:
    <pre>

{* Use smarty syntax to create an array *}
<strong>{$passwords=['pass1','pass2','pass3']}</strong>

{* Use smarty syntax to set a few messages into variables we can use to fill in the parameters *}
<strong>{$welcome_msg='Please enter your password'}
{$protected_msg='You really need to login to have access to this page.'}
{$error_msg='The password you entered is not correct. Please check carefully if the password is correct and try again.'}</strong>
  
{****************************************************************************** 
This tag initializes the plugin so that you can use the {protect}{/protect} tags.
Note the use of already set Smarty vars to pass values to some parameters. 
*******************************************************************************}    
<strong>{page_protect passwords=$passwords login_alias='home' logout_alias='bye-bye-page-alias' timeout=10 cookie_name='a_cookie_name' welcome_msg=$welcome_msg protected_msg=$protected_msg error_msg=$error_msg}</strong>
    </pre>
  </p>
<h4>Using The Set Action</h4>
  <p>
    <pre>
    
{* Using the 'set' action to spread parameters through multiple calls *}
{* redirect Home on logout *}
<strong>{page_protect action='set' logout_alias='home'}</strong>
{* set the time before a login expires *} 
<strong>{page_protect action='set' timeout=10}</strong>
{* set the message to show in case the authentication fails *}
<strong>{page_protect action='set' error_msg='Oops! Wrong pass, mate! Check your notes...'}</strong>
{* set the name of the login the check variable *}
<strong>{page_protect action='set' assign='logged_in'}</strong>
{* setting all the above in a single tag call could lead to errors *}

{* now we can check for our own named variable *}
<strong>{if $logged_in}</strong>
  The allowed content...
<strong>{else}</strong>
  Not Logged in!
<strong>{/if}</strong>

    </pre>
  </p>
  
<h4>Using The Form Action</h4>

  <p> An example with all the parameters you can use to customize the form:
    <pre>
    
{* Do you really need all this?!!! A complete form call which doesn't output nothing because the assign_output parameter is being used *}
<strong>{page_protect action='form' login_btn='Let Me In!' logout_btn='Bye Bye!' form_class='css_form' form_id='css_my_form' in_pass_id='css_passwrdid' in_pass_class='css_passwrd_class' button_id='css_btn_id' button_class='css_btn_class' assign_output='login_form'}</strong>
{* by using assign_output='login_form' you can use {$login_form} several times on the template *}
<strong>{$login_form}</strong>
    </pre>
  </p>
  
<h4>Using The Protect Block Tags</h4>

  <p>Use one of the following tags:
    <pre>
    
<strong>{protect}</strong><em>whatever content you want protected.</em><strong>{/protect}</strong> 
<strong>{protect protected_msg='</strong><em>well, you really should be logged in if you what to see the content</em><strong>'}</strong><em>whatever content you want protected.</em><strong>{/protect}</strong>
    </pre>
  </p>
  
  <h4>Passwords</h4>
  <p>The passwords parameter can accept a range of values, from a single password, a comma separated list of passwords, or an array of values:</p>

  <p>
    <pre>
      
<strong>{page_protect passwords='pass1'}</strong>
    </pre>
  </p>
  
  <p>
    <pre>
    
<strong>{page_protect passwords='pass1,'pass2','pass3'}</strong>
    </pre>
  </p>
  <p>
    <pre>
    
{* Use smarty syntax to create an array *}
<strong>{$passwords=['pass1','pass2','pass3']}</strong>

{* or *}
<strong>{$passwords[]='pass1'}
{$passwords[]='pass2'}
{$passwords[]='pass3'}</strong>

{* and use it as the value for the parameter passwords *}
<strong>{page_protect passwords=$passwords}</strong>

{* or *}
<strong>{page_protect action='set' passwords=$passwords}</strong>
    </pre>
  </p>
       
</div> 


<!--/tab: Notes/-->

<div id="notes_c">

  <h3>Notes</h3>
  
  <h4>Tags Order</h4>  

  <p>The order by which the tags are called is extremely important: the default action (parameter <strong>action='default'</strong> which can be omitted) , when used, <strong>cannot</strong> be set on the same content block as the <strong>{protect}{/protect}</strong> tags, and should be called on the topmost block of the template. This tag registers a smarty block plugin <strong>{protect}</strong> which won't be recognized before being parsed once (and registered) by the Smarty engine. After this first call, all other calls don't have a specific order other than, of course, the opening and closing <strong>{protect}{/protect}</strong> tags.</p>
  
  <h4>Persistent Parameters</h4>  
  <p>Some of the parameters used by this plugin are persistent for the duration of the request, that is to say, through all of the current rendered page. This means that you can set them once, knowing they will be used later on the same request on subsequent calls to the plugin. That also means that they can be changed on subsequent calls, if needed.</p>
  
  <h4>Passwords</h4>    

  <div class="warning" style="display:block;">
  <p><strong>Note:</strong> Unless you use an array to set the passwords, avoid the use of commas (<strong>,</strong>) and of vertical slashes (<strong>|</strong>) as password symbols as these are reserved to internal use and will unavoidably lead to passwords not being recognized by the plugin.</p>
  </div>

  <h3>Additional Notes</h3>

  <p>If the <strong>timeout</strong> parameter is used, this plugin will generate a frontend cookie. By using this parameter you may be violating some countries laws of user privacy. Please make sure you provide a fair warning on the front pages if needed, or avoid using the <strong>timeout</strong> parameter, thus disabling the use of cookies. The only drawback of not using cookies is that the authentication only lasts for a single page request.</p>
  <p>This plugin needs PHP version 5.3.</p>
 
</div> 
EOT;

  echo $txt;
}

function smarty_cms_about_function_content_protect()
{
  $txt = <<<'EOT'
<h3>About</h3>
<p><strong>Version: 1.2</strong></p>
<p>Author: Jo Morg (Fernando Morgado)</p>
<p>Another plugin made out of need. Enjoy!</p>
<h3>History</h3>
<ul>
    <li><strong>Version 1.2.1</strong>:
      <ul>        
        <li>Minor fix to allow it to work with CMSMS 2.x</li>
      </ul>
    </li>
    <li><strong>Version 1.2</strong>:
      <ul>        
        <li>Added a <strong>Quick Setup</strong>;</li>
        <li>Added an assignable <strong>Smarty</strong> variable, by default <strong>$pp_logged_in</strong>:
          <ul>
            <li>allows to use <strong>Smarty</strong> logic to check if current visitor is logged in;</li>
            <li>makes the initialization optional;</li>
          </ul>
        </li>
        <li>Updated help and corrected a few typos;</li>
      </ul>
    </li>
    <li><strong>Version 1.1</strong>:
      <ul>
        <li>Added login_alias parameter and ability to redirect on authenticated login;</li>
        <li>Updated help and corrected a few typos;</li>
      </ul>
    </li>
    <li><strong>Version 1.0</strong>:
      <ul>
        <li>Minor fixes;</li>
      </ul>
    </li>
    <li><strong>Version 1.0.RC.2</strong>:
      <ul>
        <li>Cleaned a few redundant lines of code;</li>
        <li>Added the <em><strong>set</strong></em> action to allow to distribute the numerous parameters through different tags (as long as they are on the same page);</li>
        <li>Made a few additions to the plugin's help;</li>
      </ul>
    </li>
    <li> <strong>Version 1.0.RC.1</strong>:
      <ul>
          <li>Added a few parameters to further style the form;</li>
      </ul>
    </li>
    <li> <strong>Version 1.0.RC</strong>: 
      <ul>
          <li>Initial version (for CMSMS 1.11.x). A release candidate;</li>
      </ul>
    </li>
</ul>
<h3>To Do List</h3>
<ul>
  <li>Allow IP based blacklists or whitelists;</li>
    <li>Allow pairs of username and password;</li>
    <li><strike> Add a few parameters to further style the form</strike>; &#10004;</li>
</ul>
EOT;

  echo $txt;
}
?>