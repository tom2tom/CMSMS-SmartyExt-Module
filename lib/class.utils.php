<?php
#BEGIN_LICENSE
#
# Module: SmartyExt (c) 2020-2025 by CMS Made Simple Foundation Inc.
# An add-on module for CMS Made Simple to provide useful functions
#  and commonly used gui capabilities to other modules.
# A fork of module: CGSimpleSmarty (c) 2008-2014 by Robert Campbell
#
# CMSMS - CMS Made Simple is (c) 2006 - 2021 by CMS Made Simple Foundation
# CMSMS - CMS Made Simple is (c) 2004 by Ted Kulp (wishy@cmsmadesimple.org)
# Visit the CMSMS Homepage at: https://www.cmsmadesimple.org
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
#
#END_LICENSE

namespace SmartyExt;

use RuntimeException;
use stdClass;
use function cms_join_path;
use function cmsms;
// method get_pdf_info() assumes a couple of mb_* functions are available
use function mb_convert_encoding;
use function mb_detect_encoding;
use const CMS_DB_PREFIX;

class utils
{
    /**
     * Add the numeric id of an item to an array of such ids, unless the id
     * is already in there.
     *
     * @param mixed $arr The array to be updated. Default []
     * @param mixed $id The value to be added Default null
     *
     * @return array maybe empty
     */
    public static function id_to_array($arr = [], $id = null)
    {
        if ($id === null) {
            return $arr;
        }

        if (!is_array($arr)) {
            if ($arr) {
                $arr = [$arr];
            } else {
                $arr = [];
            }
        }

        $size = count($arr);

        for ($q = 0; $q < $size; ++$q) {
            if ($arr[$q] === $id) {
                return $arr;
            }
        }

        $arr[$size] = $id;
        return $arr;
    }

    /**
     * Look for alias and content-name (=title) of the previously-built
     * array of parent pages.
     * Then provide an array of classes to be used for adding data to
     * the items in our content_dump.
     *
     * @param array $arraydata
     *
     * @return array
     * @throws RuntimeException
     */
    public static function get_parent_data(array $arraydata)
    {
        $db = cmsms()->GetDb();
        $sql_parents_query_start = 'SELECT content_id, content_alias, content_name, menu_text FROM ' . CMS_DB_PREFIX . 'content';

        $size = count($arraydata);
        $sql_parent_query_what = '';

        for ($i = 0; $i < $size; ++$i) {
            if (0 == $i) {
                $sql_parent_query_what .= ' WHERE content_id = ' . $arraydata[$i];
            } else {
                $sql_parent_query_what .= ' OR content_id = ' . $arraydata[$i];
            }
        }

        $sql_parents_query = $sql_parents_query_start . $sql_parent_query_what;

        $dbparents = $db->Execute($sql_parents_query);

        if ($db->ErrorNo()) {
            if ($dbparents) $dbparents->Close();
            throw new RuntimeException('DB error: ' . $db->ErrorMsg());
        }

        $parents_dump = [];

        while ($dbparents && $dbparentsrow = $dbparents->FetchRow()) {
            $parent = new stdClass();
            $parent->id = $dbparentsrow['content_id'];
            $parent->alias = $dbparentsrow['content_alias'];
            $parent->title = $dbparentsrow['content_name'];
            $parent->menu = $dbparentsrow['content_name'];
            $parents_dump[] = $parent;
        }
        if ($dbparents) $dbparents->Close();

        return $parents_dump;
    }

