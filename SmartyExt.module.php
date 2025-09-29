<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: SmartyExt (c) 2020-2021 by CMS Made Simple Foundation
#  An add-on module for CMS Made Simple to provide useful functions
#  and commonly used gui capabilities to other modules.
#-------------------------------------------------------------------------
# A fork of:
#
# Module: CGSimpleSmarty (c) 2008-2014 by Robert Campbell
#         (calguy1000@cmsmadesimple.org)
#
#-------------------------------------------------------------------------
#
# CMSMS - CMS Made Simple is (c) 2006 - 2021 by CMS Made Simple Foundation
# CMSMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# Visit the CMSMS Homepage at: http://www.cmsmadesimple.org
#
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
#END_LICENSE
use \SmartyExt\utils;

class SmartyExt extends CMSModule
{
  #---------------------
  # Internal autoloader
  #---------------------
  
  private function _autoloader($classname)
  {
    $parts = explode('\\', $classname);
    $classname = end($parts);
    
    $fn = cms_join_path(
      $this->GetModulePath(),
      'lib',
      'class.' . $classname . '.php'
    );
    
    if(file_exists($fn)) { require_once($fn); }
  }
  
  private function _includes()
  {
    $pat = cms_join_path(
      $this->GetModulePath(),
      'lib',
      'function.*.php'
    );
  
    foreach(glob($pat) as $filename)
    {
      include_once $filename;
    }
  }
  
  public function __construct()
  {
    global $CMS_INSTALL_PAGE, $CMS_PHAR_INSTALL;
    
    if( isset($CMS_INSTALL_PAGE) || isset($CMS_PHAR_INSTALL) ){return;}
  
    static $_setup;
  
    if($_setup){return;}
  
    $_setup = 1;
  
    $this->_includes();
    spl_autoload_register([$this, '_autoloader']);
    
    # create new smarty functions to register the plugins
    $smarty_functions = new \SmartyExt\smarty_functions();
    $smarty = cmsms()->GetSmarty();
    # register the smx class
    $smarty->registerClass('smx', 'smx');
    # add our own plugin dir for some more complex stuff
    $plugins_dir = cms_join_path( $this->GetModulePath(), 'lib', 'internal_plugins' );
    
    $smarty->addPluginsDir($plugins_dir);
    
    parent::__construct();
  }
  
  function GetName(){ return 'SmartyExt'; }
  
  function GetFriendlyName(){ return $this->Lang('friendlyname'); }
  
  function GetVersion(){ return '1.3.0'; }
  
  function MinimumCMSVersion(){ return '2.1.4'; }
  
  public function GetHelp()
  {
    $smarty = cmsms()->GetSmarty();
    $smarty->assign('mod', $this);
    $smarty->assign('parent_name', $this->GetName());
    return $this->ProcessTemplate('help.tpl');
  }
  
  function GetAuthor(){ return 'Jo Morg'; }
  
  function GetAuthorEmail(){ return 'jomorg@cmsmadesimple.org'; }
  
  function GetChangeLog()
  {
    $smarty = cmsms()->GetSmarty();
    $smarty->assign('mod', $this);
    return $this->ProcessTemplate('changelog.tpl');
  }
  
  function IsPluginModule(){ return FALSE; }
  
  function GetDependencies() { return ['CMSMSExt' => '1.4.0']; }
  
  function GetAdminDescription(){ return $this->Lang('moddescription'); }
  
  function HasAdmin(){ return FALSE; }
  
  function HandlesEvents(){ return FALSE; }
  
  function InstallPostMessage(){ return $this->Lang('postinstall'); }
  
  function UninstallPostMessage(){ return $this->Lang('postuninstall'); }
}
