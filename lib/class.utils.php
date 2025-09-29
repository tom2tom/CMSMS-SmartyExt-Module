<?php


namespace SmartyExt;

class utils
{
  /**
   * related with content_fetch
   */
  
  /**
   * id_to_array
   *
   * adds ID of an item to a list (array) of IDs.
   * If the ID already is part of the array, don't add it.
   *
   * @param array|null $array
   * @param null       $item
   *
   * @return mixed
   */
  static function id_to_array(array $array = null, $item = null)
  {
    
    if( empty($item) ) { return  $array; }
    
    if( !\is_array($array) )
    {
      if( !empty($array) ) { $array = [$array]; }
      else { $array = []; }
    }
    
    $size  = \count($array);
    
    for($q = 0; $q <= $size - 1; $q++)
    {
      if($array[$q] === $item) { return $array; }
    }
  
    $array[$size] = $item;
    
    return $array;
  }
  
  /**
   * get_parent_data
   *
   * Looks for alias and content-name (=title)
   * of the pages we require this data
   * for (our previously build array
   * of parent pages).
   * Afterwards, provide an array of classes to be used
   * for adding the data to the items in our content_dump.
   *
   * @param array $arraydata
   *
   * @return array
   * @throws \Exception
   */
  static function get_parent_data(array $arraydata)
  {
    $db                      = \cmsms()->GetDb();
    $sql_parents_query_start = 'SELECT content_id, content_alias, content_name, menu_text
			FROM ' . cms_db_prefix() . 'content';
    
    $size                    = \count($arraydata);
    $sql_parent_query_what   = '';
    
    for($i = 0; $i <= $size - 1; $i++)
    {
      if(0 === $i)
      {
        $sql_parent_query_what .= ' WHERE  content_id = ' . $arraydata[$i] . ' ';
      }
      
      if($i > 0)
      {
        $sql_parent_query_what .= ' OR content_id = ' . $arraydata[$i] . ' ';
      }
    }
    
    $sql_parents_query = $sql_parents_query_start . '		' . $sql_parent_query_what;
    
    $dbparents = $db->Execute($sql_parents_query);
    
    if( $db->ErrorNo() )
    {
      throw new \RuntimeException('DB error: ' . $db->ErrorMsg() );
    }
    
    $parents_dump = [];
    
    while($dbparents && $dbparentsrow = $dbparents->FetchRow())
    {
      $parent         = new \stdClass;
      $parent->id     = $dbparentsrow['content_id'];
      $parent->alias  = $dbparentsrow['content_alias'];
      $parent->title  = $dbparentsrow['content_name'];
      $parent->menu   = $dbparentsrow['content_name'];
      $parents_dump[] = $parent;
    }
    
    return $parents_dump;
  }
  