    /**
     * Return specified users' information
     * (ID, username, first & last name, email)
     * as an array of classes.
     *
     * @param array $arraydata wanted user-ids
     *
     * @return array
     * @throws RuntimeException
     */
    public static function get_user_data(array $arraydata)
    {
        $db = cmsms()->GetDb();
        $sql_users_query_start = 'SELECT user_id, username, first_name, last_name, email FROM ' . CMS_DB_PREFIX . 'users';

        $size = count($arraydata);

        for ($i = 0; $i <= $size - 1; ++$i) {
            if (0 == $i) {
                $sql_user_query_what = ' WHERE  user_id = ' . $arraydata[$i];
            } else {
                $sql_user_query_what .= ' OR user_id = ' . $arraydata[$i];
            }
        }

        $sql_users_query = $sql_users_query_start . $sql_user_query_what;

        $dbuser = $db->Execute($sql_users_query);

        if ($db->ErrorNo()) {
            if ($dbuser) $dbuser->Close();
            throw new RuntimeException('DB error: ' . $db->ErrorMsg());
        }

        $get_user_data = [];

        while ($dbuser && $dbuserrow = $dbuser->FetchRow()) {
            $user = new stdClass();
            $user->id = $dbuserrow['user_id'];
            $user->username = $dbuserrow['username'];
            $user->first_name = $dbuserrow['first_name'];
            $user->last_name = $dbuserrow['last_name'];
            $user->email = $dbuserrow['email'];
            $get_user_data[] = $user;
        }
        if ($dbuser) $dbuser->Close();

        return $get_user_data;
    }

    /**
     * Get the number of the highest page available.
     * db table CMS_DB_PREFIX content is aliased as C in sql command
     * db table CMS_DB_PREFIX content_props is aliased as P in sql command
     *
     * @param string $content
     * @param string $sql_limit_hierarchy Default ''
     * @param string $sql_prefix_filter Default ''
     * @param string $sql_content_filter Default ''
     * @param int $count Default 1
     * @param int $offset Default 0
     * @param array $sql_excludes Default []
     * @param string $sql_hierarchy Default ''
     * @param string $active_mode Default ''
     * @param string $showmenu_mode Default ''
     *
     * @return int Possibly 0
     * @throws RuntimeException
     */
    public static function get_max_page(
        $content,
        $sql_limit_hierarchy = '',
        $sql_prefix_filter = '',
        $sql_content_filter = '',
        $count = 1,
        $offset = 0,
        $sql_excludes = [],
        $sql_hierarchy = '',
        $active_mode = '',
        $showmenu_mode = ''
    ) {
        // No extension required
        // we only check for the single specified block

        $sql_query_where = " AND P.prop_name='" . $content . "'";
        $sql_query_excludes = '';
        for ($i = 0; $i < count($sql_excludes); ++$i) {
            $sql_query_excludes .= ' AND C.content_id != ' . $sql_excludes[$i];
        }

        $db = cmsms()->GetDb();
        $pref = CMS_DB_PREFIX;

        $sql_user_query = <<<EOS
SELECT C.content_id, prop_name, id_hierarchy
FROM {$pref}content C
JOIN {$pref}content_props P
ON C.content_id = P. content_id
$sql_query_where
$sql_content_filter
$sql_prefix_filter
$sql_limit_hierarchy
$sql_query_excludes
$sql_hierarchy
$active_mode
$showmenu_mode
EOS;
        $dbcontent = $db->Execute($sql_user_query);

        if ($db->ErrorNo()) {
            if ($dbcontent) $dbcontent->Close();
            throw new RuntimeException('DB error: ' . $db->ErrorMsg());
        }

        if ($dbcontent->RecordCount() >= 1) {
            $dbcontent->Close();
            return ceil(($dbcontent->RecordCount() - $offset) / $count);
        }

        if ($dbcontent) $dbcontent->Close();
        return 0;
    }

    /**
     * Provide the hierarchy level of a specific page (by content_id)
     *
     * @param $id
     *
     * @return int
     * @throws RuntimeException
     */
    public static function get_current_level($id)
    {
        $db = cmsms()->GetDb();
        $query = 'SELECT id_hierarchy FROM ' . CMS_DB_PREFIX . 'content WHERE content_id = ?';
        $dbhierarchy = $db->GetOne($query, [$id]);

        if ($db->ErrorNo()) {
            throw new RuntimeException('DB error: ' . $db->ErrorMsg());
        }

        $current_level = substr_count($dbhierarchy, '.');
        return $current_level + 1;
    }

