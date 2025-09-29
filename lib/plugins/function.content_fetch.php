<?php
/**
 * Smarty plugin: content_fetch
 *
 * Copyright (C) 2012 CMS Made Simple Foundation Inc.
 * License GNU General Public License V.2 or later
 */
use SmartyExt\utils;

function smarty_function_content_fetch($params, $template)
{
    // Output will always be assigned to a variable
    $assign = (!empty($params['assign'])) ? trim($params['assign']) : 'content_fetch';

    // Check if parent page data should be included in  results entries
    $parents = !empty($params['parents']);

    // Check if user data should be looked up for each entry
    $users = !empty($params['users']);

    // Set locale if required (e.g. dates/times). Locale is reset to default after script completed
    if (!empty($params['cdlocale'])) {
        setlocale(LC_TIME, $params['cdlocale']);
    } else {
        setlocale(LC_TIME, '');
    }

    // Check for which content block to pick,
    // if not set, pick default "content_en"
    $block = (!empty($params['block_name'])) ? $params['block_name'] : 'content_en';

    $sql_content_query_having = " HAVING prop_name='" . $block . "' ";

    // Set a date format for Content entries
    $dateformat = (!empty($params['dateformat'])) ? $params['dateformat'] : '%A, %e %B %Y';

    //  Check if start id is set,
    // if so, tag will ony return pages
    // that have this id or are located below
    if (isset($params['start_id'])) {
        $st = $params['start_id'];

        $sql_content_query_limit_sitearea = " AND (
(id_hierarchy LIKE ('$st')) OR
(id_hierarchy LIKE ('$st.%')) OR
(id_hierarchy LIKE ('%.$st.%')) OR
(id_hierarchy LIKE ('%.$st'))
)";

        $start_id = (int)$params['start_id'];
    } else {
        $sql_content_query_limit_sitearea = '';
        $start_id = -1;
    }

    // Check if parent page data
    // should be included in results entries
    if (isset($params['depth'])) {
        $depth = explode(',', $params['depth']);

        // Define the starting hierarchy
        if (-1 === $depth[0]) {
            $start_depth = utils::get_current_level($start_id);
        } elseif (0 === (int)$depth[0]) {
            $start_depth = 1;
        } else {
            $start_depth = (int)$depth[0];
        }

        // the REGEXP snippets used
        $snip1 = "'^[0-9]{1,10}";
        $snip2 = "\.[0-9]{1,10}";

        // Create Query String for first hierarchy level
        if ($start_depth >= 1) {
            $buffer1 = $snip1;
            for ($i = 1; $i <= $start_depth - 1; ++$i) {
                $buffer1 .= '' . $snip2;
            }
        }

        $sql_content_query_limit_hierarchy = ' AND (id_hierarchy REGEXP ' . $buffer1 . "$' ";

        // Create Query String for remaining hierarchy levels
        $max_depth = $start_depth + (int)$depth[1];

        for ($i = $start_depth; $i <= $max_depth - 1; ++$i) {
            $buffer2 = $buffer1;
            $distance = $i - $start_depth;

            $buffer2 .= str_repeat('' . $snip2, $distance + 1);

            $sql_content_query_limit_hierarchy .= ' OR id_hierarchy REGEXP ' . $buffer2 . "$' ";
        }
        $sql_content_query_limit_hierarchy .= ')';
    } else {
        $sql_content_query_limit_hierarchy = '';
    }

    // Check if first sorting item-sort-order is set
    // otherwise set to default , which is upwards
    $first_sort_order = (!empty($params['first_sort_order'])) ? $params['first_sort_order'] : 'up';

    // Check if second sorting item-sort-order is set
    // otherwise set to default , which is up
    $second_sort_order = (!empty($params['second_sort_order'])) ? $params['second_sort_order'] : 'up';

    //  Check for first sort parameter,
    // if not set, first sort parameter will be the hierarchy date of the page
    if (isset($params['first_sort'])) {
        $sql_content_query_first_sort = utils::sql_sort_param($params['first_sort'], 0, $first_sort_order);
    } else {
        $sql_content_query_first_sort = utils::sql_sort_param('hierarchy', 0, 'up');
    }

    // Check for second sort parameter,
    // if not set, second sort parameter will be the creation date of the page
    if (isset($params['second_sort'])) {
        $sql_content_query_second_sort = utils::sql_sort_param($params['second_sort'], 2, $second_sort_order);
    } else {
        $sql_content_query_second_sort = '';
    }

    // Check for parameter exclude,
    // if it contains ID(s) seperated by comma,
    // it will create an array of IDs we can exclude later on
    $sql_content_query_excludes = '';

    if (isset($params['exclude'])) {
        $exclude_ids = explode(',', $params['exclude']);
        for ($i = 0; $i <= count($exclude_ids) - 1; ++$i) {
            $sql_content_query_excludes .= 'AND ' . CMS_DB_PREFIX . 'content.content_id != ' . $exclude_ids[$i] . ' ';
        }
    } else {
        $exclude_ids[0] = -1;
    }

    // Limit the query results to a singe content_id
    // by adding a condition to the query statement
    // if not set, do not add anything to the query string
    if (isset($params['this_only'])) {
        $sql_content_query_this_only = 'AND ' . CMS_DB_PREFIX . 'content.content_id = ' . $params['this_only'];
    } else {
        $sql_content_query_this_only = '';
    }

    // Check for parameter these_only, if it contains ID(s) seperated by comma,
    // it will create an array of IDs we can include exclusively
    $sql_content_filter = '';
    if (isset($params['these_only'])) {
        $include_ids = explode(',', $params['these_only']);
        $sql_content_query_includes = '(';
        for ($i = 0; $i <= count($include_ids) - 1; ++$i) {
            $sql_content_filter .= 'OR ' . CMS_DB_PREFIX . 'content.content_id != ' . $include_ids[$i] . '  ';
        }
        $sql_content_filter .= ')';
    } else {
        $include_ids[0] = -1;
    }

    // If set to compile, smarty tags in the content will be compiled
    //set to false per default
    // !WARNING! MAY CAUSE RECURSION AND ULTIMATELY OUT OF MEMORY ERROR
    $c_smarty_modes_list = ['neutral', 'compile', 'strip'];
    $c_smarty = (!empty($params['do_smarty'])) ? $params['do_smarty'] : 'neutral';
    if (!in_array($c_smarty, $c_smarty_modes_list)) {
        $c_smarty = 'neutral';
    }

    // If set to strip, HTML tags in the content will be removed (inluding JS, CSS and HTML)

    if (isset($params['html']) && ('strip' === $params['html'])) {
        $c_html = 'strip';
    } else {
        $c_html = 'neutral';
    }

    // Check and set how the "active" page flag is handled
    $f_active = (!empty($params['active'])) ? $params['active'] : 'active';

    switch($f_active) {
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

    // Check and set how the "shown_in_menu" or "show_in_menu" page flag is handled
    // "show_in_menu" is deprecated, so we only use one of the params,
    // priority to new "shown_in_menu"
    $f_shown_in_menu = (!empty($params['show_in_menu'])) ? $params['show_in_menu'] : 'show';
    $f_shown_in_menu = (!empty($params['shown_in_menu'])) ? $params['shown_in_menu'] : $f_shown_in_menu;

    switch($f_shown_in_menu) {
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

    // Set the first item to show,
    // if not set, it will begin with the first item shown
    if (isset($params['limit_start'])) {
        $limit_start = $params['limit_start'];
        $limit_start_orig = $params['limit_start'];
    } else {
        $limit_start = 0;
        $limit_start_orig = 0;
    }

    // Set the number of items to show,
    // if no parameter is set, limit to 1000 results
    if (isset($params['limit_count'])) {
        $limit_count = $params['limit_count'];
        $limit_count_orig = $params['limit_count'];
    } else {
        $limit_count = 1000;
        $limit_count_orig = 1000;
    }

    // if parameter extension is used,
    // explode prop_names and store them in an array
    if (isset($params['extensions'])) {
        $extensions = explode(',', $params['extensions']);
        $count_extensions = count($extensions);
        $sql_content_query_props = '';

        for ($i = 0; $i <= $count_extensions - 1; ++$i) {
            $sql_content_query_props .= " OR prop_name='" . $extensions[$i] . "' " . ' ';
        }

        $limit_count *= ($count_extensions + 1);

        if ($limit_start >= 1) {
            $limit_start = ($limit_start * ($count_extensions + 1)) - 1;
        }

        $extension = true;
    } else {
        $sql_content_query_props = '';
        $extension = false;
    }

    // Check how a stated prefix should be handled
    $sql_prefix_filter = '';
    $prefix_mode = (!empty($params['prefix_mode'])) ? $params['prefix_mode'] : 'neutral';

    // Check what prefixes should be handled
    if (isset($params['prefix'])) {
        $prefixes = explode(',', $params['prefix']);
        $count = count($prefixes);
        if ('force' === $prefix_mode) {
            $sql_prefix_filter = " AND content_alias REGEXP '^";
        }
        if ('hide' === $prefix_mode) {
            $sql_prefix_filter = " AND content_alias NOT REGEXP '^";
        }
        if ('neutral' !== $prefix_mode) {
            if ($count > 1) {
                $sql_prefix_filter .= '(';
            }
            for ($i = 0; $i <= $count - 1; ++$i) {
                $sql_prefix_filter .= '' . $prefixes[$i];

                if ($i < $count - 1) {
                    $sql_prefix_filter .= '|';
                }
            }

            if ($count > 1) {
                $sql_prefix_filter .= ")' ";
            } else {
                $sql_prefix_filter .= "' ";
            }
        }
    }

    // Check if parameter 'page' specifies a page to display,
    // (only makes sense when used with limit_count, which is the page size)
    if (isset($params['page']) && ('' !== $params['page'])) {
        $page_number = $params['page'];
        // There is no negative page or a page numbered zero
        if ($page_number <= 0) {
            $page_number = 1;
        }

        if (1 === $page_number) {
            // Page is one, we do not need to change anything
        } elseif (2 === $page_number) {
            // this is the second page,
            if (0 === $limit_start) {
                $limit_start = $limit_count;
            } else {
                $limit_start = $limit_start + $limit_count - 1;
            }
        } elseif ($page_number >= 3) {
            // and this any following page
            if (0 === $limit_start) {
                $limit_start = $limit_count * ($page_number - 1);
            } else {
                $limit_start = $limit_start + ($limit_count * ($page_number - 1)) - 1;
            }
        }

        $pager_info = new stdClass();
        $pager_info->current = $page_number;
        $pager_info->max = utils::get_max_page(
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

    // If start and count are not set,
    // ignore the output limit and give back everything,
    // else: assemble mysql limit statement
    if (isset($params['limit_start']) || isset($params['limit_count'])) {
        $sql_content_query_limit = ' LIMIT ' . $limit_start . ' , ' . $limit_count;
    } else {
        $sql_content_query_limit = '';
    }

    // Create FULLTEXT Search expression
    if ((isset($params['filter'])) && ('' !== $params['filter'])) {
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

        $filter_count = count($these_ids_array);

        if ($filter_count > 0) {
            $sql_content_filter = 'AND (';
        }

        for ($i = 0; $i <= $filter_count - 1; ++$i) {
            if ($i > 0) {
                $sql_content_filter .= 'OR ';
            } else {
                $sql_content_filter .= ' ';
            }

            $sql_content_filter .= '' . CMS_DB_PREFIX . "content_props.content_id = '" . $these_ids_array[$i] . "' ";
        }

        if ($filter_count > 0) {
            $sql_content_filter .= ')  ';
        }

        if (0 === $filter_count) {
            $sql_content_filter = ' AND ' . CMS_DB_PREFIX . 'content_props.content_id = -1 ';
        }
    } else {
        $sql_content_filter = '';
    }

    $pref = CMS_DB_PREFIX;

    // Merge all sql parts
    $sql_the_full_query = <<<EOS
SELECT
C.content_id,content_name,content_alias,menu_text,show_in_menu,
C.create_date,C.modified_date,owner_id,id_hierarchy,parent_id,
last_modified_by,active,P.prop_name,P.content
FROM {$pref}content C
JOIN {$pref}content_props P
ON C.content_id = P.content_id
$sql_content_filter
$sql_prefix_filter
$sql_content_query_limit_sitearea
$sql_content_query_limit_hierarchy
$sql_content_query_excludes
$sql_content_query_active
$sql_content_query_show
$sql_content_query_this_only
$sql_content_query_having
$sql_content_query_props
$sql_content_query_first_sort
$sql_content_query_second_sort
$sql_content_query_limit
EOS;

    $db = cmsms()->GetDb();
    // Execute sql command
    $dbresult = $db->Execute($sql_the_full_query);

    if ($db->ErrorNo() > 0) {
        if ($dbresult) { $dbresult->Close(); }
        throw new RuntimeException('DB error: ' . $db->ErrorMsg());
    }

//  if (!$dbresult) {
//      echo 'DB error: ' . $db->ErrorMsg() . "<br>";
//  }

    // Store database results in array of classes

    // if we use extension checking... we need an extra array
    if ($extension) {
        $content_props = [];
        $counter = 0;
    }

    $content_dump = [];
    $parent_ids = [];
    $user_ids = [];
    $dump_count = 0;

    while ($dbresult && $dbqueryresultrow = $dbresult->FetchRow()) {
        // Store the data of items with prop_name (=block-name) of parameter "block" into an array
        // this is our main content_dump item data
        if ($dbqueryresultrow['prop_name'] == $block) {
            $dump_item = new stdClass();
            $dump_item->content = new stdClass();
            $dump_item->parents = new stdClass();
            $dump_item->created = new stdClass();
            $dump_item->modified = new stdClass();
            $dump_item->item = $dump_count;

            $dump_item->content->id = $dbqueryresultrow['content_id'];
            $dump_item->content->alias = $dbqueryresultrow['content_alias'];
            $dump_item->content->title = $dbqueryresultrow['content_name'];
            $dump_item->content->menu = $dbqueryresultrow['menu_text'];
            $dump_item->content->show = $dbqueryresultrow['show_in_menu'];
            $dump_item->content->active = $dbqueryresultrow['active'];
            $dump_item->content->data = $dbqueryresultrow['content'];

            // See if the ID of this page's parent is already known
            // as a page for which we need to lookup name and alias later on
            if ($parents) {
                $parent_ids = utils::id_to_array($parent_ids, $dbqueryresultrow['parent_id']);
            }

            $dump_item->parents->id = $dbqueryresultrow['parent_id'];
            $dump_item->parents->alias = '';
            $dump_item->parents->title = '';
            $dump_item->parents->menu = '';

            if ($users) {
                $user_ids = utils::id_to_array($user_ids, $dbqueryresultrow['owner_id']);
                $user_ids = utils::id_to_array($user_ids, $dbqueryresultrow['last_modified_by']);
            }

            $dump_item->created->by = $dbqueryresultrow['owner_id'];
            $dump_item->created->date = CMSMS\strftime($dateformat, @strtotime($dbqueryresultrow['create_date']));
            $dump_item->modified->by = $dbqueryresultrow['last_modified_by'];
            $dump_item->modified->date = CMSMS\strftime($dateformat, @strtotime($dbqueryresultrow['modified_date']));
            $dump_item->extension = 0;
            $content_dump[] = $dump_item;
            ++$dump_count;
        } elseif ($extension && ('' != $dbqueryresultrow['content'])) {
            // If the content block is not our primary content block,
            // check if we should assign it to our buffer array, that stores all the other props
            $content_props[$counter]['content_id'] = $dbqueryresultrow['content_id'];
            $content_props[$counter]['content'] = $dbqueryresultrow['content'];
            $content_props[$counter]['prop_name'] = $dbqueryresultrow['prop_name'];
            ++$counter;
        }
    }
    if ($dbresult) { $dbresult->Close(); }

    // Get the extra data into the results (if needed)
    // TODO look at each result entry and compare it to the single data options (parents, extensions, users as well as strip options)

    $count_content_dump = count($content_dump);

    if ($parents) {
        $theparents = utils::get_parent_data($parent_ids);
        $count_theparents = count($theparents);
    }

    if ($users) {
        $theusers = utils::get_user_data($user_ids);
        $count_theusers = count($theusers);
    }

    if ($extension) {
        $count_content_props = count($content_props);
    }

    for ($i = 0; $i <= $count_content_dump - 1; ++$i) {
        if ($parents) {
            // Compare against parent IDs to expand parents data
            for ($k1 = 0; $k1 <= $count_theparents - 1; ++$k1) {
                if ($content_dump[$i]->parents->id === $theparents[$k1]->id) {
                    // we found the parent page of our content_dump item...
                    $content_dump[$i]->parents->alias = $theparents[$k1]->alias;
                    $content_dump[$i]->parents->title = $theparents[$k1]->title;
                    break;
                }
            }
        }

        if ($users) {
            // Compare against user_ids IDs to expand user data (extensions)
            for ($k3 = 0; $k3 <= $count_theusers - 1; ++$k3) {
                if ($content_dump[$i]->created->by === $theusers[$k3]->id) {
                    // we found the user who created the page
                    $content_dump[$i]->created->by = $theusers[$k3];
                }

                if ($content_dump[$i]->modified->by === $theusers[$k3]->id) {
                    // we found the user who made the last modification
                    $content_dump[$i]->modified->by = $theusers[$k3];
                }
            }
        }

        if ($extension) {
            // Compare against content_prop IDs to expand entry data (extensions)
            for ($k2 = 0; $k2 <= $count_content_props - 1; ++$k2) {
                if ($content_dump[$i]->content->id === $content_props[$k2]['content_id']) {
                    // We found out that there is some extra content for this page, so let's flag the item
                    $content_dump[$i]->extension = 1;
                    // Now let's assign the data (remember: only when available)
                    // to class names (we use the prop_names as class-names)
                    $content_dump[$i]->extensions->$content_props[$k2]['prop_name']->data = $content_props[$k2]['content'];

                    if ('compile' === $c_smarty) {
                        $_compiled = '';
                        // Extension data fields should be compiled
                        $template->_compile_source('temporary template', $content_dump[$i]->content->data, $_compiled);
                        @ob_start();
                        $template->_eval('?>' . $_compiled);
                        $content_dump[$i]->content->data = @ob_get_clean();
                    } elseif ('strip' === $c_smarty) {
                        // Extension data field should be stripped
                        $content_dump[$i]->content->data = utils::strip_out($content_dump[$i]->content->data, 'smarty');
                    }
//                  else {
//                  }

                    if ('strip' === $c_html) {
                        // HTML, JS and inline styles should be stripped
                        $content_dump[$i]->extensions->$content_props[$k2]['prop_name']->data = utils::strip_out(
                            $content_dump[$i]->extensions->$content_props[$k2]['prop_name']->data,
                            'html'
                        );
                    }

                    $content_dump[$i]->extensions->$content_props[$k2]['prop_name']->length = strlen(
                        $content_dump[$i]->extensions->$content_props[$k2]['prop_name']->data
                    );
                }
            }
        }

        if ('compile' === $c_smarty) {
            // prevent calling page to be processed in Smarty
            if ($content_dump[$i]->content->id !== cms_utils::get_current_pageid()) {
                $template->_compile_source('temporary template', $content_dump[$i]->content->data, $_compiled);
                @ob_start();
                $template->_eval('?>' . $_compiled);
                $content_dump[$i]->content->data = @ob_get_clean();
            }
        } elseif ('strip' === $c_smarty) {
            $content_dump[$i]->content->data = utils::strip_out($content_dump[$i]->content->data, 'smarty');
        }

        if ('strip' === $c_html) {
            $content_dump[$i]->content->data = utils::strip_out($content_dump[$i]->content->data, 'html');
        }
    }

    // Assign results

    $template->assign($assign, $content_dump);

    if (!empty($pager_info)) {
        $template->assign('pager_info', $pager_info);
    }

    setlocale(LC_TIME, '');

    return '';
}