  /**
   * get_user_data
   *
   * Looks up all users' information
   * (ID, username, first & last name as well as email)
   * and return them as an array of classes.
   *
   * @param $arraydata
   *
   * @return array
   * @throws \Exception
   */
  static function get_user_data(array $arraydata)
  {
    $db = cmsms()->GetDb();
    
    $sql_users_query_start = 'SELECT user_id, username, first_name, last_name, email
			FROM ' . cms_db_prefix() . 'users ';
    
    $size = \count($arraydata);
    
    for($i = 0; $i <= $size - 1; $i++)
    {
      if(0 === $i)
      {
        $sql_user_query_what = ' WHERE  user_id = ' . $arraydata[$i] . ' ';
      }
      if($i > 0)
      {
        $sql_user_query_what .= '  OR user_id = ' . $arraydata[$i] . ' ';
      }
      
    }
    
    $sql_users_query = $sql_users_query_start . '	' . $sql_user_query_what;
    
    $dbuser = $db->Execute($sql_users_query);
  
    if( $db->ErrorNo() )
    {
      throw new \RuntimeException('DB error: ' . $db->ErrorMsg() );
    }
  
    $get_user_data = [];
    
    while($dbuser && $dbuserrow = $dbuser->FetchRow())
    {
      $user             = new \stdClass;
      $user->id         = $dbuserrow['user_id'];
      $user->username   = $dbuserrow['username'];
      $user->first_name = $dbuserrow['first_name'];
      $user->last_name  = $dbuserrow['last_name'];
      $user->email      = $dbuserrow['email'];
      $get_user_data[]  = $user;
    }
    
    return $get_user_data;
  }
  
  
  /**
   * get_max_page
   *
   * get the number of the highest page available.
   *
   * @param $content
   * @param $sql_limit_hierarchy
   * @param $sql_prefix_filter
   * @param $sql_content_filter
   * @param $count
   * @param $offset
   * @param $sql_excludes
   * @param $sql_hierarchy
   * @param $active_mode
   * @param $showmenu_mode
   *
   * @return float|int
   * @throws \Exception
   */
  static function get_max_page(
                          $content,
                          $sql_limit_hierarchy,
                          $sql_prefix_filter,
                          $sql_content_filter,
                          $count,
                          $offset,
                          $sql_excludes,
                          $sql_hierarchy,
                          $active_mode,
                          $showmenu_mode
                        )
  {
    
    # No extension required
    # we only check for the single specified block
    
    $sql_query_where    = "AND prop_name='" . $content . "'";
    $sql_query_excludes = '';
    for($i = 0; $i <= \count($sql_excludes) - 1; $i++)
    {
      $sql_query_excludes .= 'AND ' . cms_db_prefix() . 'content_props.content_id != ' . $sql_excludes[$i] . ' ';
    }
    
    $db = cmsms()->GetDb();
    
    $sql_user_query = 'SELECT '
                      . cms_db_prefix()
                      . 'content.content_id, prop_name, id_hierarchy FROM '
                      . cms_db_prefix()
                      . 'content, '
                      . cms_db_prefix()
                      . 'content_props WHERE '
                      . cms_db_prefix()
                      . 'content.content_id = '
                      . cms_db_prefix()
                      . 'content_props.content_id '
                      . $sql_query_where . ' '
                      . $sql_content_filter . ' '
                      . $sql_prefix_filter . ' '
                      . $sql_limit_hierarchy . ' '
                      . $sql_query_excludes . ' '
                      . $sql_hierarchy . ' '
                      . $active_mode . ' '
                      . $showmenu_mode;
    
    $dbcontent      = $db->Execute($sql_user_query);
  
    if( $db->ErrorNo() )
    {
      throw new \RuntimeException('DB error: ' . $db->ErrorMsg() );
    }
    
    if($dbcontent->RecordCount() >= 1)
    {
      return \ceil(($dbcontent->RecordCount() - $offset) / $count);
    }
  
    return 0;
  }
  
