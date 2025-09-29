<?php
/**
 * Smarty plugin: files_list
 *
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */

use SmartyExt\utils;

function smarty_function_files_list($params, $template)
{
    // initialization and defaults
    $file_ext = '';
    $download = false;
    $q_var = (!empty($params['query_var'])) ? $params['query_var'] : 'fl'; // sets the query variable
    $folder = isset($params['folder']) ? rtrim($params['folder'], '/') : 'uploads';  // folder to read from
    $delimiter = (!empty($params['delimiter'])) ? $params['delimiter'] : '_';
    $date = !isset($params['date']) || (bool)$params['date'];
    $show_hidden = isset($params['show_hidden']) || (bool)$params['show_hidden'];
    $count_downloads = isset($params['count_downloads']) || (bool)$params['count_downloads'];
    $show_size = !isset($params['show_size']) || (bool)$params['show_size'];
    $strip_extension = isset($params['strip_extension']) && (bool)$params['strip_extension'];
    $dl_counter_db_dir = (!empty($params['counter_db_dir'])) ? $params['counter_db_dir'] : '';
    $dl_counter_db_fn = (!empty($params['dl_counter_db_fn'])) ? $params['dl_counter_db_fn'] : '.counter_db';
    $file_extension = (!empty($params['file_extension'])) ? $params['file_extension'] : '';
//  $size = (!empty($params['size'])) ? $params['size'] : 'Size';
    $dateformat = (!empty($params['dateformat'])) ? $params['dateformat'] : 'Y-m-d H:i:s';
    $browse_sub_dirs = !isset($params['browse_sub_dirs']) || (bool)$params['browse_sub_dirs'];
    $max_entries = isset($params['max_entries']) ? (int)$params['max_entries'] : 0; // 0 = all
    $max_entries = 'all' === $max_entries ? 0 : (int)$params['max_entries']; // 0 = all
    $thumbs_folder = (!empty($params['thumbs_folder'])) ? $params['thumbs_folder'] : 'thumbs';
    $obfuscate_download = !isset($params['obfuscate_download']) || (bool)$params['obfuscate_download']; // default = true
    $security_token = !isset($params['security_token']) ||
        (bool)$params['security_token']; // require a security token default = true
    $security_token_qv = (!empty($params['security_token_qv'])) ? $params['security_token_qv'] : '_st'; //TODO current version
    $root = !empty($params['root']);
    $sort = (!empty($params['sort'])) ? $params['sort'] : ''; // Check if user has specified sort order
    $recursive = !empty($params['recursive']);

    $serve_fn = '';
    $token = '';
    $files_db = [];

    // root param true makes obfuscate true
    $obfuscate_download = $obfuscate_download || $root;

    $get_params = (!empty($_GET)) ? $_GET : [];

    if ($security_token && $obfuscate_download) {
        // do we have a session token? use it, otherwise create one but disallow downloading on this passage
        if (!isset($_SESSION['utils::st'])) {
            $st = bin2hex(random_bytes(16)); // for PHP 5.3+ and relevant extension openssl_random_pseudo_bytes(16)
            $_SESSION['utils::st'] = $st;
        } else {
            $st = $_SESSION['utils::st'];
            $download = (isset($get_params[$security_token_qv]) && $st === $get_params[$security_token_qv]);
            $token = $download ? $get_params[$security_token_qv] : $st;
        }
    }

    // set some parameters needed for the counter
    $prms = [
      'count_downloads' => $count_downloads,
      'dl_counter_db_dir' => $dl_counter_db_dir,
      'dl_counter_db_fn' => $dl_counter_db_fn,
      'folder' => $folder
    ];

    if (!empty($get_params[$q_var])) {
        if ($download) {
            $serve_fn = urldecode($get_params[$q_var]);
            $t = explode('/', $serve_fn);
            $serve_fn = array_pop($t);
            $sub_dir = implode('/', $t);
        } else {
            $sub_dir_1 = urldecode($get_params[$q_var]);
            $sub_dir = str_replace('..', '', $sub_dir_1);
        }
    } else {
        $sub_dir = '';
    }

    $config = cmsms()->GetConfig();
    $root_path = $root ? '' : $config['root_path'];

//  $file_names = [];
//  $file_types = [];
//  $file_dates = [];
//  $file_sizes = [];

    $files_info = [
      'file_names' => '',
      'file_types' => '',
      'file_dates' => '',
      'file_sizes' => '',
      'f_count' => 0
    ];

    // done initialization

    $dir = cms_join_path($root_path, utils::uri_2_path(utils::join_uri($folder, $sub_dir)));
/*
    if (!$handle = opendir($dir)) {
      $messages['error'][] = 'could not read ' . $get_params[$q_var] . '!';
      $handle             = opendir($folder);
      $get_params[$q_var] = '';
      $sub_dir            = '';
    }

    $f_count   = 0;
    $max_count = 1;

    while ($handle && ( FALSE !== ($entry = readdir($handle)))) {
      if ('.' !== $entry && '..' !== $entry) {
        // prevent showing hidden files unless we want to
        if (!$show_hidden && \startswith($entry, '.') ) { continue; }

        $f_count++;
        $full_name = cms_join_path($root_path, utils::uri_2_path(utils::join_uri($folder, $sub_dir, $entry)));

        if ($download && $serve_fn === $entry) {
          utils::do_download($full_name, urlencode($entry), $prms);
        }

        //Insert filename, filetype, filetime and filesize into arrays
        $file_names[] = $entry;
        $file_types[] = filetype($full_name);
        $file_dates[] = filemtime($full_name);
        $file_sizes[] = sprintf( '%u', filesize($full_name) );
      }
    }

    closedir($handle);
    $f_count--;
*/
    // Get the files information
    try {
        $files_info = utils::getDirectoryFilesInfo($dir, $root_path, $folder, $sub_dir, $show_hidden, $download, $serve_fn, $prms, $recursive);
    } catch(Exception $e) {
        $messages['error'][] = $e->getMessage();
        $get_params[$q_var] = '';
        $sub_dir = '';

        try {
            $files_info = utils::getDirectoryFilesInfo($folder, $root_path, $folder, $sub_dir, $show_hidden, $download, $serve_fn, $prms, $recursive);
        } catch(Exception $e) {
            $messages['error'][] = $e->getMessage();
        }
    }
/*
    $abc123456 = compact('files_info');
    echo('<br>:::::::::::::::::::::<br>');
    debug_display($abc123456);
    echo('<br>' . __FILE__ . ' : (' . __CLASS__ . ' :: ' . __FUNCTION__ . ') : ' . __LINE__ . '<br>');
    die('<br>RIP!<br>');
*/
    // Access the file information
    $file_names = $files_info['file_names'];
    $file_types = $files_info['file_types'];
    $file_dates = $files_info['file_dates'];
    $file_sizes = $files_info['file_sizes'];
    $f_count = $files_info['f_count'];

    $abc123456 = compact('file_names', 'files_info');
    echo('<br>:::::::::::::::::::::<br>');
    debug_display($abc123456);
    echo('<br>' . __FILE__ . ' : (' . __CLASS__ . ' :: ' . __FUNCTION__ . ') : ' . __LINE__ . '<br>');
    die('<br>RIP!<br>');

    $file_shortnames = array_map('strtolower', $file_names);

    // Change file sort order if needed
    $sort .= '  ';

    if (0 === stripos($sort, 'd')) {
        if (0 === stripos($sort, 'dd')) {
            array_multisort($file_dates, SORT_DESC, $file_types, $file_shortnames, $file_names);
        } else {
            array_multisort($file_dates, SORT_ASC, $file_types, $file_shortnames, $file_names);
        }
    } elseif ('s' === strtolower($sort[0])) {
        if (0 === stripos($sort, 'sd')) {
            array_multisort($file_sizes, SORT_DESC, $file_types, $file_shortnames, $file_names);
        } else {
            array_multisort($file_sizes, SORT_ASC, $file_types, $file_shortnames, $file_names);
        }
    } elseif (0 === stripos($sort, 'n')) {
        if (0 === stripos($sort, 'nd')) {
            array_multisort($file_shortnames, SORT_DESC, $file_names, $file_types);
        } else {
            array_multisort($file_shortnames, SORT_ASC, $file_names, $file_types);
        }
    } else {
        array_multisort($file_types, $file_shortnames, $file_names);
    }

    // messages to display on the frontend
    $messages = [];
    $files_list = [];

    if (!$max_entries) {
        $max_entries = $f_count + 1;
    } // 0 == all

    if (!empty($get_params[$q_var]) && ($max_count <= $max_entries)) {
        $entry['name'] = '..';
        $entry['type'] = 'parent_dir';
        $entry['url'] = utils::recreate_Url(utils::join_uri($sub_dir, '..'));
        $entry['file_ext'] = $file_ext;
        $files_list[] = $entry;
    }
    //TODO use FileTypeHelper
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
    //TODO ibid
    $image_ext_list = [
      'ico',
      'gif',
      'jpg',
      'png'
    ];

    $total = 0;
    $file_count = 0;
    $dir_count = 0;
    $dl_db = [];

    if ($count_downloads) {
        $dl_db = utils::read_dl_db($prms);
    }

    for ($i = 0; $i <= $f_count; ++$i) {
        if ($max_count <= $max_entries) {
            $full_name = cms_join_path($root_path, utils::uri_2_path(utils::join_uri($folder, $sub_dir, $file_names[$i])));
            $directory = is_dir($full_name);

            if (!$directory) {
                $arr = explode('.', $file_names[$i]);
                $file_ext = end($arr); //TODO if hidden file
                ++$file_count;
            } else {
                $file_ext = 'Directory';
                ++$dir_count;
            }

            $extension_tmp = trim($file_extension);
            $replace_extension = explode(',', $extension_tmp);
            $clean_name = str_replace($delimiter, ' ', $file_names);
            $pretty_clean_name = str_replace($defaultextension, ' ', $clean_name);
            $super_clean_name = str_replace($replace_extension, ' ', $pretty_clean_name);
/*
            $cleaniconurl = str_replace($delimiter, '-', $file_names);
            $prettycleaniconurl = str_replace($defaultextension, $iconextension, $cleaniconurl);
            $autoiconpath = 'uploads/images/appicons/' . $file_names;
            $iconpath = 'uploads/images/appicons/' . $prettycleaniconurl[$i];
            $basePath = 'uploads/images/appicons/';
*/
            if ($directory && $browse_sub_dirs) {
                $href = utils::recreate_Url($file_names[$i], $q_var);
            } else {
                $href = utils::join_uri($folder, $sub_dir, $file_names[$i]);
                $thumbs_path = utils::join_uri($folder, $sub_dir, $thumbs_folder, $file_names[$i]);
            }

            if ($browse_sub_dirs || !is_dir($full_name)) {
                if ($strip_extension && !$directory) {
                    $entry['name'] = $super_clean_name[$i];
                    $entry['type'] = 'file';
                    $entry['sub_type'] = (in_array($file_ext, $image_ext_list) ? 'image' : $file_ext);
                    $entry['thumbs_url'] = 'image' === $entry['sub_type'] ? $thumbs_path : '';

                    if ($obfuscate_download) {
                        $uri_params = [
                          $security_token_qv => $token,
                          $q_var => utils::join_uri($sub_dir, $file_names[$i])
                        ];
                        $url = utils::get_naked_uri() . '?' . http_build_query($uri_params);
                        $entry['url'] = $url;
                    } else {
                        $entry['url'] = $href;
                    }

                    $entry['file_ext'] = $file_ext;
                    $entry['title'] = 'Link to download the ' . $super_clean_name[$i] . ' document in ' . $file_ext .
                                         ' format';
                } elseif (!$directory) {
                    $entry['name'] = $super_clean_name[$i];
                    $entry['type'] = 'file';
                    $entry['sub_type'] = (in_array($file_ext, $image_ext_list) ? 'image' : $file_ext);
                    $entry['thumbs_url'] = 'image' === $entry['sub_type'] ? $thumbs_path : '';

                    if ($obfuscate_download) {
                        $uri_params = [
                          $security_token_qv => $token,
                          $q_var => utils::join_uri($sub_dir, $file_names[$i])
                        ];

                        $url = utils::get_naked_uri() . '?' . http_build_query($uri_params);
                        $entry['url'] = $url;
                    } else {
                        $entry['url'] = $href;
                    }

                    $entry['file_ext'] = $file_ext;
                    $entry['title'] = 'Link to download the ' . $super_clean_name[$i] . ' document in ' . $file_ext .
                                         ' format';
                } else {
                    $tn = is_array($super_clean_name) ? $super_clean_name[$i] : '';
                    $entry['name'] = $tn;
                    $entry['type'] = 'dir';
                    $entry['url'] = $href;
                    $entry['file_ext'] = $file_ext;
                    $entry['title'] = 'Link to the ' . $tn . ' ' . $file_ext;
                }

                if (!$directory && $count_downloads) {
                    if (isset($dl_db[$full_name])) {
                        $entry['download_count'] = $dl_db[$full_name];
                    } else {
                        $entry['download_count'] = 0;
                    }
                }

                if ($show_size && !$directory) {
                    if (sprintf('%u', filesize($full_name) < 1024)) {
                        $entry['size'] = sprintf('%u', filesize($full_name)) . ' B';
                    } elseif (sprintf('%u', filesize($full_name) < 1000000)) {
                        $entry['size'] = sprintf('%u', filesize($full_name) / 1024) . ' KB';
                    } elseif (sprintf('%u', filesize($full_name) < 1000000000)) {
                        $entry['size'] = sprintf('%01.1f', filesize($full_name) / 1048576) . ' MB';
                    } else {
                        $entry['size'] = sprintf('%01.1f', filesize($full_name) / 1073741824) . ' GB';
                    }

                    $total += (int)sprintf('%u', filesize($full_name));
                }

                if ($date && $directory) {
                    $entry['date'] = date($dateformat, filemtime($full_name));
                } elseif ($date) {
                    $entry['date'] = date($dateformat, filemtime($full_name));
                }

                ++$max_count;

                $files_list[] = $entry;
            }
        }
    }

    if (sprintf('%u', (int)$total < 1024)) {
        $total_text = sprintf('%u', (int)$total) . ' B';
    } elseif (sprintf('%u', (int)$total < 1000000)) {
        $total_text = sprintf('%u', (int)$total / 1024) . ' KB';
    } elseif (sprintf('%u', (int)$total < 1000000000)) {
        $total_text = sprintf('%01.1f', (int)$total / 1048576) . ' MB';
    } else {
        $total_text = sprintf('%01.1f', (int)$total / 1073741824) . ' GB';
    }

    $files_list_obj = new stdClass();
    $files_list_obj->messages = $messages; // to be implemented
    $files_list_obj->files = $files_list;
    $files_list_obj->total_size_text = $total_text;
    $files_list_obj->total_size = (int)$total;
    $files_list_obj->file_count = $file_count;
    $files_list_obj->dir_count = $dir_count;
    $files_list_obj->items_count = $dir_count + $file_count;

    $assign = !empty($params['assign']) ? trim($params['assign']) : 'files_list';
    $template->assign($assign, $files_list_obj);
    return '';
}
