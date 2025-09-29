<?php

namespace SmartyExt;


class smarty_functions
{
  public const C_UNSET = '_unset_';
  
  public function __construct()
  {
    $smarty = \cmsms()->GetSmarty();
    
    $smarty->register_function('mod_action_link', [$this, 'mod_action_link']);
    $smarty->register_function('mod_action_url', [$this, 'module_action_url']);
    $smarty->register_function('xt_repeat', [$this, 'smarty_function_repeat']);
    $smarty->register_function('sess_put', [$this, 'smarty_function_session_put']);
    $smarty->register_function('sess_erase', [$this, 'smarty_function_session_erase']);
    $smarty->register_function('xt_anchor_link', [$this, 'plugin_anchorlink']);
    $smarty->register_function('xt_setvar', [$this, 'plugin_setvar']);
    $smarty->register_function('xt_unsetvar', [$this, 'plugin_unsetvar']);
    $smarty->register_function('xt_getvar', [$this, 'plugin_getvar']);
    $smarty->register_function('content_fetch', [$this, 'smarty_function_content_fetch']);
    $smarty->register_function('files_list', [$this, 'smarty_function_files_list']);
    $smarty->register_function('trigger_404', [$this, 'trigger_404']);
    $smarty->register_function('trigger_403', [$this, 'trigger_403']);
  }
  
  /**
   * Create a link to an anchor further down the page.
   *
   * @param $params
   * @param $smarty
   *
   * @return string|null
   */
  function plugin_anchorlink($params, &$smarty)
  {
    $name    = \get_parameter_value($params, 'n');
    $name    = \get_parameter_value($params, 'name', $name);
    $assign  = \trim(\get_parameter_value($params, 'assign'));
    $urlonly = \get_parameter_value($params, 'u');
    $urlonly = \cms_to_bool(\get_parameter_value($params, 'urlonly', $urlonly));
    $text    = \get_parameter_value($params, 'text', $name);
    
    unset($params['name'], $params['n'], $params['assign'], $params['u'], $params['urlonly'], $params['text']);
    
    // start the work
    $url = NULL;
    $out = NULL;
    
    if($name)
    {
      $url = \smx::anchor_url($name);
    }
    
    if($urlonly)
    {
      $out = $url;
    }
    else
    {
      // build a link with all the leftover params (don't filter them, there are lots of valid params for a link).
      $tpl = ' %s="%s"';
      $out = '<a';
      $out .= \sprintf($tpl, 'href', $url);
      
      foreach($params as $key => $val)
      {
        $out .= " $key=\"$val\"";
      }
      
      $out .= '>' . $text . '</a>';
    }
    
    if($assign)
    {
      $smarty->assign($assign, $out);
    }
    else
    {
      return $out;
    }
    
    return '';
  }
  
  function plugin_setvar($params, &$samrty)
  {
    foreach($params as $key => $val)
    {
      $key = \trim($key);
      if(!$key)
      {
        continue;
      }
      
      if(self::C_UNSET === $val)
      {
        \xt_tmpdata::erase($key);
      }
      else
      {
        \xt_tmpdata::set($key, $val);
      }
    }
  }
  
  function plugin_unsetvar($params, &$smarty)
  {
    foreach($params as $key => $val)
    {
      $key = \trim($key);
      if(!$key)
      {
        continue;
      }
      
      if('unset' === $key)
      {
        if($val)
        {
          $list = \explode(',', $val);
          
          foreach($list as $one)
          {
            $one = \trim($one);
            if(!$one)
            {
              continue;
            }
            \xt_tmpdata::erase($one);
          }
        }
      }
      else
      {
        \xt_tmpdata::erase($key);
      }
    }
  }
  
  function plugin_getvar($params, &$smarty)
  {
    $key  = \xt_param::get_string($params, 'v');
    $key  = \xt_param::get_string($params, 'var', $key);
    $dflt = $params['dflt'] ?? NULL;
    
    $val = NULL;
    
    if($key)
    {
      $val = \xt_tmpdata::get($key, $dflt);
    }
    
    $assign = \xt_param::get_string($params, 'assign');
    $scope  = \strtolower(\xt_param::get_string($params, 'scope', 'local'));
    
    if($assign)
    {
      $smarty->assign($assign, $val);
      if('global' === $scope)
      {
        $smarty->assignGlobal($assign, $val);
      }
    }
    
    return $val;
  }
  
  /**
   * A simple function to generate a url to a module action
   *
   * @param $params
   * @param $tpl
   *
   * @return array|string|string[]
   */
  function module_action_url($params, $tpl)
  {
    $params['urlonly'] = 1;
    $assign            = isset($params['assign']) ? \trim($params['assign']) : '';
    unset($params['imageonly'], $params['text'], $params['title'], $params['image'], $params['class'], $params['assign']);
    $out = $this->mod_action_link($params, $tpl);
    
    if($assign)
    {
      $tpl->assign($assign, $out);
      
      return '';
    }
    
    return $out;
  }
  