  /**
   * get_current_level
   *
   * Provide the hierarchy level of a specific page (by content_id)
   *
   * @param $id
   *
   * @return int
   * @throws \Exception
   */
  static function get_current_level($id)
  {
    $current_level = 0;
    $db                  = cmsms()->GetDb();
    $q                   = 'SELECT id_hierarchy, content_id FROM ' . cms_db_prefix() . 'content WHERE content_id = ' .
                           $id . ' ';
    
    $dbhierarchy = $db->Execute($q);
  
    if( $db->ErrorNo() )
    {
      throw new \RuntimeException('DB error: ' . $db->ErrorMsg() );
    }
    
    while($dbhierarchy && $dbhierarchyrow = $dbhierarchy->FetchRow())
    {
      $current_level = \substr_count($dbhierarchyrow['id_hierarchy'], '.');
    }
    
    ++$current_level;
    
    return $current_level;
  }
  
  
  /**
   * @param $content
   * @param $sql_limit_hierarchy
   * @param $sql_prefix_filter
   * @param $content_filter
   * @param $count
   * @param $offset
   * @param $sql_excludes
   * @param $sql_hierarchy
   *
   * @return array|mixed
   * @throws \Exception
   */
  static function get_matching_pages(
                                $content,
                                $sql_limit_hierarchy,
                                $sql_prefix_filter,
                                $content_filter,
                                $count,
                                $offset,
                                $sql_excludes,
                                $sql_hierarchy
                              )
  {
  
    $sql_content_filter = '';
    
    if('' !== $content_filter)
    {
      $sql_content_filter = "AND match(content) against('+" . $content_filter . "' in boolean mode)";
    }
    
    $sql_query_where    = "AND prop_name='" . $content . "' ";
    $sql_query_excludes = '';
    for($i = 0; $i <= count($sql_excludes) - 1; $i++)
    {
      $sql_query_excludes .= 'AND ' . cms_db_prefix() . 'content_props.content_id != ' . $sql_excludes[$i] . ' \n';
    }
    
    $db = cmsms()->GetDb();
    
    $sql_filter_query = 'SELECT '
                        . cms_db_prefix()
                        . 'content.content_id, prop_name, id_hierarchy FROM '
                        . cms_db_prefix()
                        . 'content, '
                        . cms_db_prefix()
                        . 'content_props WHERE '
                        . cms_db_prefix()
                        . 'content.content_id = '
                        . cms_db_prefix()
                        . 'content_props.content_id '
                        . $sql_query_where . ' '
                        . $sql_content_filter . ' '
                        . $sql_prefix_filter . ' '
                        . $sql_limit_hierarchy . ' '
                        . $sql_query_excludes . ' '
                        . $sql_hierarchy;
    
    $dbfilter         = $db->Execute($sql_filter_query);
  
    if( $db->ErrorNo() )
    {
      throw new \RuntimeException('DB error: ' . $db->ErrorMsg() );
    }
    
    $filter_ids = [];
    while($dbfilter && $dbfilterrow = $dbfilter->FetchRow())
    {
      $filter_ids = self::id_to_array($filter_ids, $dbfilterrow['content_id']);
    }
  
    return $filter_ids;
    
  }
  
  /**
   * sql_sort_param
   *
   * a function that allows the user
   * to use simple words instead of the table column names
   * to sort data.
   *
   * Additionally, it makes it easy to expand the sql query string
   *
   * @param $sort_by
   * @param $flag: If equal 0 the parameter is the first and thus the mySQL command ORDER BY will be added to the string
   * @param $order
   *
   * @return string
   */
  static function sql_sort_param($sort_by, $flag, $order)
  {
    $sql_sort_param = '';
    
    if(0 === $flag)
    {
      $sql_sort_param = ' ORDER BY ';
    }
    
    # User-friendly selection of table columns for sorting
    switch($sort_by)
    {
      case 'id':
        $sql_sort_param .= 'content_id';
      break;
      case 'alias':
        $sql_sort_param .= 'content_alias';
      break;
      case 'title':
        $sql_sort_param .= 'content_name';
      break;
      case 'show':
        $sql_sort_param .= 'show_in_menu';
      break;
      case 'created':
        $sql_sort_param .= 'create_date';
      break;
      case 'modified':
        $sql_sort_param .= 'modified_date';
      break;
      case 'owner':
        $sql_sort_param .= 'owner_id';
      break;
      case 'id_hierarchy':
        $sql_sort_param .= 'id_hierarchy';
      break;
      case 'lasteditor':
        $sql_sort_param .= 'last_modified_by';
      break;
      case 'active':
        $sql_sort_param .= 'active';
      break;
      default:
        $sql_sort_param .= 'hierarchy';
      break;
    }
    
    # checks if the sorting order is set to from bottom to top,
    # if not, it will set it to the reverse
    if('up' === $order)
    {
      $sql_sort_param .= ' ASC ';
    }
    else
    {
      $sql_sort_param .= ' DESC ';
    }
    
    # If smaller/equal 1 the parameter is not the last
    # and will be seperated with a comma from the next one
    if($flag >= 1)
    {
      $sql_sort_param = ', ' . $sql_sort_param;
    }
    
    return $sql_sort_param;
  }
  