    /**
     * Get TBA
     * db table CMS_DB_PREFIX content is aliased as C in sql command
     * db table CMS_DB_PREFIX content_props is aliased as P in sql command
     *
     * @param string $content
     * @param string $sql_limit_hierarchy Default ''
     * @param string $sql_prefix_filter Default ''
     * @param string $content_filter Default ''
     * @param int $count Default 1 UNUSED
     * @param int $offset Default 0 UNUSED
     * @param array $sql_excludes Default []
     * @param $sql_hierarchy Default ''
     *
     * @return array
     * @throws RuntimeException
     */
    public static function get_matching_pages(
        $content,
        $sql_limit_hierarchy = '',
        $sql_prefix_filter = '',
        $content_filter = '',
        $count = 1,
        $offset = 0,
        $sql_excludes = [],
        $sql_hierarchy = ''
    ) {
        $sql_content_filter = '';

        if ('' !== $content_filter) {
            // full-text search on texty column(s) having a full-text index
            $sql_content_filter = "AND MATCH(content) AGAINST('+" . $content_filter . "' IN BOOLEAN MODE)";
        }

        $sql_query_where = "AND prop_name='" . $content . "' ";
        $sql_query_excludes = '';
        // TODO C.content_id NOT IN( implode(',', -suitably cleaned-$sql_excludes))
        for ($i = 0; $i < count($sql_excludes); ++$i) {
            $sql_query_excludes .= ' AND C.content_id != ' . $sql_excludes[$i];
        }

        $db = cmsms()->GetDb();
        $pref = CMS_DB_PREFIX;

        $sql_filter_query = <<<EOS
SELECT C.content_id, prop_name, id_hierarchy
FROM {$pref}content C JOIN {$pref}content_props P
ON C.content_id = P.content_id
$sql_query_where
$sql_content_filter
$sql_prefix_filter
$sql_limit_hierarchy
$sql_query_excludes
$sql_hierarchy
EOS;
        $dbfilter = $db->Execute($sql_filter_query);

        if ($db->ErrorNo()) {
            if ($dbfilter) $dbfilter->Close();
            throw new RuntimeException('DB error: ' . $db->ErrorMsg());
        }

        $filter_ids = [];
        while ($dbfilter && $dbfilterrow = $dbfilter->FetchRow()) {
            $filter_ids = self::id_to_array($filter_ids, $dbfilterrow['content_id']);
        }

        if ($dbfilter) $dbfilter->Close();
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
     * @param string $sort_by
     * @param int $flag >= 0 If 0, the parameter is the first
     *  and thus the mySQL command ORDER BY will be added to the string
     * @param mixed $order string | null
     *
     * @return string
     */
    public static function sql_sort_param($sort_by, $flag, $order)
    {
        $flag = (int)$flag;
        if ($flag == 0) {
            $sql_sort_param = ' ORDER BY ';
        } else {
            $sql_sort_param = '';
        }

        // User-friendly selection of table column for sorting
        switch (strtolower((string)$sort_by)) {
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

        // checks if the sorting order is set to from bottom to top,
        // if not, it will set it to the reverse
        if (strcasecmp('up', (string)$order) == 0) {
            $sql_sort_param .= ' ASC ';
        } else {
            $sql_sort_param .= ' DESC ';
        }

        // If >= 1 the parameter is not the first
        // and will be separated by a comma from the next one
        if ($flag >= 1) {
            $sql_sort_param = ', ' . $sql_sort_param;
        }

        return $sql_sort_param;
    }

    /**
     * Strip out Smarty or HTML (incl JS and CSS)
     *
     * @param string $content string to be sanitized
     * @param string $mode 'html' and 'smarty' are recognised here
     *
     * @return string
     */
    public static function strip_out($content, $mode)
    {
        if ('html' == $mode) {
            $htmlexpressions = [
              '@<script[^>]*?>.*?</script>@si',  // Strip out javascript
              '@<style[^>]*?>.*?</style>@siU',   // Strip style tags properly
              '@<[\/\!]*?[^<>]*?>@si',           // Strip out HTML tags
              '@<![\s\S]*?--[ \t\n\r]*>@'        // Strip multi-line comments including CDATA
            ];
            $ret = preg_replace($htmlexpressions, ['','','',''], $content);
        } elseif ('smarty' == $mode) {
            $ret = preg_replace('@{[^}]*?}@', '', $content); // too bad if other delimiter(s) used!
        } else {
            return $content;
        }
        return $ret;
    }

    /**
     *
     * @param string $subdir
     * @param string $q_var Default 'fl'
     *
     * @return string
     */
    public static function recreate_UrlSmart($subdir, $q_var = 'fl')
    {
        $subdir = rtrim($subdir, '/');
        $get_params = (isset($_GET)) ? $_GET : [];
        $naked_uri = self::get_naked_uri();

        if (isset($get_params[$q_var]) && 0 !== \strpos($subdir, '..')) {
            $subdir1 = preg_replace('/\.{2}(\x2F)$/', '', \urldecode($get_params[$q_var]));
            $subdir = preg_replace('/[^\x2F]+(\x2F)$/', '', $subdir1);

            if (empty($subdir) || $subdir == $get_params[$q_var]) {
                unset($get_params[$q_var]);
            } else {
                $get_params[$q_var] = $subdir;
            }
        } else {
            $get_params[$q_var] = $subdir;
        }

        $smarturl = $naked_uri;

        if (!empty($get_params)) {
            $smarturl .= '?' . http_build_query($get_params);
        }

        return $smarturl;
    }

    public static function get_naked_uri()
    {
        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

        return $uri_parts[0];
    }

    /**
     *
     * @param string $sub_dir
     * @param string $q_var Default 'fl'
     *
     * @return string
     */
    public static function recreate_Url($sub_dir, $q_var = 'fl')
    {
        $sub_dir = rtrim($sub_dir, '/');
        $get_params = (isset($_GET)) ? $_GET : [];
        $naked_uri = self::get_naked_uri();

        if (isset($get_params[$q_var])) {
            if (false !== strpos($sub_dir, '..')) {
// revisit this
                $sub_dir_1 = preg_replace('/\.{2}(\x2F)$/', '', $get_params[$q_var]);
                $sub_dir = preg_replace('/[^\x2F]+(\x2F)$/', '', $sub_dir_1);
//
                if (empty($sub_dir)) {
                    unset($get_params[$q_var]);
                } elseif ($sub_dir === $sub_dir_1) {
                    $tmp = explode('/', $sub_dir);
                    unset($tmp[count($tmp) - 1]);
                    $get_params[$q_var] = implode('/', $tmp);
                } elseif ($sub_dir === $get_params[$q_var]) {
                    $tmp = explode('/', $sub_dir);
                    unset($tmp[count($tmp) - 1]);
                    $get_params[$q_var] = implode('/', $tmp);
                } else {
                    $get_params[$q_var] = $sub_dir;
                }
            } else {
                $get_params[$q_var] = self::join_uri($get_params[$q_var], $sub_dir);
            }
        } else {
            $get_params[$q_var] = $sub_dir;
        }

        $url = $naked_uri;

        if (empty($get_params[$q_var])) {
            unset($get_params[$q_var]);
        }

        if (!empty($get_params)) {
            if (empty($get_params[$q_var]) && null !== $get_params[$q_var]) {
                unset($get_params[$q_var]);
            }
            $url .= '?' . http_build_query($get_params);
        }

        return $url;
    }

    /**
     *
     * @param string $uri
     *
     * @return string
     */
    public static function uri_2_path($uri)
    {
        return strtr(trim($uri, ' /'), '/', DIRECTORY_SEPARATOR);
    }

    /**
     *
     * @param varargs $args
     *
     * @return string
     */
    public static function join_uri(...$args)
    {
        $args = array_map(static function($srt) {
            return trim($srt, ' /');
            }, $args);
        $args = array_filter($args);

        return implode('/', $args);
    }

    /**
     *
     * @param array $params
     *
     * @return array
     */
    public static function read_dl_db($params)
    {
        extract($params, EXTR_OVERWRITE);
        /** @var string $folder in scope from extract */
        /** @var string $dl_counter_db_dir in scope from extract */
        /** @var string $dl_counter_db_fn in scope from extract */
        $db_dn = (empty($dl_counter_db_dir)) ? $folder : $dl_counter_db_dir;
        $db_fn = (empty($dl_counter_db_fn)) ? '.counter_db' : $dl_counter_db_fn;
        $db_full_fn = cms_join_path($db_dn, $db_fn);

        if (file_exists($db_full_fn)) {
            $fo = fopen($db_full_fn, 'rb');
            flock($fo, LOCK_SH, $waitIfLocked);
            $str = @file_get_contents($db_full_fn);
            flock($fo, LOCK_UN);
            fclose($fo);
        } else {
            $str = '';
        }

        $tmp = explode(PHP_EOL, $str);
        $db = [];

        foreach ($tmp as $line) {
            if (!empty($line)) {
                $t = explode('::', $line);
                $db[$t[0]] = $t[1];
            }
        }

        return $db;
    }

    /**
     *
     * @param string $full_filename
     * @param string $filename UNUSED
     * @param array|null $params
     */
    public static function do_count_download($full_filename, $filename, $params)
    {
        $db = self::read_dl_db($params);
        extract($params, EXTR_OVERWRITE);
        /** @var string $folder in scope from extract */
        /** @var string $dl_counter_db_dir in scope from extract */
        /** @var string $dl_counter_db_fn in scope from extract */
        $db_dn = (empty($dl_counter_db_dir)) ? $folder : $dl_counter_db_dir;
        $db_fn = (empty($dl_counter_db_fn)) ? '.counter_db' : $dl_counter_db_fn;
        $db_full_fn = cms_join_path($db_dn, $db_fn);

        $key = $full_filename;

        if (isset($db[$key])) {
            (int)$db[$key]++;
        } else {
            $db[$key] = 1;
        }

        $str = '';

        foreach ($db as $k => $v) {
            $str .= $k . '::' . $v . PHP_EOL;
        }

        file_put_contents($db_full_fn, $str, LOCK_EX);
    }

    /**
     *
     * @param string $full_filename
     * @param string $filename
     * @param array|null $params
     */
    public static function do_download($full_filename, $filename, $params)
    {
        $handlers = ob_list_handlers();
        for ($cnt = 0, $cntMax = count($handlers); $cnt < $cntMax; ++$cnt) {
            ob_end_clean();
        }
        // grab the params if they exist
        extract($params, EXTR_OVERWRITE);
        /** @var bool $count_downloads in scope from extract */
        $count_downloads = !empty($count_downloads);

        //some mime types
        //TODO use FileTypeHelper stuff
        $mime_types = [
          '.avi' => 'video/avi',
          '.bmp' => 'image/bmp',
          '.webp' => 'image/webp',
          '.csv' => 'text/csv',
          '.doc' => 'application/msword',
          '.exe' => 'application/octet-stream',
          '.gif' => 'image/gif',
          '.html' => 'text/html',
          '.ico' => 'image/x-icon',
          '.jpg' => 'image/jpeg',
          '.mov' => 'video/quicktime',
          '.pdf' => 'application/pdf',
          '.png' => 'image/png',
          '.txt' => 'text/plain',
          '.rtf' => 'text/richtext',
          '.swf' => 'application/x-shockwave-flash',
          '.xls' => 'application/excel'
        ];

        if (file_exists($full_filename)) {
            if ($count_downloads) {
                self::do_count_download($full_filename, $filename, $params);
            }

            $arr = explode('.', $full_filename);
            $file_ext = '.' . end($arr);
            $mime_type = (!empty($mime_types[$file_ext])) ? $mime_types[$file_ext] : 'application/x-binary';

            // set headers accordingly
            header('Content-type: ' . $mime_type);
            header('Content-Description: File Transfer');
            header('Content-Length: ' . filesize($full_filename));
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($full_filename);
            exit;
        }
    }

    /**
     * doesn't seem to work on most pdf files, so...
     * we'll likely ditch this
     *
     * @param string $file
     *
     * @return array
     */
    public static function get_pdf_info($file)
    {
        $title = '';
        $author = '';

        $description = '';
        if (is_file($file)) {
            $handle = fopen($file, 'rb');
            while (!feof($handle)) {
                $buffer = fgets($handle, 1024);

                if (0 === strpos($buffer, '/Title (')) {
                    $title = str_replace(
                        '\\',
                        '',
                        'UTF-8' === mb_detect_encoding($buffer, 'auto', true)
                        ? substr(trim($buffer), 8, -1)
                        : mb_convert_encoding(
                            substr(trim($buffer), 8, -1),
                            'UTF-8'
                        )
                    );
                }

                if (0 === strpos($buffer, '/Author (')) {
                    $author = str_replace(
                        '\\',
                        '',
                        'UTF-8' === mb_detect_encoding($buffer, 'auto', true)
                        ? substr(trim($buffer), 9, -1)
                        : mb_convert_encoding(
                            substr(trim($buffer), 9, -1),
                            'UTF-8'
                        )
                    );
                }

                if (0 === strpos($buffer, '/Subject (')) {
                    $description = str_replace(
                        '\\',
                        '',
                        'UTF-8' === mb_detect_encoding(
                            $buffer,
                            'auto',
                            true
                        ) ? substr(
                            trim($buffer),
                            10,
                            -1
                        ) : mb_convert_encoding(substr(trim($buffer), 10, -1), 'UTF-8')
                    );
                }

                if ($title && $author && $description) {
                    fclose($handle);
                    break;
                }
            }

            if (!$title || !$author || !$description) {
                fclose($handle);
            }
        }

        return [
          'title' => $title,
          'author' => $author,
          'description' => $description
        ];
    }

    /**
     * Return a list of files in the specified directory, recursively or not
     *
     * @param string $directory
     * @param string $root_path
     * @param string $folder
     * @param string $sub_dir
     * @param bool $show_hidden Default false
     * @param bool $download Default false
     * @param mixed $serve_fn Default null
     * @param mixed $params Default null
     * @param bool $recursive Default false
     *
     * @return array
     * @throws RuntimeException
     */
    public static function getDirectoryFilesInfo($directory, $root_path, $folder, $sub_dir, $show_hidden = false, $download = false, $serve_fn = null, $params = null, $recursive = false)
    {
        $files = [];
//      $file_types = [];
//      $file_dates = [];
//      $file_sizes = [];

        $handle = opendir($directory);

        if (false === $handle) {
            throw new RuntimeException("Unable to open directory: $directory");
        }

        while ($entry = readdir($handle)) {
            if ($entry !== '.' && $entry !== '..') {
                // Prevent showing hidden files unless specified TODO robust detection of all hidden files
                if (!$show_hidden && $entry[0] == '.') {
                    continue;
                }

                $full_name = cms_join_path($root_path, self::uri_2_path(self::join_uri($folder, $sub_dir, $entry)));

                if ($download && $serve_fn === $entry) {
                    self::do_download($full_name, urlencode($entry), $params);
                }

                $file_info = [
                    'name' => $entry,
                    'full_name' => $full_name,
                    'type' => filetype($full_name),
                    'date' => filemtime($full_name),
                    'size' => sprintf('%u', filesize($full_name)),
                    'children_count' => 0
                ];

                if (is_dir($full_name) && $recursive) {
                    $sub_dir_info = self::getDirectoryFilesInfo($full_name, $root_path, $folder, self::join_uri($sub_dir, $entry), $show_hidden, $download, $serve_fn, $params, true);
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