  /**
   * A simple function to generate a link to a module action
   *
   * @param $params
   * @param $smarty
   *
   * @return array|string|string[]
   */
  function mod_action_link($params, $smarty)
  {
    $gCms   = cmsms();
    $inline = FALSE;
    
    $module = $smarty->get_template_vars('module');
    
    if(!$module)
    {
      $module = $smarty->get_template_vars('actionmodule');
    }
    
    $module = get_parameter_value($params, 'module', $module);
    
    if(!$module)
    {
      $module = $smarty->getTemplateVars('module');
    }
    if(!$module)
    {
      $module = $smarty->getTemplateVars('actionmodule');
    }
    if(!$module)
    {
      $module = $smarty->getTemplateVars('_module');
    }
    
    $mid = $smarty->getTemplateVars('actionid');
    
    if(!$mid)
    {
      $mid = 'm1_';
      
      if($gCms->is_frontend_request())
      {
        $mid = 'cntnt01';
      }
    }
    
    if(!$module)
    {
      return '';
    }
    
    unset($params['module']);
    
    $obj = \cms_utils::get_module($module);
    
    if(!\is_object($obj))
    {
      return '';
    }
    
    $text = $module;
    if(isset($params['text']))
    {
      $text = \trim($params['text']);
      unset($params['text']);
    }
    
    $title = '';
    if(isset($params['title']))
    {
      $title = \trim($params['title']);
      unset($params['title']);
    }
    
    $confmessage = '';
    if(isset($params['confmessage']))
    {
      $confmessage = \trim($params['confmessage']);
      unset($params['confmessage']);
    }
    
    $image = '';
    if(isset($params['image']))
    {
      $image = \trim($params['image']);
      unset($params['image']);
    }
    
    $class = 'systemicon';
    if(isset($params['class']))
    {
      $class = \trim($params['class']);
      unset($params['class']);
    }
    
    $action = 'default';
    if(isset($params['action']))
    {
      $action = $params['action'];
      unset($params['action']);
    }
    
    if(isset($params['id']))
    {
      $mid    = $params['id'];
      $inline = TRUE;
      unset($params['id']);
    }
    
    $imageonly = FALSE;
    if(isset($params['imageonly']))
    {
      $imageonly = TRUE;
      unset($params['imageonly']);
    }
    
    $pageid = \cms_utils::get_current_pageid();
    if(isset($params['page']))
    {
      // convert the page alias to an id
      $manager = $gCms->GetHierarchyManager();
      $node    = $manager->sureGetNodeByAlias($params['page']);
      if(isset($node))
      {
        $content = $node->GetContent();
        if(isset($content))
        {
          $pageid = $content->Id();
        }
      }
      else
      {
        $node = $manager->sureGetNodeById($params['page']);
        if(isset($node))
        {
          $pageid = $params['page'];
        }
      }
      unset($params['page']);
    }
    
    $urlonly = !empty($params['urlonly']);
    if($urlonly)
    {
      $urlonly = TRUE;
      unset($params['urlonly']);
    }
    
    $jsfriendly = !empty($params['jsfriendly']);
    if($jsfriendly)
    {
      $jsfriendly = TRUE;
      $urlonly    = TRUE;
      unset($params['jsfriendly']);
    }
    
    $forjs = !empty($params['forjs']);
    if($forjs)
    {
      $jsfriendly = TRUE;
      $urlonly    = TRUE;
      unset($params['forjs']);
    }
    
    $forajax = !empty($params['forajax']);
    
    if($forajax)
    {
      $jsfriendly = TRUE;
      $urlonly    = TRUE;
      $forajax    = TRUE;
      unset($params['forajax'], $params['for_ajax']);
    }
    
    $assign = '';
    if(isset($params['assign']))
    {
      $assign = \trim($params['assign']);
      unset($params['assign']);
    }
    
    $addtext = '';
    if($title)
    {
      $addtext = 'title="' . $title . '"';
    }
    
    if(!empty($image) && \method_exists($obj, 'CreateImageLink') && FALSE == $urlonly)
    {
      $output = $obj->CreateImageLink(
        $mid, $action, $pageid, $text, $image, $params, $class, $confmessage, $imageonly, FALSE, $addtext
      );
    }
    else
    {
      $output = $obj->CreateLink($mid, $action, $pageid, $text, $params, $confmessage, $urlonly, $inline, $addtext);
      if($urlonly && $jsfriendly)
      {
        $output = \str_replace('amp;', '', $output);
      }
      if($forajax)
      {
        if(FALSE !== \strpos($output, '?'))
        {
          $output .= '?showtemplate=false';
        }
        else
        {
          $output .= '&showtemplate=false';
        }
      }
    }
    
    // all done
    if(!empty($assign))
    {
      $smarty->assign($assign, $output);
      
      return '';
    }
    
    return $output;
  }
  
  /**
   * A simple function to repeat a string N times
   *
   * @param $params
   * @param $smarty
   *
   * @return string
   */
  function smarty_function_repeat($params, $smarty)
  {
    $num = 1;
    if(!isset($params['text']))
    {
      return '';
    }
    
    $text = $params['text'];
    if(isset($params['count']))
    {
      $num = (int)$params['count'];
    }
    
    $out = '';
    for($i = 0; $i < $num; $i++)
    {
      $out .= $text;
    }
    
    if(isset($params['assign']))
    {
      $gCms   = cmsms();
      $smarty = $gCms->GetSmarty();
      $smarty->assign(\trim($params['assign']), $out);
      
      return '';
    }
    
    return $out;
  }
  
  function smarty_function_session_put($params, $smarty)
  {
    if(isset($params['var']) && isset($params['value']))
    {
      $var = \trim($params['var']);
      if(\is_string($var))
      {
        $_SESSION[$var] = $params['value'];
      }
    }
  }
  
  function smarty_function_session_erase($params, $smarty)
  {
    if(isset($params['var']))
    {
      $var = \trim($params['var']);
      if($var && isset($_SESSION[$var]))
      {
        unset($_SESSION[$var]);
      }
    }
  }
  
  /**
   * @param $params
   * @param $smarty
   *
   * @return string
   * @throws \Exception
   */
  function smarty_function_content_fetch($params, $smarty)
  {
    #  Check if output should be assigned to a specific variable name
    if(isset($params['assign']))
    {
      $assign = \trim($params['assign']);
    }
    else
    {
      $assign = 'content_fetch';
    }
    
    
    # Check if parent page data should be included in  results entries
    $parents = isset($params['parents']) && $params['parents'];
    
    # Check if user data should be looked up for each entry
    $users = isset($params['users']) && $params['users'];
    
    #  Set locale if required (e.g. dates/times). Locale is reset to default after script completed
    if(isset($params['cdlocale']) && $params['cdlocale'])
    {
      \setlocale(\LC_TIME, $params['cdlocale']);
    }
    else
    {
      \setlocale(\LC_TIME, '');
    }
    
    # Check for which content block to pick,
    # if not set, pick default "content_en"
    $block = $params['block_name'] ?? 'content_en';
    
    $sql_content_query_having = " HAVING prop_name='" . $block . "' ";
    
    # Set a date format for your Content entries
    $dateformat = $params['dateformat'] ?? '%A, %e %B %Y';
    
    #  Check if start id is set,
    # if so, tag will ony return pages
    # that have this id or are located below
    if(isset($params['start_id']))
    {
      $st = $params['start_id'];
      
      $sql_content_query_limit_sitearea = " AND (
				(id_hierarchy LIKE ('$st')) OR
				(id_hierarchy LIKE ('$st.%')) OR
				(id_hierarchy LIKE ('%.$st.%')) OR
				(id_hierarchy LIKE ('%.$st'))
			)";
      
      $start_id = (integer)$params['start_id'];
    }
    else
    {
      $sql_content_query_limit_sitearea = '';
      $start_id                         = -1;
    }
    