  /**
   * strip_out
   *
   *  strips out smarty or HTML (incl JS and CSS)
   *
   * @param $content
   * @param $mode
   *
   * @return string
   */
  static function strip_out($content, $mode)
  {
    $ret = $content;
    
    $htmlexpressions = [
      '@<script[^>]*?>.*?</script>@si',  // Strip out javascript
      '@<style[^>]*?>.*?</style>@siU',   // Strip style tags properly
      '@<[\/\!]*?[^<>]*?>@si',           // Strip out HTML tags
      '@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments including CDATA
    ];
    
    if('html' === $mode)
    {
      $ret = \preg_replace($htmlexpressions, '', $content);
    }
    else if('smarty' === $mode)
    {
      # note to self: in \} the escaping \ may be redundant... revisit
      $ret = \preg_replace('/\{.*?\}/', '', $content);
    }
    
    return $ret;
  }
  
  /**
   * related with files_list
   */
  
  static function recreate_UrlSmart($subdir, $q_var = 'fl')
  {
    $subdir     = \rtrim($subdir, '/');
    $get_params = isset($_GET) ? $_GET : [];
    $naked_uri  = self::get_naked_uri();
    
    if( isset($get_params[$q_var]) && 0 !== \strpos($subdir, '..') )
    {
      $subdir1 = \preg_replace('/\.{2}(\x2F)$/', '', \urldecode($get_params[$q_var]));
      $subdir  = \preg_replace('/[^\x2F]+(\x2F)$/', '', $subdir1);
      
      
      if(empty($subdir) || $subdir == $get_params[$q_var])
      {
        unset($get_params[$q_var]);
      }
      else
      {
        $get_params[$q_var] = $subdir;
      }
    }
    else
    {
      $get_params[$q_var] = $subdir;
    }
    
    $smarturl = $naked_uri;
    
    if(!empty($get_params))
    {
      $smarturl .= '?' . \http_build_query($get_params);
    }
    
    return $smarturl;
    
  }
  
  public static function get_naked_uri()
  {
    $uri_parts = \explode('?', $_SERVER['REQUEST_URI'], 2);
    
    return $uri_parts[0];
  }
  
  public static function recreate_Url($sub_dir, $q_var = 'fl')
  {
    $sub_dir    = \rtrim($sub_dir, '/');
    $get_params = $_GET ?? [];
    $naked_uri  = self::get_naked_uri();
    
    if(isset($get_params[$q_var]))
    {
      if( FALSE !== \strpos($sub_dir, '..') )
      {
        # revisit this....
        $sub_dir_1 = \preg_replace('/\.{2}(\x2F)$/', '', $get_params[$q_var]); #..
        $sub_dir = \preg_replace('/[^\x2F]+(\x2F)$/', '', $sub_dir_1);         # ****/
        # ..................
        if(empty($sub_dir))
        {
          unset($get_params[$q_var]);
        }
        elseif($sub_dir === $sub_dir_1)
        {
          $tmp = \explode('/', $sub_dir);
          unset($tmp[\count($tmp) - 1]);
          $get_params[$q_var] = \implode('/', $tmp);
        }
        else if($sub_dir === $get_params[$q_var])
        {
          $tmp = \explode('/', $sub_dir);
          unset($tmp[\count($tmp) - 1]);
          $get_params[$q_var] = \implode('/', $tmp);
        }
        else
        {
          $get_params[$q_var] = $sub_dir;
        }
      }
      else
      {
        $get_params[$q_var] = self::join_uri($get_params[$q_var], $sub_dir);
      }
    }
    else
    {
      $get_params[$q_var] = $sub_dir;
    }
    
    $url = $naked_uri;
    
    if(empty($get_params[$q_var]))
    {
      unset($get_params[$q_var]);
    }
    
    if(!empty($get_params))
    {
      if(empty($get_params[$q_var]) && NULL !== $get_params[$q_var])
      {
        unset($get_params[$q_var]);
      }
      $url .= '?' . \http_build_query($get_params);
    }
    
    return $url;
  }
  
  public static function uri_2_path($uri)
  {
    return \str_replace('/', \DIRECTORY_SEPARATOR, \trim($uri, '/'));
  }
  
  public static function join_uri()
  {
    $args = \func_get_args();
    $args = \array_map(
      static function($srt) {
        return \trim($srt, '/');
      },
      $args
    );
    
    $args = \array_filter($args);
    
    return \implode('/', $args);
  }
  
