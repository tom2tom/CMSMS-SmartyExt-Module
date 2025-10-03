<?php
# Module: SmartyExt (c) 2020-2025 CMS Made Simple Foundation Inc.
# An add-on module for CMS Made Simple to provide useful functions
#  and commonly used GUI capabilities to other modules.
# A fork of module: CGSimpleSmarty (c) 2008-2014 Robert Campbell
#
# CMSMS - CMS Made Simple is (c) 2006 - 2021 CMS Made Simple Foundation Inc.
# CMSMS - CMS Made Simple is (c) 2004 Ted Kulp (wishy@cmsmadesimple.org)
# Visit the CMSMS homepage at: https://www.cmsmadesimple.org
#
# This module is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This module is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of that License along with CMSMS
# or if not, read the License online at
# https://www.gnu.org/licenses/old-licenses/gpl-2.0.html.

class SmartyExt extends CMSModule
{
    public function GetAdminDescription() { return $this->Lang('moddescription'); }
    public function GetAuthor() { return 'JoMorg'; }
    public function GetAuthorEmail() { return 'jomorg@cmsmadesimple.org'; }
    public function GetChangeLog() { return file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'changelog.htm'); }
    public function GetFriendlyName() { return $this->Lang('friendlyname'); }
    public function GetName() { return 'SmartyExt'; }
    public function GetVersion() { return '1.4.0'; }
    public function InstallPostMessage() { return $this->Lang('postinstall'); }
    public function MinimumCMSVersion() { return '2.2.17'; } // want CMSMS\strftime
    public function UninstallPostMessage() { return $this->Lang('postuninstall'); }

    public function GetHelp()
    {
        $smarty = cmsms()->GetSmarty();
        $modname = $this->GetName();
        $dir = ''; // default language direction (for css selection)
        $langnow = CmsNlsOperations::get_current_language();
        if ($langnow) {
            $info = CmsNlsOperations::get_language_info($langnow);
            if ($info->direction() == 'rtl') {
                $dir = '-rtl';
            }
        }
        $tpl = $smarty->createTemplate("module_file_tpl:$modname;help.tpl", null, null, null);
        $tpl->assign('dir', $dir)
          ->assign('baseurl', $this->GetModuleURLPath());
        return $tpl->fetch();
    }

    public function InitializeAdmin()
    {
        $this->InitializeFrontend();
    }

    public function InitializeFrontend()
    {
        spl_autoload_register([$this, 'autoloader']);
        $smarty = cmsms()->GetSmarty();
        $smarty->registerClass('smx', 'SmartyExt\smx');
        $plugins_dir = cms_join_path(__DIR__, 'lib', 'plugins');
        $smarty->addPluginsDir($plugins_dir);
    }

    private function autoloader($classname)
    {
        //$classname will not contain the leading backslash of a fully-qualified identifier
        if ($classname[0] == 'S' && strncmp($classname, 'SmartyExt', 9) == 0) {
            //no support here for a class in a nested folder
            $parts = explode('\\', $classname);
            $fn = cms_join_path(__DIR__, 'lib', 'class.' . end($parts) . '.php');
            if (file_exists($fn)) {
                include_once $fn;
            }
        }
    }
}