    # Check if parent page data
    # should be included in results entries
    if(isset($params['depth']))
    {
      $depth = \explode(',', $params['depth']);
      
      /* Define the starting hierarchy */
      if(-1 === $depth[0])
      {
        $start_depth = utils::get_current_level($start_id);
      }
      else if(0 === (integer)$depth[0])
      {
        $start_depth = 1;
      }
      else
      {
        $start_depth = (integer)$depth[0];
      }
      
      # the REGEXP snippets used
      $snip1 = "'^[0-9]{1,10}";
      $snip2 = "\.[0-9]{1,10}";
      
      # Create Query String for first hierarchy level
      if($start_depth >= 1)
      {
        $buffer1 = $snip1;
        for($i = 1; $i <= $start_depth - 1; $i++)
        {
          $buffer1 .= '' . $snip2;
        }
      }
      
      $sql_content_query_limit_hierarchy = ' AND (id_hierarchy REGEXP ' . $buffer1 . "$' ";
      
      # Create Query String for remaining hierarchy levels
      $max_depth = $start_depth + (integer)$depth[1];
      
      for($i = $start_depth; $i <= $max_depth - 1; $i++)
      {
        $buffer2  = $buffer1;
        $distance = $i - $start_depth;
        
        $buffer2 .= \str_repeat('' . $snip2, $distance + 1);
        
        $sql_content_query_limit_hierarchy .= ' OR id_hierarchy REGEXP ' . $buffer2 . "$' ";
      }
      $sql_content_query_limit_hierarchy .= ')';
    }
    else
    {
      $sql_content_query_limit_hierarchy = '';
    }
    
    # Check if first sorting item-sort-order is set
    # otherwise set to default , which is upwards
    $first_sort_order = $params['first_sort_order'] ?? 'up';
    
    # Check if second sorting item-sort-order is set
    # otherwise set to default , which is up
    $second_sort_order = $params['second_sort_order'] ?? 'up';
    
    #  Check for first sort parameter,
    # if not set, first sort parameter will be the hierarchy date of the page
    if(isset($params['first_sort']))
    {
      $sql_content_query_first_sort = utils::sql_sort_param($params['first_sort'], 0, $first_sort_order);
    }
    else
    {
      $sql_content_query_first_sort = utils::sql_sort_param('hierarchy', 0, 'up');
    }
    
    # Check for second sort parameter,
    # if not set, second sort parameter will be the creation date of the page
    if(isset($params['second_sort']))
    {
      $sql_content_query_second_sort = utils::sql_sort_param($params['second_sort'], 2, $second_sort_order);
    }
    else
    {
      $sql_content_query_second_sort = '';
    }
    
    # Check for parameter exclude,
    # if it contains ID(s) seperated by comma,
    # it will create an array of IDs we can exclude later on
    $sql_content_query_excludes = '';
    
    if(isset($params['exclude']))
    {
      $exclude_ids = \explode(',', $params['exclude']);
      for($i = 0; $i <= \count($exclude_ids) - 1; $i++)
      {
        $sql_content_query_excludes .= 'AND ' . cms_db_prefix() . 'content.content_id != ' . $exclude_ids[$i] . ' ';
      }
    }
    else
    {
      $exclude_ids[0] = -1;
    }
    
    # Limit the query results to a singe content_id
    # by adding a condition to the query statement
    # if not set, do not add anything to the query string
    if(isset($params['this_only']))
    {
      $sql_content_query_this_only = 'AND ' . cms_db_prefix() . 'content.content_id = ' . $params['this_only'];
    }
    else
    {
      $sql_content_query_this_only = '';
    }
    
    # Check for parameter these_only, if it contains ID(s) seperated by comma,
    # it will create an array of IDs we can include exclusively
    $sql_content_filter = '';
    if(isset($params['these_only']))
    {
      $include_ids                = \explode(',', $params['these_only']);
      $sql_content_query_includes = '(';
      for($i = 0; $i <= \count($include_ids) - 1; $i++)
      {
        $sql_content_filter .= 'OR ' . cms_db_prefix() . 'content.content_id != ' . $include_ids[$i] . '  ';
      }
      $sql_content_filter .= ')';
    }
    else
    {
      $include_ids[0] = -1;
    }
    
    # If set to compile, smarty tags in the content will be compiled
    #set to false per default
    # !WARNING! MAY CAUSE RECURSION AND ULTIMATELY OUT OF MEMORY ERROR
    $c_smarty_modes_list = ['neutral', 'compile', 'strip'];
    $c_smarty            = $params['do_smarty'] ?? 'neutral';
    if(!\in_array($c_smarty, $c_smarty_modes_list))
    {
      $c_smarty = 'neutral';
    }
    
    # If set to strip, HTML tags in the content will be removed (inluding JS, CSS and HTML)
    
    if(isset($params['html']) && ('strip' === $params['html']))
    {
      $c_html = 'strip';
    }
    else
    {
      $c_html = 'neutral';
    }
    
    # Check and set how the "active" page flag is handled
    $f_active = $params['active'] ?? 'active';
    
    switch($f_active)
    {
      case 'force':
        $sql_content_query_active = ' ';
      break;
      case 'inactive':
        $sql_content_query_active = ' AND active = 0 ' . ' ';
      break;
      default:
        $sql_content_query_active = ' AND active = 1 ' . ' ';
      break;
    }
    
    # Check and set how the "shown_in_menu" or "show_in_menu" page flag is handled
    # "show_in_menu" is deprecated, so we only use one of the params,
    # priority to new "shown_in_menu"
    $f_shown_in_menu = $params['show_in_menu'] ?? 'show';
    $f_shown_in_menu = $params['shown_in_menu'] ?? $f_shown_in_menu;
    
    switch($f_shown_in_menu)
    {
      case 'force':
        $sql_content_query_show = ' ';
      break;
      case 'hidden':
        $sql_content_query_show = 'AND show_in_menu = 0 ' . ' ';
      break;
      default:
        $sql_content_query_show = ' AND show_in_menu = 1 ' . ' ';
      break;
    }
    