  public static function read_dl_db($params)
  {
    \extract($params, \EXTR_OVERWRITE);
    /** @var string $dl_counter_db_dir in scope from extract */
    /** @var string $dl_counter_db_fn in scope from extract */
    $db_dn      = empty($dl_counter_db_dir) ? $folder : $dl_counter_db_dir;
    $db_fn      = empty($dl_counter_db_fn) ? '.counter_db' : $dl_counter_db_fn;
    $db_full_fn = \cms_join_path($db_dn, $db_fn);
    
    if( \file_exists($db_full_fn) )
    {
      $fo     = \fopen($db_full_fn, 'rb');
      
      \flock($fo, \LOCK_SH, $waitIfLocked);
      $string = @\file_get_contents($db_full_fn);
      \flock($fo, \LOCK_UN);
      \fclose($fo);
    }
    else
    {
      $string = '';
    }
  
    $tmp = \explode(\PHP_EOL, $string);
    $db = [];
  
    foreach($tmp as $line)
    {
      if( !empty($line) )
      {
        $t = \explode('::', $line);
        $db[$t[0]] = $t[1];
      }
    }
    
    return $db;
  }
  
  public static function do_count_download($full_filename, $filename, $params)
  {
    $db = self::read_dl_db($params);
    \extract($params, \EXTR_OVERWRITE);
    /** @var string $dl_counter_db_dir in scope from extract */
    /** @var string $dl_counter_db_fn in scope from extract */
    $db_dn = empty($dl_counter_db_dir) ? $folder : $dl_counter_db_dir;
    $db_fn = empty($dl_counter_db_fn) ? '.counter_db' : $dl_counter_db_fn;
    $db_full_fn = \cms_join_path($db_dn, $db_fn);
  
    $key = $full_filename;
  
    if( isset($db[$key]) )
    {
      (int)$db[$key]++;
    }
    else
    {
      $db[$key] = 1;
    }
    
    $string = '';
    
    foreach($db as $k => $v)
    {
      $string .= $k . '::' . $v . \PHP_EOL;
    }
  
    \file_put_contents($db_full_fn, $string, \LOCK_EX);
  }
  
  public static function do_download($full_filename, $filename, $params)
  {
    $handlers = \ob_list_handlers();
    for($cnt = 0, $cntMax = \count($handlers); $cnt < $cntMax; $cnt++) { \ob_end_clean(); }
    # grab the params if they exist
    \extract($params, \EXTR_OVERWRITE);
  
    $count_downloads   = (bool)(!empty($count_downloads) ?? $count_downloads);
    
    #some mime types
    $mime_types = [
      '.avi'  => 'video/avi',
      '.bmp'  => 'image/bmp',
      'webp'  => 'image/webp',
      '.csv'  => 'text/csv',
      '.doc'  => 'application/msword',
      '.exe'  => 'application/octet-stream',
      '.gif'  => 'image/gif',
      '.html' => 'text/html',
      '.ico'  => 'image/x-icon',
      '.jpg'  => 'image/jpeg',
      '.mov'  => 'video/quicktime',
      '.pdf'  => 'application/pdf',
      '.png'  => 'image/png',
      '.txt'  => 'text/plain',
      '.rtf'  => 'text/richtext',
      '.swf'  => 'application/x-shockwave-flash',
      '.xls'  => 'application/excel'
    ];
    
    if(\file_exists($full_filename))
    {
      if($count_downloads)
      {
        self::do_count_download($full_filename, $filename, $params);
      }
      
      $array     = \explode('.', $full_filename);
      $file_ext  = '.' . \end($array);
      $mime_type = $mime_types[$file_ext] ?? 'application/x-binary';
      
      # set headers accordingly
      \header('Content-type: ' . $mime_type);
      \header('Content-Description: File Transfer');
      \header('Content-Length: ' . \filesize($full_filename));
      \header('Content-Disposition: attachment; filename=' . $filename);
      \header('Pragma: no-cache');
      \header('Expires: 0');
      \readfile($full_filename);
      exit;
    }
  }
  
  /**
   * doesn't seem to work on most pdf files, so...
   * we'll likely ditch it soon
   * @param string $file
   * @return array
   */
  public static function get_pdf_info($file)
  {

    $title = '';
    $author = '';
    
    $description = '';
    if(\is_file($file) )
    {
      $handle = \fopen ($file, 'rb');
      while (!\feof($handle))
      {
        $buffer = \fgets($handle, 1024);
        
        if( 0 === \strpos($buffer, '/Title (') )
        {
          $title = \str_replace(
            '\\', '', 'UTF-8' === \mb_detect_encoding($buffer, 'auto', TRUE)
            ? \substr(\trim($buffer), 8, -1)
            : \mb_convert_encoding(
              \substr(\trim($buffer), 8, -1), 'UTF-8'
            )
          );
        }
        
        if( 0 === \strpos($buffer, '/Author (') )
        {
          $author = \str_replace(
            '\\', '', 'UTF-8' === \mb_detect_encoding($buffer, 'auto', TRUE)
            ? \substr(\trim($buffer), 9, -1)
            : \mb_convert_encoding(
              \substr(\trim($buffer), 9, -1), 'UTF-8'
            )
          );
        }
        
        if( 0 === \strpos($buffer, '/Subject (') )
        {
          $description = \str_replace(
            '\\', '', 'UTF-8' === \mb_detect_encoding(
                        $buffer, 'auto', TRUE
                      ) ? \substr(
            \trim($buffer), 10, -1
          ) : \mb_convert_encoding(\substr(\trim($buffer), 10, -1), 'UTF-8')
          );
        }
  
        if($title && $author && $description)
        {
          \fclose($handle);
          break;
        }
      }
      
      if(!$title || !$author || !$description)
      {
        \fclose($handle);
      }
    }
  
    return [
      'title'       => $title,
      'author'      => $author,
      'description' => $description
    ];
  }
  
  /**
   * return a list of files in a directory recursively or not
   * as an array
   *
   * @param      $directory
   * @param      $root_path
   * @param      $folder
   * @param      $sub_dir
   * @param bool $show_hidden
   * @param bool $download
   * @param null $serve_fn
   * @param null $prms
   * @param bool $recursive
   *
   * @return array
   */
  static function getDirectoryFilesInfo($directory, $root_path, $folder, $sub_dir, $show_hidden = false, $download = false, $serve_fn = null, $prms = null, $recursive = false): array
  {
    $files = [];
//    $file_types = [];
//    $file_dates = [];
//    $file_sizes = [];
  
    $handle = \opendir($directory);
  
    if (false === $handle)
    {
      throw new \RuntimeException("Unable to open directory: $directory");
    }
  
    while ($entry = readdir($handle))
    {
      if ($entry !== '.' && $entry !== '..')
      {
        // Prevent showing hidden files unless specified
        if (!$show_hidden && startsWith($entry, '.'))
        {
          continue;
        }
      
        $full_name = cms_join_path($root_path, utils::uri_2_path(utils::join_uri($folder, $sub_dir, $entry)));
      
        if ($download && $serve_fn === $entry)
        {
          utils::do_download($full_name, \urlencode($entry), $prms);
        }
  
        $file_info = [
          'name'           => $entry,
          'full_name'      => $full_name,
          'type'           => \filetype($full_name),
          'date'           => \filemtime($full_name),
          'size'           => \sprintf('%u', \filesize($full_name)),
          'children_count' => 0
        ];
      
        if (\is_dir($full_name) && $recursive)
        {
          $sub_dir_info = self::getDirectoryFilesInfo($full_name, $root_path, $folder, utils::join_uri($sub_dir, $entry), $show_hidden, $download, $serve_fn, $prms, true);
          $file_info['children'] = $sub_dir_info['file_names'];
          $file_info['children_count'] = is_array($sub_dir_info['file_names']) ? count($sub_dir_info['file_names']) : 0;
        }
      
        $files[] = $file_info;
      }
    }
  
    closedir($handle);
  
    return $files;
  }
  
  
  
  
  
}

?>