    # Set the first item to show,
    # if not set, it will begin with the first item shown
    if(isset($params['limit_start']))
    {
      $limit_start      = $params['limit_start'];
      $limit_start_orig = $params['limit_start'];
    }
    else
    {
      $limit_start      = 0;
      $limit_start_orig = 0;
    }
    
    # Set the number of items to show,
    # if no parameter is set, limit to 1000 results
    if(isset($params['limit_count']))
    {
      $limit_count      = $params['limit_count'];
      $limit_count_orig = $params['limit_count'];
    }
    else
    {
      $limit_count      = 1000;
      $limit_count_orig = 1000;
    }
    
    # if parameter extension is used,
    # explode prop_names and store them in an array
    if(isset($params['extensions']))
    {
      $extensions              = \explode(',', $params['extensions']);
      $count_extensions        = \count($extensions);
      $sql_content_query_props = '';
      
      for($i = 0; $i <= $count_extensions - 1; $i++)
      {
        $sql_content_query_props .= " OR prop_name='" . $extensions[$i] . "' " . ' ';
      }
      
      $limit_count *= ($count_extensions + 1);
      
      if($limit_start >= 1)
      {
        $limit_start = ($limit_start * ($count_extensions + 1)) - 1;
      }
      
      $extension = TRUE;
    }
    else
    {
      $sql_content_query_props = '';
      $extension               = FALSE;
    }
    
    # Check how a stated prefix should be handled
    $sql_prefix_filter = '';
    $prefix_mode       = $params['prefix_mode'] ?? 'neutral';
    
    # Check what prefixes should be handled
    if(isset($params['prefix']))
    {
      $prefixes = \explode(',', $params['prefix']);
      $count    = \count($prefixes);
      if('force' === $prefix_mode)
      {
        $sql_prefix_filter = " AND content_alias REGEXP '^";
      }
      if('hide' === $prefix_mode)
      {
        $sql_prefix_filter = " AND content_alias NOT REGEXP '^";
      }
      if('neutral' !== $prefix_mode)
      {
        if($count > 1)
        {
          $sql_prefix_filter .= '(';
        }
        for($i = 0; $i <= $count - 1; $i++)
        {
          $sql_prefix_filter .= '' . $prefixes[$i];
          
          if($i < $count - 1)
          {
            $sql_prefix_filter .= '|';
          }
        }
        
        if($count > 1)
        {
          $sql_prefix_filter .= ")' ";
        }
        else
        {
          $sql_prefix_filter .= "' ";
        }
      }
    }
    
    # Check if parameter 'page' specifies a page to display,
    # (only makes sense when used with limit_count, which is the page size)
    if(isset($params['page']) && ('' !== $params['page']))
    {
      $page_number = $params['page'];
      # There is no negative page or a page numbered zero
      if($page_number <= 0)
      {
        $page_number = 1;
      }
      
      if(1 === $page_number)
      {
        # Page is one, we do not need to change anything
      }
      else if(2 === $page_number)
      {
        // this is the second page,
        if(0 === $limit_start)
        {
          $limit_start = $limit_count;
        }
        else
        {
          $limit_start = $limit_start + $limit_count - 1;
        }
      }
      else if($page_number >= 3)
      {
        // and this any following page
        if(0 === $limit_start)
        {
          $limit_start = $limit_count * ($page_number - 1);
        }
        else
        {
          $limit_start = $limit_start + ($limit_count * ($page_number - 1)) - 1;
        }
      }
      
      $pager_info          = new \stdClass;
      $pager_info->current = $page_number;
      $pager_info->max     = utils::get_max_page(
        $block,
        $sql_content_query_limit_hierarchy,
        $sql_prefix_filter,
        $sql_content_filter,
        $limit_count_orig,
        $limit_start_orig,
        $exclude_ids,
        $sql_content_query_limit_sitearea,
        $sql_content_query_active,
        $sql_content_query_show
      );
      
      $pager_info->size = $limit_count_orig;
    }
    
    # If start and count are not set,
    # ignore the output limit and give back everything,
    # else: assemble mysql limit statement
    if(isset($params['limit_start']) || isset($params['limit_count']))
    {
      $sql_content_query_limit = ' LIMIT ' . $limit_start . ' , ' . $limit_count;
    }
    else
    {
      $sql_content_query_limit = '';
    }
    
    # Create FULLTEXT Search expression
    if((isset($params['filter'])) && ('' !== $params['filter']))
    {
      $these_ids_array = utils::get_matching_pages(
        $block,
        $sql_content_query_limit_hierarchy,
        $sql_prefix_filter,
        $params['filter'],
        $limit_count_orig,
        $limit_start_orig,
        $exclude_ids,
        $sql_content_query_limit_sitearea
      );
      
      $filter_count = \count($these_ids_array);
      
      if($filter_count > 0)
      {
        $sql_content_filter = 'AND (';
      }
      
      for($i = 0; $i <= $filter_count - 1; $i++)
      {
        if($i > 0)
        {
          $sql_content_filter .= 'OR ';
        }
        else
        {
          $sql_content_filter .= ' ';
        }
        
        $sql_content_filter .= '' . \cms_db_prefix() . "content_props.content_id = '" . $these_ids_array[$i] . "' ";
      }
      
      if($filter_count > 0)
      {
        $sql_content_filter .= ')  ';
      }
      
      if(0 === $filter_count)
      {
        $sql_content_filter = ' AND ' . \cms_db_prefix() . 'content_props.content_id = -1  ';
      }
      
    }
    else
    {
      $sql_content_filter = '';
    }
    
    $db = \cmsms()->GetDb();
    
    # Start query element for final content query
    $sql_content_query_start = '
		SELECT
			' . \cms_db_prefix() . 'content.content_id as content_id,
			content_name,
			menu_text,
			show_in_menu,
			' . \cms_db_prefix() . 'content.create_date,
			' . \cms_db_prefix() . 'content.modified_date,
			owner_id,
			id_hierarchy,
			parent_id,
			last_modified_by,
			active,
			prop_name,
			content,
			content_alias

		FROM
			' . \cms_db_prefix() . 'content, ' . \cms_db_prefix() . 'content_props

		WHERE
			' . \cms_db_prefix() . 'content.content_id = ' . \cms_db_prefix() . 'content_props.content_id

		';
    
    # Put all mysql query elements into one string
    
    $sql_the_full_query =
      $sql_content_query_start . '
		' . $sql_content_filter . '
		' . $sql_prefix_filter . '
		' . $sql_content_query_limit_sitearea . '
		' . $sql_content_query_limit_hierarchy . '
		' . $sql_content_query_excludes . '
		' . $sql_content_query_active . '
		' . $sql_content_query_show . '
		' . $sql_content_query_this_only . '
		' . $sql_content_query_having . '
		' . $sql_content_query_props . '
		' . $sql_content_query_first_sort . '
		' . $sql_content_query_second_sort . '
		' . $sql_content_query_limit;
    
    # Execute mysql Command
    $dbresult = $db->Execute($sql_the_full_query);
    
    if($db->ErrorNo())
    {
      throw new \RuntimeException('DB error: ' . $db->ErrorMsg());
    }
    
    //    if(!$dbresult)
    //    {
    //      echo 'DB error: ' . $db->ErrorMsg() . "<br/>";
    //    }
    
    
    # Store Database Results in Array of Classes
    
    # if we use extension checking... we need an extra array
    if($extension)
    {
      $content_props = [];
      $counter       = 0;
    }
    
    $content_dump = [];
    $parent_ids   = [];
    $user_ids     = [];
    $dump_count   = 0;
    
    while($dbresult && $dbqueryresultrow = $dbresult->FetchRow())
    {
      # Store the data of items with prop_name (=block-name) of parameter "block" into an array
      # this is our main content_dump item data
      if($dbqueryresultrow['prop_name'] == $block)
      {
        $dump_item           = new \stdClass;
        $dump_item->content  = new \stdClass;
        $dump_item->parents  = new \stdClass;
        $dump_item->created  = new \stdClass;
        $dump_item->modified = new \stdClass;
        $dump_item->item     = $dump_count;
        
        $dump_item->content->id     = $dbqueryresultrow['content_id'];
        $dump_item->content->alias  = $dbqueryresultrow['content_alias'];
        $dump_item->content->title  = $dbqueryresultrow['content_name'];
        $dump_item->content->menu   = $dbqueryresultrow['menu_text'];
        $dump_item->content->show   = $dbqueryresultrow['show_in_menu'];
        $dump_item->content->active = $dbqueryresultrow['active'];
        $dump_item->content->data   = $dbqueryresultrow['content'];
        
        # See if the ID of this pages parent is already known
        # as a page for which we need to lookup name and alias later on
        if($parents)
        {
          $parent_ids = utils::id_to_array($parent_ids, $dbqueryresultrow['parent_id']);
        }
        
        $dump_item->parents->id    = $dbqueryresultrow['parent_id'];
        $dump_item->parents->alias = '';
        $dump_item->parents->title = '';
        $dump_item->parents->menu  = '';
        
        if($users)
        {
          $user_ids = utils::id_to_array($user_ids, $dbqueryresultrow['owner_id']);
          $user_ids = utils::id_to_array($user_ids, $dbqueryresultrow['last_modified_by']);
        }
        
        # TODO - replace strftime asap
        $dump_item->created->by    = $dbqueryresultrow['owner_id'];
        $dump_item->created->date  = @\strftime($dateformat, @\strtotime($dbqueryresultrow['create_date']));
        $dump_item->modified->by   = $dbqueryresultrow['last_modified_by'];
        $dump_item->modified->date = @\strftime($dateformat, @\strtotime($dbqueryresultrow['modified_date']));
        $dump_item->extension      = 0;
        $content_dump[]            = $dump_item;
        ++$dump_count;
        
      }
      else if($extension && ('' != $dbqueryresultrow['content']))
      {
        // If the content block is not our primary content block, let's check if we should assign it to our buffer array, that stores all the other props
        $content_props[$counter]['content_id'] = $dbqueryresultrow['content_id'];
        $content_props[$counter]['content']    = $dbqueryresultrow['content'];
        $content_props[$counter]['prop_name']  = $dbqueryresultrow['prop_name'];
        ++$counter;
      }
    }
    
    # Get the extra data into the results (if needed)
    # TODO look at each result entry and compare it to the single data options (parents, extensions, users as well as strip options)
    
    $count_content_dump = \count($content_dump);
    
    if($parents)
    {
      $theparents       = utils::get_parent_data($parent_ids);
      $count_theparents = \count($theparents);
    }
    
    if($users)
    {
      $theusers       = utils::get_user_data($user_ids);
      $count_theusers = \count($theusers);
    }
    
    if($extension)
    {
      $count_content_props = \count($content_props);
    }
    
    for($i = 0; $i <= $count_content_dump - 1; $i++)
    {
      if($parents)
      {
        # Compare against parent IDs to expand parents data
        for($k1 = 0; $k1 <= $count_theparents - 1; $k1++)
        {
          if($content_dump[$i]->parents->id === $theparents[$k1]->id)
          {
            # we found the parent page of our content_dump item...
            $content_dump[$i]->parents->alias = $theparents[$k1]->alias;
            $content_dump[$i]->parents->title = $theparents[$k1]->title;
            break;
          }
        }
      }
      
      if($users)
      {
        # Compare against user_ids IDs to expand user data (extensions)
        for($k3 = 0; $k3 <= $count_theusers - 1; $k3++)
        {
          if($content_dump[$i]->created->by === $theusers[$k3]->id)
          {
            # we found the user who created the page
            $content_dump[$i]->created->by = $theusers[$k3];
          }
          
          if($content_dump[$i]->modified->by === $theusers[$k3]->id)
          {
            # we found the user who made the last modification
            $content_dump[$i]->modified->by = $theusers[$k3];
          }
        }
      }
      
      if($extension)
      {
        # Compare against content_prop IDs to expand entry data (extensions)
        for($k2 = 0; $k2 <= $count_content_props - 1; $k2++)
        {
          if($content_dump[$i]->content->id === $content_props[$k2]['content_id'])
          {
            # We found out that there is some extra content for this page, so let's flag the item
            $content_dump[$i]->extension = 1;
            # Now let's assign the data (remember: only when available)
            # to class names (we use the prop_names as class-names)
            $content_dump[$i]->extensions->$content_props[$k2]['prop_name']->data = $content_props[$k2]['content'];
            
            if('compile' === $c_smarty)
            {
              $_compiled = '';
              # Extension data fields should be compiled
              $smarty->_compile_source('temporary template', $content_dump[$i]->content->data, $_compiled);
              @\ob_start();
              $smarty->_eval('?>' . $_compiled);
              $content_dump[$i]->content->data = @\ob_get_contents();
              @\ob_end_clean();
            }
            else if('strip' === $c_smarty)
            {
              # Extension data field should be stripped
              $content_dump[$i]->content->data = utils::strip_out($content_dump[$i]->content->data, 'smarty');
            }
            //            else
            //            {
            //            }
            
            if('strip' === $c_html)
            {
              # HTML, JS and inline styles should be stripped
              $content_dump[$i]->extensions->$content_props[$k2]['prop_name']->data = utils::strip_out(
                $content_dump[$i]->extensions->$content_props[$k2]['prop_name']->data, 'html'
              );
            }
            
            $content_dump[$i]->extensions->$content_props[$k2]['prop_name']->length = \strlen(
              $content_dump[$i]->extensions->$content_props[$k2]['prop_name']->data
            );
          }
        }
      }
      
      if('compile' === $c_smarty)
      {
        # prevent calling page to be processed in smarty
        if($content_dump[$i]->content->id !== \cms_utils::get_current_pageid())
        {
          $smarty->_compile_source('temporary template', $content_dump[$i]->content->data, $_compiled);
          @\ob_start();
          $smarty->_eval('?>' . $_compiled);
          $content_dump[$i]->content->data = @\ob_get_contents();
          @\ob_end_clean();
        }
      }
      else if('strip' === $c_smarty)
      {
        $content_dump[$i]->content->data = utils::strip_out($content_dump[$i]->content->data, 'smarty');
      }
      
      if('strip' === $c_html)
      {
        $content_dump[$i]->content->data = utils::strip_out($content_dump[$i]->content->data, 'html');
      }
    }
    
    # Return Results
    
    $smarty->assign($assign, $content_dump);
    
    if(isset($pager_info))
    {
      $smarty->assign('pager_info', $pager_info);
    }
    
    \setlocale(\LC_TIME, '');
    
    return '';
  }
  
  /**
   * @param $params
   * @param $template
   *
   * @return void
   */
  public function smarty_function_files_list($params, $template)
  {
    # initialization  and defaults ########################################################################
    $file_ext           = '';
    $download           = FALSE;
    $q_var              = $params['query_var'] ?? 'fl'; # sets the query variable
    $folder             = isset($params['folder']) ? \rtrim($params['folder'], '/') : 'uploads';  # folder to read from
    $delimiter          = $params['delimiter'] ?? '_';
    $date               = !isset($params['date']) || (bool)$params['date'];
    $show_hidden        = isset($params['show_hidden']) || (bool)$params['show_hidden'];
    $count_downloads    = isset($params['count_downloads']) || (bool)$params['count_downloads'];
    $show_size          = !isset($params['show_size']) || (bool)$params['show_size'];
    $strip_extension    = isset($params['strip_extension']) && (bool)$params['strip_extension'];
    $dl_counter_db_dir  = $params['counter_db_dir'] ?? '';
    $dl_counter_db_fn   = $params['dl_counter_db_fn'] ?? '.counter_db';
    $file_extension     = $params['file_extension'] ?? '';
    //$size               = $params['size'] ?? 'Size';
    $dateformat         = $params['dateformat'] ?? 'Y-m-d H:i:s';
    $browse_sub_dirs    = !isset($params['browse_sub_dirs']) || (bool)$params['browse_sub_dirs'];
    $max_entries        = isset($params['max_entries']) ? (int)$params['max_entries'] : 0; # 0 = all
    $max_entries        = 'all' === $max_entries ? 0 : (int)$params['max_entries']; # 0 = all
    $thumbs_folder      = $params['thumbs_folder'] ?? 'thumbs';
    $obfuscate_download = !isset($params['obfuscate_download']) || (bool)$params['obfuscate_download']; # default = true
    $security_token     = !isset($params['security_token']) ||
                          (bool)$params['security_token']; # require a security token default = true
    $security_token_qv  = $params['security_token_qv'] ?? '_st';
    $root               = isset($params['root']) && (bool)$params['root'];
    $sort               = $params['sort'] ?? ''; # Check if user has specified sort order
    $recursive          = isset($params['recursive']) && (bool)$params['recursive'];
    
    $serve_fn           = '';
    $token              = '';
    $files_db           = [];
    
    # root param true makes obfuscate true
    $obfuscate_download = $obfuscate_download || $root;
    
    $get_params = $_GET ?? [];
    
    if($security_token && $obfuscate_download)
    {
      
      # do we have a session token? use it, otherwise create one but disallow downloading on this passage
      if( !isset($_SESSION['utils::st']) )
      {
        $st                    = \bin2hex(\openssl_random_pseudo_bytes(16));
        $_SESSION['utils::st'] = $st;
      }
      else
      {
        $st       = $_SESSION['utils::st'];
        $download = (isset($get_params[$security_token_qv]) && $st === $get_params[$security_token_qv]);
        $token    = $download ? $get_params[$security_token_qv] : $st;
      }
    }
  
    # set some parameters needed for the counter
    $prms = [
      'count_downloads'   => $count_downloads,
      'dl_counter_db_dir' => $dl_counter_db_dir,
      'dl_counter_db_fn'  => $dl_counter_db_fn,
      'folder'            => $folder
    ];
    
    
    if(!empty($get_params[$q_var]))
    {
      if($download)
      {
        $serve_fn = \urldecode($get_params[$q_var]);
        $t        = \explode('/', $serve_fn);
        $serve_fn = \array_pop($t);
        $sub_dir  = \implode('/', $t);
      }
      else
      {
        $sub_dir_1 = \urldecode($get_params[$q_var]);
        $sub_dir  = \str_replace('..', '', $sub_dir_1);
      }
    }
    else
    {
      $sub_dir = '';
    }
    
    $config    = cmsms()->GetConfig();
    $root_path = $root ? '' : $config['root_path'];
    
//    $file_names = [];
//    $file_types = [];
//    $file_dates = [];
//    $file_sizes = [];
  
    $files_info = [
      'file_names' => '',
      'file_types' => '',
      'file_dates' => '',
      'file_sizes' => '',
      'f_count' => 0
    ];
    
    # done initialization ##################################################################################
    
    $dir = cms_join_path($root_path, utils::uri_2_path(utils::join_uri($folder, $sub_dir)));
    
//    if( !$handle = \opendir($dir) )
//    {
//      $messages['error'][] = 'could not read ' . $get_params[$q_var] . '!';
//      $handle             = \opendir($folder);
//      $get_params[$q_var] = '';
//      $sub_dir             = '';
//    }
    
//    $f_count   = 0;
//    $max_count = 1;
//
//    while( $handle && ( FALSE !== ($entry = \readdir($handle) ) ) )
//    {
//      if('.' !== $entry && '..' !== $entry)
//      {
//        # prevent showing hidden files unless we want to
//        if( !$show_hidden && \startswith($entry, '.') ) { continue; }
//
//        $f_count++;
//        $full_name = cms_join_path($root_path, utils::uri_2_path(utils::join_uri($folder, $sub_dir, $entry)));
//
//        if($download && $serve_fn === $entry)
//        {
//          utils::do_download($full_name, \urlencode($entry), $prms);
//        }
//
//        #Insert filename, filetype, filetime and filesize into arrays
//        $file_names[] = $entry;
//        $file_types[] = \filetype($full_name);
//        $file_dates[] = \filemtime($full_name);
//        $file_sizes[] = \sprintf( '%u', \filesize($full_name) );
//      }
//    }
//
//    \closedir($handle);
//    $f_count--;
  
    // Get the files information
    try
    {
      $files_info = utils::getDirectoryFilesInfo($dir, $root_path, $folder, $sub_dir, $show_hidden, $download, $serve_fn, $prms, $recursive);
    }
    catch(\Exception $e)
    {
      $messages['error'][] = $e->getMessage();
      $get_params[$q_var] = '';
      $sub_dir             = '';
      
      try
      {
        $files_info = utils::getDirectoryFilesInfo($folder, $root_path, $folder, $sub_dir, $show_hidden, $download, $serve_fn, $prms, $recursive);
      }
      catch(\Exception $e)
      {
        $messages['error'][] = $e->getMessage();
      }

    }
    
//    ###################################################################################################
//    $abc123456 = \compact('files_info');
//    echo('<br/>:::::::::::::::::::::<br/>');
//    debug_display($abc123456);
//    echo('<br/>' . __FILE__ . ' : (' . __CLASS__ . ' :: ' . __FUNCTION__ . ') : ' . __LINE__ . '<br/>');
//    die('<br/>RIP!<br/>');
//    ###################################################################################################
  
    // Access the file information
    $file_names = $files_info['file_names'];
    $file_types = $files_info['file_types'];
    $file_dates = $files_info['file_dates'];
    $file_sizes = $files_info['file_sizes'];
    $f_count = $files_info['f_count'];
    
    ###################################################################################################
    $abc123456 = \compact('file_names', 'files_info');
    echo('<br/>:::::::::::::::::::::<br/>');
    debug_display($abc123456);
    echo('<br/>' . __FILE__ . ' : (' . __CLASS__ . ' :: ' . __FUNCTION__ . ') : ' . __LINE__ . '<br/>');
    die('<br/>RIP!<br/>');
    ###################################################################################################
    
    $file_shortnames = \array_map('\strtolower', $file_names);
    
    # Change file sort order if needed
    $sort .= '  ';
    
    if( 0 === \stripos($sort, 'd') )
    {
      if( 0 === \stripos($sort, 'dd') )
      {
        \array_multisort($file_dates, \SORT_DESC, $file_types, $file_shortnames, $file_names);
      }
      else
      {
        \array_multisort($file_dates, \SORT_ASC, $file_types, $file_shortnames, $file_names);
      }
    }
    else if( 's' === \strtolower($sort[0]) )
    {
      if( 0 === \stripos($sort, 'sd') )
      {
        \array_multisort($file_sizes, \SORT_DESC, $file_types, $file_shortnames, $file_names);
      }
      else
      {
        \array_multisort($file_sizes, \SORT_ASC, $file_types, $file_shortnames, $file_names);
      }
    }
    else if(0 === \stripos($sort, 'n'))
    {
      if(0 === \stripos($sort, 'nd'))
      {
        \array_multisort($file_shortnames, \SORT_DESC, $file_names, $file_types);
      }
      else
      {
        \array_multisort($file_shortnames, \SORT_ASC, $file_names, $file_types);
      }
    }
    else
    {
      \array_multisort($file_types, $file_shortnames, $file_names);
    }
    
    # messages to display on the frontend
    $messages   = [];
    $files_list = [];
    
    if(!$max_entries)
    {
      $max_entries = $f_count + 1;
    } # 0 == all
    
    if(!empty($get_params[$q_var]) && ($max_count <= $max_entries))
    {
      $entry['name']     = '..';
      $entry['type']     = 'parent_dir';
      $entry['url']      = utils::recreate_Url( utils::join_uri($sub_dir, '..') );
      $entry['file_ext'] = $file_ext;
      $files_list[]      = $entry;
    }
    
    $defaultextension = [
      '.pdf',
      '.doc',
      '.docx',
      '.txt',
      '.rtf',
      '.xls',
      '.xlsx',
      '.avi',
      '.mov',
      '.iso',
      '.flv',
      '.swf',
      '.exe'
    ];
    
    $image_ext_list = [
      'ico',
      'gif',
      'jpg',
      'png'
    ];
  
    $total      = 0;
    $file_count = 0;
    $dir_count  = 0;
    $dl_db      = [];
  
    if($count_downloads)
    {
      $dl_db = utils::read_dl_db($prms);
    }

    for($i = 0; $i <= $f_count; $i++)
    {
      if($max_count <= $max_entries)
      {
        $full_name  = \cms_join_path($root_path, utils::uri_2_path(utils::join_uri($folder, $sub_dir, $file_names[$i])));
        $directory = \is_dir($full_name);
        
        if(!$directory)
        {
          $array    = \explode('.', $file_names[$i]);
          $file_ext = \end($array);
          $file_count++;
        }
        else
        {
          $file_ext = 'Directory';
          $dir_count++;
        }
        
        $extension_tmp     = \trim($file_extension);
        $replace_extension = \explode(',', $extension_tmp);
        $clean_name        = \str_replace($delimiter, ' ', $file_names);
        $pretty_clean_name  = \str_replace($defaultextension, ' ', $clean_name);
        $super_clean_name   = \str_replace($replace_extension, ' ', $pretty_clean_name);
        //$cleaniconurl     = \str_replace($delimiter, '-', $file_names);
        //$prettycleaniconurl = str_replace($defaultextension, $iconextension, $cleaniconurl);
        //$autoiconpath = 'uploads/images/appicons/' . $file_names;
        //$iconpath = 'uploads/images/appicons/' . $prettycleaniconurl[$i];
        //$basePath = 'uploads/images/appicons/';
        
        if($directory && $browse_sub_dirs)
        {
          $href = utils::recreate_Url($file_names[$i], $q_var);
        }
        else
        {
          $href        = utils::join_uri($folder, $sub_dir, $file_names[$i]);
          $thumbs_path = utils::join_uri($folder, $sub_dir, $thumbs_folder, $file_names[$i]);
        }
        
        if( $browse_sub_dirs || !\is_dir($full_name) )
        {
          if($strip_extension && !$directory)
          {
            $entry['name']       = $super_clean_name[$i];
            $entry['type']       = 'file';
            $entry['sub_type']    = (\in_array($file_ext, $image_ext_list) ? 'image' : $file_ext);
            $entry['thumbs_url'] = 'image' === $entry['sub_type'] ? $thumbs_path : '';
            
            if($obfuscate_download)
            {
              $uri_params   = [
                $security_token_qv => $token,
                $q_var             => utils::join_uri($sub_dir, $file_names[$i])
              ];
              $url          = utils::get_naked_uri() . '?' . \http_build_query($uri_params);
              $entry['url'] = $url;
            }
            else
            {
              $entry['url'] = $href;
            }
            
            $entry['file_ext'] = $file_ext;
            $entry['title']    = 'Link to download the ' . $super_clean_name[$i] . ' document in ' . $file_ext .
                                 ' format';
          }
          else if(!$directory)
          {
            $entry['name']       = $super_clean_name[$i];
            $entry['type']       = 'file';
            $entry['sub_type']    = (\in_array($file_ext, $image_ext_list) ? 'image' : $file_ext);
            $entry['thumbs_url'] = 'image' === $entry['sub_type'] ? $thumbs_path : '';
            
            if($obfuscate_download)
            {
              $uri_params   = [
                $security_token_qv => $token,
                $q_var             => utils::join_uri($sub_dir, $file_names[$i])
              ];
              
              $url          = utils::get_naked_uri() . '?' . \http_build_query($uri_params);
              $entry['url'] = $url;
            }
            else
            {
              $entry['url'] = $href;
            }
            
            $entry['file_ext'] = $file_ext;
            $entry['title']    = 'Link to download the ' . $super_clean_name[$i] . ' document in ' . $file_ext .
                                 ' format';
          }
          else
          {
            $tn                = \is_array($super_clean_name) ? $super_clean_name[$i] : '';
            $entry['name']     = $tn;
            $entry['type']     = 'dir';
            $entry['url']      = $href;
            $entry['file_ext'] = $file_ext;
            $entry['title']    = 'Link to the ' . $tn . ' ' . $file_ext;
          }
  
          if(!$directory && $count_downloads)
          {
            if(isset($dl_db[$full_name]) )
            {
              $entry['download_count'] = $dl_db[$full_name];
            }
            else
            {
              $entry['download_count'] = 0;
            }
          }
          
          if($show_size && !$directory)
          {
            if(\sprintf('%u', \filesize($full_name) < 1024))
            {
              $entry['size'] = \sprintf('%u', \filesize($full_name)) . ' B';
            }
            else if(\sprintf('%u', \filesize($full_name) < 1000000))
            {
              $entry['size'] = \sprintf('%u', \filesize($full_name) / 1024) . ' KB';
            }
            else if(\sprintf('%u', \filesize($full_name) < 1000000000))
            {
              $entry['size'] = \sprintf('%01.1f', \filesize($full_name) / 1048576) . ' MB';
            }
            else
            {
              $entry['size'] =\sprintf('%01.1f', \filesize($full_name) / 1073741824) . ' GB';
            }
  
            $total += (int)\sprintf('%u', \filesize($full_name));
          }
          
          if($date && $directory)
          {
            $entry['date'] = \date($dateformat, \filemtime($full_name));
          }
          else if($date)
          {
            $entry['date'] = \date($dateformat, \filemtime($full_name));
          }
          
          $max_count++;
          
          $files_list[] = $entry;
        }
      }
    }
    
    if( \sprintf('%u', (int)$total < 1024) )
    {
      $total_text = \sprintf('%u', (int)$total) . ' B';
    }
    else if(\sprintf('%u', (int)$total < 1000000))
    {
      $total_text = \sprintf('%u', (int)$total / 1024) . ' KB';
    }
    else if(\sprintf('%u', (int)$total < 1000000000))
    {
      $total_text = \sprintf('%01.1f', (int)$total / 1048576) . ' MB';
    }
    else
    {
      $total_text =\sprintf('%01.1f', (int)$total / 1073741824) . ' GB';
    }
  
    $files_list_obj                  = new \stdClass();
    $files_list_obj->messages        = $messages; # to be implemented;
    $files_list_obj->files           = $files_list;
    $files_list_obj->total_size_text = $total_text;
    $files_list_obj->total_size      = (int)$total;
    $files_list_obj->file_count      = $file_count;
    $files_list_obj->dir_count       = $dir_count;
    $files_list_obj->items_count     = $dir_count + $file_count;
    
    $smarty_var = $params['assign'] ?? 'files_list';
    $template->assign($smarty_var, $files_list_obj);
  }
  
  /**
   * @throws \CmsError404Exception
   */
  function trigger_404($params, $smarty)
  {
    $msg = $params['msg'] ?? 'This content is not available';
    $active = !empty($params['msg']);
    
    if($active)
    {
      throw new \CmsError404Exception($msg);
    }
  }
  
  /**
   * @throws \CmsError403Exception
   */
  function trigger_403($params, $smarty)
  {
    $msg = $params['msg'] ?? 'Permission denied!';
    $active = !empty($params['msg']);
  
    if($active)
    {
      throw new \CmsError403Exception($msg);
    }
  }
}

?>