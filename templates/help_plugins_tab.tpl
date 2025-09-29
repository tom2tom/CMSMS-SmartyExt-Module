<h2>Smarty Plugins</h2>
<p>This is a list of the plugins available from this module, with instructions and example usage.</p>

<ul class="accordion-list">
  <li class="accordion-list-item">
    <h3 id="content_fetch" class="al">&#123;content_fetch} <span class="small">Fetch and reuse CMSMS content</span></h3>
    <div class="accordion-list-item-body">
      <p>A fork of <strong><em>&#123;content_dump}</em></strong> from Nils Haack. Still a work in progress, so keep checking these docs for changes in parameters and behaviour.</p>
      <p>A <em>'Swiss Army Knife'</em> for CMSMS content fetching, giving you the ability to fetch content and insert it into a single page. It offers a variety of independent parameters that can be combined to cater for a lot of your site's internal content distribution needs.</p>
      <p>Just attach some parameters and limit/organize the output to your data requirements. The Smarty output allows you to freely design the results in your template or content block (from the regular editors view - though it is recommended to do it in the template). With Smarty, you can even control the output by limiting it to specific values (e.g. only user 'John Doe') or use modifiers to further manipulate the content.</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>block_name</strong> - The name of the content block to fetch from (default <strong>content_en</strong>);</li>
        <li><strong>start_id</strong> - Defines a starting point for the content fetching (default <strong>-1</strong> meaning all matching content);</li>
        <li><strong>limit_start</strong> - The first n items to skip (default <strong>0</strong>);</li>
        <li><strong>limit_count</strong> - Maximum number of pages to fetch (default <strong>1000</strong>);</li>
        <li><strong>page</strong> - Generates a single page view out of the results of rest of the parameters, allowing for some form of pagination (default <strong>0</strong>);</li>
        <li>
          <strong>active</strong> - filter by page states; accepted values are:
          <ul>
            <li><strong>force</strong> - show everything regardless of their state;</li>
            <li><strong>active</strong> - show only active pages (default);</li>
            <li><strong>inactive</strong> - show only inactive pages;</li>
          </ul>
        </li>
        <li>
          <strong>show_in_menu</strong> | <strong>shown_in_menu</strong> - filter by menu visibility; accepted values are:
          <ul>
            <li><strong>force</strong> - show everything regardless of their visibility in a menu;</li>
            <li><strong>show</strong> - show only pages set to be visible in a menu (default);</li>
            <li><strong>hidden</strong> - show only pages set not to be visible in a menu;</li>
          </ul>
        </li>
        <li><strong>filter</strong> - Filter results by words or phrases (see extended help and examples below);</li>
        <li><strong>prefix</strong> - Filter results by alias prefix(es) (see extended help and examples below);</li>
        <li>
          <strong>prefix_mode</strong> - Changes the <strong>prefix</strong> filtering behaviour; accepted values are:
          <ul>
            <li><strong>force</strong> - show only pages with the prefix(es) listed;</li>
            <li><strong>neutral</strong> - ignores prefix(es) (default);</li> {* actually doesnt make sense... (JM) *}
            <li><strong>hide</strong> - show only pages without the prefix(es) listed;</li>
          </ul>
        </li>
        <li>
          <strong>depth</strong> - Filter results by hierarchy start point and number of levels. It takes two integer values seperated by comma such as '-1,3', '-1' being the starting point, in this case the root and '3' the number of levels (or how deep) to fetch (see extended help and examples below). If used, both values are mandatory;
        </li>
        <li><strong>exclude</strong> - Exclude content by id, accepting comma seperated lists of integers if needed;</li>
        <li><strong>this_only</strong> - Fetch a particular content from pages by id;</li>
        <li><strong>these_only</strong> - Fetch a particular set of content from pages by a comma seperated lists of integers of ids;</li>
        <li><strong>parents</strong> - A boolean type setting defining whether to include a limited set of properties of the parents of each result, defaults to false (see extended help and examples below);</li>
        <li>
          <strong>users</strong> - A boolean type setting defining whether to expand user data related to the content such as <strong>created by</strong> and <strong>modified by</strong> which if true will return <strong>username</strong>,
          <strong>firstname</strong> and <strong>lastname</strong> in addition to the default (if false) <strong>uid</strong> (see extended help and examples below);
        </li>
        <li>
          <strong>extensions</strong> - A comma seperated list of content_blocks to determine if they exist and have content; it will show in the results list as an additional property for each result with a value of '1' or '0' depending on
          whether the content block has been found and is not empty (default <strong>false</strong>) (see extended help and examples below);
        </li>
        <li><strong>dateformat</strong> - Format the date output in a PHP strftime format (default <strong>'%A, %e %B %Y'</strong>). Note: deprecated pending some more changes to the CMSMS core to be PHP 8.0+ compatible;</li>
        <li><strong>cdlocale</strong> - Specify a specific locale for the output otherwise system default is used;</li>
        <li><strong>locale</strong> - Specify a specific locale used for the timestamp otherwise system default is used;</li>
        <li>
          <strong>do_smarty</strong> - Changes the Smarty parsing behaviour; accepted values are:
          <ul>
            <li><strong>compile</strong> - parses the content through Smarty before output. Note: can be recursive and result in endless loops;</li>
            <li><strong>neutral</strong> - passes through without parsing (default);</li>
            <li><strong>strip</strong> - an attempt is made to remove anything between '&#123;' and '}' (and that means <em>anything</em>);</li>
          </ul>
          <p>Notes:</p>
          <ul>
            <li>there is a real chance of, inadvertently, create an endless loop with the <strong>compile</strong> option resulting in out of memory errors;</li>
            <li>to try to prevent this the plugin can recognise a single use of the tag in the content an that should be safe;</li>
            <li>multiple occurrences of the tag on the same content block using the <strong>compile</strong> option cannot be safely detected and should be avoided;</li>
            <li>
              <strong>strip</strong> mode uses a regex to remove any and all occurrences of patterns with a '&#123;' ... '}' construct. That means other natural language constructs that fit that pattern will also be removed. JavaScript constructs
              can easily become corrupted too;
            </li>
            <li>one way to avoid that issue with natural language text constructs is to replace '&#123;' and '}' with their html codes, respectively: <strong>'&amp;#123;'</strong> and <strong>'&amp;#125;'</strong> or equivalent;</li>
            <li>regarding JavaScript, the best alternative is to move the code snippets used to a different content block or to the main template(s) if at all possible;</li>
          </ul>
        </li>
        <li>
          <strong>html</strong> - Tries to remove any and all HTML, JavaScript and CSS from the resulting content block(s) so it can be possible to safely truncate the text; accepted values are:
          <ul>
            <li><strong>strip</strong> - strips the retrieved blocks;</li>
            <li><strong>neutral</strong> - passes through the retrieved blocks after processing <strong>do_smarty</strong>;</li>
          </ul>
        </li>
        <li>
          <strong>first_sort</strong> - sorts the content by specific elements; accepted values are:
          <ul>
            <li><strong>id</strong> - sorts by page (content) id;</li>
            <li><strong>title</strong> - sorts by page (content) title;</li>
            <li><strong>created</strong> - sorts by page (content) created date (order may need to be reversed);</li>
            <li><strong>modified</strong> - sorts by page (content) modified date (order may need to be reversed);</li>
            <li><strong>owner</strong> - sorts by page (content) owner id;</li>
            <li><strong>hierarchy</strong> - sorts by page (content) hierarchy;</li>
            <li><strong>id_hierarchy</strong> - sorts by page (content) hierarchy id;</li>
            <li><strong>lasteditor</strong> - sorts by page (content) last editor id;</li>
            <li><strong>active</strong> - sorts by page (content) active state;</li>
            <li><strong>show</strong> - sorts by page (content) menu visibility;</li>
          </ul>
        </li>
        <li><strong>first_sort_order</strong> - determines the direction of the sorting filter, whether it is <em>'up'</em> or <em>'down'</em> (default <strong>'up'</strong> as in alphabetically A to B);</li>
        <li>
          <strong>second_sort</strong> - sorts the content by specific elements; accepted values are:
          <ul>
            <li><strong>id</strong> - sorts by page (content) id;</li>
            <li><strong>title</strong> - sorts by page (content) title;</li>
            <li><strong>created</strong> - sorts by page (content) created date (order may need to be reversed);</li>
            <li><strong>modified</strong> - sorts by page (content) modified date (order may need to be reversed);</li>
            <li><strong>owner</strong> - sorts by page (content) owner id;</li>
            <li><strong>hierarchy</strong> - sorts by page (content) hierarchy;</li>
            <li><strong>id_hierarchy</strong> - sorts by page (content) hierarchy id;</li>
            <li><strong>lasteditor</strong> - sorts by page (content) last editor id;</li>
            <li><strong>active</strong> - sorts by page (content) active state;</li>
            <li><strong>show</strong> - sorts by page (content) menu visibility;</li>
          </ul>
        </li>
        <li><strong>second_sort_order</strong> - determines the direction of the sorting filter, whether it is <em>'up'</em> or <em>'down'</em> (default <strong>'up'</strong> as in alphabetically A to B);</li>
        <li><strong>assign</strong> - Assign the output to the specified Smarty variable (default <strong>content_fetch</strong> meaning the result will be available via &#123;$content_fetch});</li>
      </ul>
      <hr>
      <h2>Extended Help and Examples</h2>
      <h3>Debugging</h3>
      <p>You can debug the plugin output by simply surrounding the assigned Smarty variable tag with HTML 'pre' tags and using a print_r modifier:</p>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">
  &#123;content_fetch assign='my_var_name'}
  &lt;pre>&#123$my_var_name|print_r:1}&lt;/pre></pre>
      <p>That should output the contents of the assigned var as an array of results the structure of which should vary depending on the parameters used.</p>

      <h3>Selecting items</h3>
      <h4><em>Assigning to a Smarty variable</em></h4>
      <p><strong>assign</strong> can be used to define the output variable to something other than the default <strong>&#123;$content_fetch}</strong>.</p>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">
  &#123;content_fetch assign='my_var_name'}
  &#123;$my_var_name[0]->content->alias}</pre>

      <h4><em>Selecting a content-block</em></h4>

      <p><strong>block_name</strong> can be used to define the content block you want to use as the &#123;&#123;$content_fetch[n]->content->data} element. As a default, it will be <em>'content_en'</em>, the page standard content block. If the block you want to show is &#123;content block='summary'} in your source page's template, you would call it like in the following example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;content_fetch block_name='summary'}</pre>

      <h4><em>Where should the fetch begin?</em></h4>
      <p><strong>start_id</strong> can be used to define a starting point for your content collection. If not specified, it will begin with id <em>'-1'</em> and thus include all matching pages from all pages. A nice trick to use the tag in several pages based on the same template and limit the tag to this site area is to set <em>start_id=$content_id</em>. This way, you use the viewed page's id as the initial page and ignore all other content folders.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;content_fetch start_id=16}</pre>

      <h4><em>Skip the first X items (offset).</em></h4>
      <p><strong>limit_start</strong> will allow you to do that. State the number of the item you would like your output to begin with. <em>Default is 0</em></p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;content_fetch limit_start=5}</pre>

      <h4><em>Limit the output to a specific number of items.</em></h4>
      <p><strong>limit_count</strong> can be used to limit the output to a specific number. In combination with page, it will define the size of a page.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;content_fetch limit_count=10}</pre>

      <h4><em>Pagination</em></h4>
      <p><strong>page</strong> can be used to generate a page based view. Value is <em>page number</em>. It will basically move the view you have on the results by the number of items specified in limit_count. It alters the limit_start by adding limit_count to it, each time an increasing page is selected. Let's say you have limit_start=0 and limit_count=5, page=2 will alter limit_start to be 6.</p>
      <p>Usage of this parameter will provide some extra smarty data elements. $pager_info->current, $pager_info->max and $pager_info->size. Representing the currently selected page, the maximum page available and the page size. Use smarty and this data to build easy and complex pagers alike.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;content_fetch limit_count=5 page=2}</pre>

      <h4><em>Show also inactive pages or only inactive pages.</em></h4>
      <p><strong>active</strong> can be used to control how the <strong>'active'</strong> flag of pages should be interpreted. 'force' shows all pages regardless of status. 'active' show active pages only (default). 'inactive' show inactive pages only.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch active='force'}</pre>

      <h4><em>Shown in menu?</em></h4>
      <p><strong>shown_in_menu</strong> can be used to control how the 'show_in_menu' flag of pages should be interpreted. 'force' shows all pages regardless of status. 'show' show pages set to 'Show in Menu' only (default). 'hidden' show pages that are set to 'Don't Show in Menu' only.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch show_in_menu='hidden'}</pre>

      <h3>Filter items</h3>

      <h4><em>Filter for specific content (e.g. check if data fields contain the word 'world')</em></h4>
      <p><strong>filter</strong> can be used to limit the results to entries which contain specific words or phrases or to exlude such items. Use boolean logic. e.g. filter='-hello' to exclude items that contain the word 'hello' in any of their content->data or extension->itemname->data elements or filter='world' to limit the results to those items that contain the word 'world' in any of their data elements.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch filter='world'}</pre>

      <h4><em>Exclude or limit (filter) the output to pages with matching alias prefix</em></h4>
      <p><strong>prefix</strong> can be used to state the prefix or prefixes that should be considered for excluding from the collection or that should be the only pages in the collection. Several prefixes can be used but must be separated by comma. E.g. &#123$content_fetch prefix='private_,special_'}</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch prefix='prefixname'}</pre>

      <h4><em>How should the prefix or list of prefix(es) be handled?</em></h4>
      <p><strong>prefix_mode</strong> Prefix-mode is used to control the handling of the prefix(es). 'force', 'neutral" and "hide' are the available values. Standard mode is neutral that will disregard any available prefix. Forced mode only shows pages with one of the stated prefixes. Hide will exclude pages with the prefix from the items returned.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch prefix_mode='hide'}</pre>

      <h4><em>How can I limit the output to specific hierarchy levels (e.g. depth) ?</em></h4>
      <p><strong>depth</strong> is used to control which hierarchy levels should be considered for output. It takes two integer values separated by comma (e.g. depth='-1,3'}. The first value defines the starting hierarchy. -1 = start with content_fetch, n = start with specific hierarchy level [0 and 1 both return first level].</p>
      <p>The second value defines the number of additional levels added to the collection. 0 = no depth, siblings of page specified with first depth value are returned (if not excluded otherwise). n = number of additional level relative to the level specified with first depth value.</p>
      <p><strong>Both values must be used!</strong></p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch depth='-1,3'}</pre>

      <h3>Exclude items</h3>

      <p><em>Exclude single items from the list</em></p>
      <p><strong>exclude</strong> allows you to remove specific pages from the results. It takes any number of content <em>IDs separated by comma</em>. Especially when forcing content_fetch to compile smarty in found content-blocks, you can use this tag to break endless recursions or for any other reason where you want specific pages not to appear.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch exclude='13,23,53,12,32'}</pre>

      <p><em>Just show the data of a specific element</em></p>
      <p><strong>this_only</strong> is a parameter that will limit the result to this specific ID.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch this_only=55}</pre>

      <h3>Add more data to the item</h3>
      <h4><em>Get more information about parent pages</em></h4>
      <p><strong>parents</strong> can be used to request more information about the parent pages (alias and title). Possible values are <em>true</em> or <em>false</em>. Using these parameters allows you to use the data element $dump[n]->parents->alias or $dump[n]->parents->title. The data element $dump[n]->parents->id will be provided in any case.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch parents=true}</pre>

      <h4><em>Get more information about the users who wrote/edited the content</em></h4>
      <p><strong>users</strong> can be used to request more information about the users who last edited and created the content. Possible values are <em>true</em> or <em>false</em>. Per default $dump[n]->created->by and $dump[n]->modified->by will only return the user ID. If set to true, the mentioned class elements will be expanded by detailed user info (first-, last- and user name).</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch users=true}</pre>
      <h4><em>Using more content-blocks from the pages</em></h4>
      <p><strong>extensions</strong> is your choice then. It takes a <em>comma separated list of content_blocks</em>. If any of them (for an item) features content, $dump[n]->extension will be '1', other wise it will be '0' (default). This can be used to check for availability of 'more' data. Each content_block will be added as a class below $dump[n]->extensions. E.g. &#123$content block='more_text'} will be available as $dump[n]->extensions->more_text->data together with $dump[n]->extensions->more_text->length. Ideally, your content block names do not feature special characters and not ' ' or '-', use '_' instead.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch extensions='summary,image,other_block_name'}</pre>

      <h3>Process data from items</h3>

      <h4><em>Change the date/time format</em></h4>
      <p><strong>dateformat</strong> can be used to format the date output of content_fetch. Check <a href="http://de.php.net/strftime" target="blank">http://de.php.net/strftime</a> for more ifo on the date format _options. It is set to <em>'%A, %e %B %Y'</em> by default (e.g. Sat, 20 September 2008). It is used for the two time stamps $dump[n]->created->date and $dump[n]->modified->date that are always returned with each item.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch dateformat='%A, %e %B %Y'}</pre>

      <h4><em>Change the date/time locale</em></h4>
      <p><strong>cdlocale</strong> can be used to specify a specific locale for your output. E.g. your page is danish and your site/server is set to this locale - but for the generation of valid RSS you might need the US date/time locale. </p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch cdlocale='en-us'}</pre>

      <h4><em>Change the date format locale</em></h4>
      <p><strong>locale</strong> can be used to alter the locale used for the timestamp (e.g. system is Danish, but you need US day names for XML).</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch locale='en-US'}</pre>

      <h4><em>Compile or remove Smarty tags from output</em></h4>
      <p><strong>do_smarty</strong> controls how smarty data that may be retrieved should be handled. There are three options <em>'compile'</em>, <em>'neutral'</em> and <em>'strip'</em>. <em>'compile'</em> compiles smarty data in the pages found. <em>'neutral'</em> prints out the smarty code as regular text and is the default setting. <em>'strip'</em> deletes all smarty code from your page (well, everything between &#123$ and }).</p>
      <p>However, be carefully not to construct a query that would have to compile itself, this will result in an out of memory error due to endless recursion.If you use this tag only once with the compile parameter in your site, no problem, it prevents itself from being rendered. But if you have another occurrence of this tag that would include the page that is calling the currently processed tag with compile='true', you should exclude all of these pages (content_fetch does not check for the compile parameter of other occurrences of the tag).</p>
      <p>If that called tag would feature other content blocks... then, again, all would work nice. Just do not construct something with content_fetch that is somewhere in the chain of events compiling itself.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch do_smarty="compile"}</pre>

      <h4><em>Remove HTML from content elements so that it can safely be truncated</em></h4>
      <p><strong>html</strong> is a parameter that allows you to remove any HTML, JavaScript and CSS from a content-block. <em>'strip'</em> and <em>'neutral'</em> are the available _options. <em>'neutral'</em> will display the content as it is (after processing do_smarty settings) and is the default setting. <em>'strip'</em> will remove the mentioned elements.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch html='neutral'}</pre>

      <h3>Sort items</h3>

      <h4><em>Primary sorting of data elements</em></h4>
      <p><strong>first_sort</strong> sorts the found content by one of the following values: <em>id, title, created, modified, owner, hierarchy (default), id_hierarchy, lasteditor, active, show (show in menu)</em>. For time based (like newest contents) use created, for last updated lists use modified (both need to be reversed with sort_order). Hint: Sorting by owner or lasteditor takes place by ID, not name!</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch first_sort='owner'}</pre>

      <h4><em>Set the direction of primary sorting</em></h4>
      <p><strong>first_sort_order</strong> can be used to reverse the sorting filter. <em>'up'</em> and <em>'down'</em> are the available _options, whereas "up" (A before B or yesterday before today) is the default direction.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch first_sort_order='up'}</pre>

      <h4><em>Secondary sorting of data elements</em></h4>
      <p><strong>second_sort</strong> sorts the found content just as <strong>first_sort_order</strong> does and adds an extra filter to the results</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch second_sort='owner'}</pre>

      <h4><em>Set the direction of secondary sorting</em></h4>
      <p><strong>second_sort_order</strong> is the same as <strong>first_sort_order</strong>  but for <strong>second_sort</strong>.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123$content_fetch second_sort_order='up'}</pre>

      <h3>Maximum data structure for fetch items:</h3>

      <pre class="click-to-copy" title="Click to copy to clipboard">
  &#123;*
    $content_fetch = array of classes
    n = integer

    $content_fetch[n]-&gt;item  =  Counter for items in current list (integer)
  *}
  &#123$content_fetch[1]-&gt;content-&gt;id}
  &#123$content_fetch[1]-&gt;content-&gt;alias}
  &#123$content_fetch[1]-&gt;content-&gt;title}
  &#123$content_fetch[1]-&gt;content-&gt;menu}
  &#123$content_fetch[1]-&gt;content-&gt;show}
  &#123$content_fetch[1]-&gt;content-&gt;active}
  &#123$content_fetch[1]-&gt;content-&gt;data}

  &#123$content_fetch[1]-&gt;parents-&gt;id}
  &#123$content_fetch[1]-&gt;parents-&gt;alias}
  &#123$content_fetch[1]-&gt;parents-&gt;title}

  &#123$content_fetch[1]-&gt;created-&gt;date}
  &#123$content_fetch[1]-&gt;created-&gt;by-&gt;username}
  &#123$content_fetch[1]-&gt;created-&gt;by-&gt;last_name}
  &#123$content_fetch[1]-&gt;created-&gt;by-&gt;first_name}
  &#123$content_fetch[1]-&gt;created-&gt;by-&gt;email}

  &#123$content_fetch[1]-&gt;modified-&gt;date}
  &#123$content_fetch[1]-&gt;modified-&gt;by-&gt;username}
  &#123$content_fetch[1]-&gt;modified-&gt;by-&gt;last_name}
  &#123$content_fetch[1]-&gt;modified-&gt;by-&gt;first_name}
  &#123$content_fetch[1]-&gt;modified-&gt;by-&gt;email}

  &#123$content_fetch[1]-&gt;extension}
  &#123*
    And for each extension you have named, you will get an equally named class element below &#123$content_fetch[n]-&gt;extensions
    e.g.
  *}
  &#123$content_fetch[1]->extensions-&gt;summary-&gt;data}
  &#123$content_fetch[1]-&gt;extensions-&gt;summary-&gt;length}
      </pre>

      <h5>Date structure of pager information</h5>
      <pre class="click-to-copy" title="Click to copy to clipboard">
  &#123$pager_info-&gt;current}
  &#123$pager_info-&gt;max}
  &#123$pager_info-&gt;size}</pre>

      <h5>Usefull smarty snippets for working with content_fetch</h5>
      <p>Below you find some code snippets you can use in your templates in conjunction with content_fetch</p>

      <h5>Prepare template for paging</h5>
      <pre class="click-to-copy" title="Click to copy to clipboard">
  &#123;assign var=page_call value=$smarty.get.show_page}
  &#123;if $page_call == ''}&#123;assign var=page_call value=1}&#123;/if}</pre>

      <p>This will listen for URL parameter called show_page and its value. If no parameter found, create is and assign value 1. Use $page_call as the value of the page parameter &#123;...page=$page_call...}</p>
      <h4>'newer' 'older' pager</h4>
      <pre class="click-to-copy" title="Click to copy to clipboard">
  &#123;if $page_call > 1 }
    &lt;a href="blog.htm?show_page=&#123;$pager_info->current-1}">newer articles&lt;/a>
  &#123;/if}

  &#123;if $pager_info->max > $page_call}
    &lt;a href="blog.htm?show_page=&#123;$pager_info->current+1}">older articles&lt;/a>
  &#123;/if}</pre>
      <h4>Page number list pager</h4>
      <pre class="click-to-copy" title="Click to copy to clipboard">
&#123;section name='i' start=1 loop=$pager_info->max+1 step=1}
&lt;a href="blog.htm?show_page=&#123;$smarty.section.i.index}">&#123;$smarty.section.i.index}&lt;/a>
&#123;/section}</pre>
      <h4>Walk through all available elements</h4>
      <pre class="click-to-copy" title="Click to copy to clipboard">
  &#123;content_fetch ... }
  &#123;foreach $content_fetch as $fetch_data}
  &#123;$fetch_data->content->data}
  &#123;/foreach}</pre>
      <h4>Display read more link</h4>
      <pre class="click-to-copy" title="Click to copy to clipboard">
  &#123;content_fetch ... }
  &#123;foreach $content_fetch as $fetch_data}
    &#123;if $fetch_data->extension == 1}
      &lt;a href="&#123;$fetch_data->content->alias}.htm">read more&lt;/a>
    &#123;/if}
  &#123;/foreach}</pre>
      <p>Original Author of the content_dump plugin: Nils Haack &lt;hello@opticalvalve.com&gt;</p>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="content_protect" class="al">&#123;content_protect} <span class="small">Protect content with a password</span></h3>
    <div class="accordion-list-item-body">
      <p>This is a fork of <strong>page_protect</strong> by Jo Morg. Same code base, a few differences emerged  from being integrated in a module, in particular the lack of need to initialize the plugin as in the original plugin.<br>
        This plugin allows you to protect a number of pages with one or more passwords either by being set once per each page you want to protect, or by being set on a page template, allowing you to protect all pages connected to that template. It is complemented by a block plugin <code><strong>&#123;protect} <em>...content you want to protect...</em> &#123;/protect}</strong></code>  and a modifier <code><strong>&#123;'protect this'|protect}</strong></code></p>

        <div class="warning" style="display:block;">
          <p>
            <strong>Note: </strong> there is a particularity in the way CMSMS renders content which may expose protected content in certain situations. To prevent that there are a few rules you need to follow:
          </p>
          <ul>
            <li>
              When the default content block is the only one with the content to protect:
              <ol type="1">
                <li>use all the logic inside the block itself even if the passwords are set on the template;</li>
                <li>set the login form and passwords as close to the top of the content as possible when set inside the content block;</li>
                <li>using any other content property to set the logic (as the <strong>Smarty data or logic that is specific to this page</strong>) may work on a typical setting but will leave the protected content vulnerable in certain circumstances;</li>
                <li>keep in mind that there is a feature in CMSMS that will strip down the whole template and render only the default content, so following the above rules will prevent exposure of the protected content;</li>
              </ol>
            </li>
            <li>if you have more than the default content block:
               <ol type="1">
                <li>preferably don't use the main block to keep the protected content, use a different block for it;</li>
                <li>content blocks other than the main are not vulnerable to being exposed by the above described CMSMS feature;</li>
                <li>if you must use it then follow the rules for the default content as in the previous suggestion;</li>
              </ol>
            </li>
          </ul>
        </div>

        <div class="warning" style="display:block;">
          <p>
            <strong>Note: </strong> This is a simple, relatively safe way of protecting content without having to install a full-fledged users management module. If you need a more complex and complete users' access management module please consider using <strong>MAMS</strong>.
          </p>
        </div>

        <div class="information" style="display:block;">
          <p>
            Because of this plugin is a fork of the discontinued Page Protect (page_protect) there are a few parameters that were left for backward compatibility. As this plugin doesn't require initialization, which is done at the module level when it is loaded, the default and the set actions are now the same thing. That means that a typical initialization tag does nothing on its own and can be safely removed (if replaced from a page_protect original tag), but leaving it in place doesn't affect in any way the site's performance.
          </p>
        </div>
        <div class="information" style="display:block;">
          <p>
            Parameters marked as <strong><em>(persistent) </em></strong> are set once for the whole page request. The default action can set any and all of them without the need to repeat them on every tag call. However, a later call can override a previously set parameter.
          </p>
        </div>

        <h4>Parameters</h4>
        <ul>
          <li><strong>action</strong> <em>(optional)</em> - possible values:
            <ul>
              <li><em><strong>default</strong></em> <strong> <em>(default)</em></strong>: the default action for the module and can be omitted. On its own it does nothing, but can be used as a set action in conjunction with other parameters;</li>
              <li><em><strong>set</strong> (legacy) (deprecated)</em>: use this action to distribute persistent parameters though different tags on the same page (helps with template readability), this action now being part of the default action can also be omitted;</li>
              <li><em><strong>form</strong></em>: show either the <strong>login</strong> form or the <strong>logout</strong> form, depending on current user state;</li>
            </ul>
          </li>
          <li><strong>login_alias</strong> <em>(optional) (persistent) </em> - an existing page alias (defaults to no redirection): this will be used to redirect after login if needed;</li>
          <li><strong>logout_alias</strong> <em>(optional) (persistent)</em> - an existing page alias (defaults to current page i.e. the page where the plugin is being used): this will be used to redirect after logout;</li>
          <li><strong>timeout</strong> <em>(optional) (persistent)</em> - if set to a value higher than 0 it will set a cookie and use this value as minutes before login times out;</li>
          <li><strong>cookie_name</strong> <em>(optional) (persistent)</em> - the name of the cookie (defaults to <strong>cp_auth</strong>);</li>
          <li><strong>welcome_msg</strong> <em>(optional) (persistent)</em> - a message to be shown as a welcome text on the login form;</li>
          <li><strong>protected_msg</strong> <em>(optional) (persistent)</em> - a message to replace the protected content (defaults to no message at all);</li>
          <li><strong>error_msg</strong> <em>(optional) (persistent)</em> - an error message to be shown on password errors;</li>
        </ul>
        <p>Exclusive to form action:</p>
        <ul>
          <li><strong>login_btn</strong> <em>(optional)</em> - text of the login button caption (defaults to Login);</li>
          <li><strong>logout_btn</strong> <em>(optional)</em> - text of the logout button caption(defaults to Logout;</li>
          <li><strong>form_id</strong> <em>(optional)</em> - login form id;</li>
          <li><strong>form_class</strong> <em>(optional)</em> - login form class;</li>
          <li><strong>in_pass_id</strong> <em>(optional)</em> - password input id;</li>
          <li><strong>in_pass_class</strong> <em>(optional)</em> - password input class;</li>
          <li><strong>button_id</strong> <em>(optional)</em> - both buttons id;</li>
          <li><strong>button_class</strong> <em>(optional)</em> - both buttons class;</li>
        </ul>
        <p>The protect block tag accepts only one parameter:</p>
        <ul>
          <li><strong>protected_msg</strong> <em>(optional)</em> - a message to replace the protected content (defaults to whatever was set on a previous persistent tag call);</li>
        </ul>
        <p>The protect modifier doesn't take any parameters.</p>
        <hr>
        <p>Example as a content block:</p>
          <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;protect} ...content you want to protect... &#123;/protect} and a modifier </pre>
        <p>Example as a content modifier:</p>
        <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;'protect this'|protect}</pre>

        <h3>Quick Use</h3>
        <p>The plugin allows for fast deployment of a secure page. Content Protect will assign a <strong>Smarty</strong> variable, <strong>$cp_logged_in</strong> by default, with a boolean value flagging whether the current user is logged in or not.</p>
        <p>Example:</p>
        <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;content_protect action='form' passwords='pass1'}</pre>

        <p>This is a simple way to hide the content of a page from non-authorized users:</p>
        <div class="information" style="display:block;">
          <p>Use this on the page content!</p>
        </div>
        <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;content_protect action='form' passwords='pass1'}
  &#123;if $cp_logged_in}
    The allowed content....
  &#123;else}
    Not Logged in!
  &#123;/if}</pre>
        <p>The previous snippet will show a form for non-logged-in users, along with the text "Not Logged in!"</p>
        <p>For the logged-in user will present a logout button, and "The allowed content....".</p>
        <hr>
        <p>This is a simple way to hide the content of all pages with this template from non-authorized users:</p>
        <div class="information" style="display:block;">
          <p>Use this on the main template!</p>
        </div>

        <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;content name=protected assign='protected'}
  &#123;content_protect action='form' passwords='pass1'}
  &#123;if $cp_logged_in}
      &#123;$protected}
  &#123;else}
    Not Logged in!
  &#123;/if}
  &#123;* or *}
  &#123;$protected|protect}
  &#123;* or *}
  &#123;protect}&#123;$protected}&#123;/protect}</pre>

      <p>The previous snippet will show a form for non-logged-in users, along with the text "Not Logged in!"</p>
      <p>For the logged-in user will present a logout button, and the content of the page.</p>
      <hr>
      <p>You can also use this tag to add a few parameters except for action='set' and action='form' (<em>and all form exclusive parameters as they are only pertinent to the form action and not persistent</em>). Use:</p>
      <ul>
        <li><strong>&#123;content_protect passwords='</strong>pass1[,pass<em><strong>2</strong></em>]...[,pass<em><strong>n</strong></em>]<strong>'}</strong></li>
        <li><strong>&#123;content_protect action='</strong>default<strong>' passwords='</strong>pass1[,pass<em><strong>2</strong></em>]...[,pass<em><strong>n</strong></em>]<strong>'}</strong></li>
      </ul>

      <p>Put this on the field <strong>Smarty data or logic that is specific to this page</strong> found on the page <em><strong>_options tab</strong></em>:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;content_protect passwords='password1,passwor2,password3,passwordn'}</pre>
      <p>You can set the passwords, and any other parameters on the initialization tag as most of the parameters are persistent through the same request.</p>
      <p>Put this on the top of your templates:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;content_protect passwords='password1,passwor2,password3,passwordn'}</pre>

      <h3>The Form Action</h3>

      <p>You can set all options on the tag call with this action. If you are using the quick way, this is all you need to set a Smarty variable which you can check anywhere on the template after this call.</p>
      <p>Other than that just place the tag where you want a login/logout form. The default <strong><em>form</em> action</strong> has default values for all form parameters, so the minimal tag is <strong>&#123;content_protect action='</strong><em>form</em><strong>'}</strong>.</p>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;content_protect action=form}</pre>
      <p>An example with all the parameters you can use to customize the form:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;* Do we really need all this?!!! A complete form call *}
      <&#123;content_protect action='form' login_btn='Let Me In!' logout_btn='Bye Bye!' form_class='css_form' form_id='css_my_form' in_pass_id='css_passwrdid' in_pass_class='css_passwrd_class' button_id='css_btn_id' button_class='css_btn_class'}
      </pre>
      <p>You can use any of the persistent parameters with the form action if needed. However, the form specific parameters <strong>are not persistent</strong>.</p>

      <h3>The Default and Set Actions</h3>
      <div class="information" style="display:block;">
        <p>
          The <strong>set</strong> action is a legacy action, deprecated but still functional. It is recommended to use the <strong>default</strong> which can be omitted.
        </p>
      </div>
      <p>These are special actions with the sole purpose of allowing you to set persistent parameters on different tag calls, helping a bit with the readability of the tags: along the template. Just keep in mind that if you call it again, using the same parameters with different values, the last value will override all previous.</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;* Using the 'set' action to spread parameters through multiple calls *}
      &#123;* redirect Home *}
      &#123;content_protect action='set' logout_alias='home'}
      &#123;* set the time before a login expires *}
      &#123;content_protect action='set' timeout=10}
      &#123;* set the message to show in case the authentication fails *}
      &#123;content_protect action='set' error_msg='Oops! Wrong pass, mate! Check your notes...'}
      &#123;* setting all the above in a single tag call could lead to errors *}
      </pre>

      <h3>The Protect Tags</h3>
      <p>These tags are block smarty tags, and can be used several times on the page in pairs, i.e: an opening tag and a closing tag. The opening tag accepts only one parameter, the <strong>protected_msg</strong> which overrides the default one if set. This is a per occurrence tag, meaning that if it is set on the <em><strong>default</strong></em> or  <em><strong>set</strong></em> actions it is persistent, but if set on a <strong>&#123;protect}</strong> tag it affects only the tag where it is used and doesn't persist to the next occurrence.</p>

      <h4><strong>Content wrapping tags example.</strong></h4>
      <p>Use one of the following tags:
      <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;protect}whatever content you want protected.&#123;/protect}
      &#123;protect protected_msg='well, you really should be logged in if you what to see the content'}whatever content you want protected.&#123;/protect}</strong>
      </pre>
      </p>

      <h5>The protect block tag accepts only one parameter:</h5>
      <ul>
        <li><em>(optional) <strong>protected_msg</strong></em> - a message to replace the protected content (defaults to whatever was set on a previous persistent tag call);</li>
      </ul>
      <h5>The protect modifier tag doesn't accept parameters</h5>
      <p>For the time being the modifier doesn't require any parameter to work. It will use whatever parameters have been set previously.</p>

      <h3>Code Snippets</h3>

      <h4>Using The Form Action</h4>

      <p> An example with all the parameters you can use to customize the form:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;* Do we really need all this?!!! A complete form call which doesn't output nothing because the assign_output parameter is being used *}
       &#123;content_protect action='form' login_btn='Let Me In!' logout_btn='Bye Bye!' form_class='css_form' form_id='css_my_form' in_pass_id='css_passwrdid' in_pass_class='css_passwrd_class' button_id='css_btn_id' button_class='css_btn_class' assign_output='login_form'}
       &#123;* by using assign_output='login_form' you can use &#123;$login_form} several times on the template *}
       &#123;$login_form}
      </pre>

      <h4>Using The Protect Block Tags</h4>

      <p>Use one of the following tags:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;protect}whatever content you want protected.&#123;/protect}
      &#123;protect protected_msg='well, you really should be logged in if you what to see the content'}whatever content you want protected.&#123;/protect}
      </pre>

    <h4>Using The Protect Modifier</h4>
    <p>Use any one of the following methods:</p>
    <pre class="click-to-copy" title="Click to copy to clipboard">  &#123;$protect_me = 'whatever content you want protected.'}
      &#123;$protect_me|protect}
      &#123;* or *}
      &#123;'Some text I want to have protected'|protect}
      &#123;protect protected_msg='well, you really should be logged in if you what to see the content'}whatever content you want protected.&#123;/protect}
    </pre>

  <h4>Passwords</h4>
  <p>The passwords parameter can accept a range of values, from a single password, a comma separated list of passwords, or an array of values:</p>

    <pre class="click-to-copy" title="Click to copy to clipboard">
<strong>&#123;content_protect passwords='pass1'}</strong>
    </pre>

    <pre class="click-to-copy" title="Click to copy to clipboard">
<strong>&#123;content_protect passwords='pass1,'pass2','pass3'}</strong>
    </pre>

    <pre class="click-to-copy" title="Click to copy to clipboard">
&#123;* Use smarty syntax to create an array *}
<strong>&#123;$passwords=['pass1','pass2','pass3']}</strong>

&#123;* or *}
<strong>&#123;$passwords[]='pass1'}
&#123;$passwords[]='pass2'}
&#123;$passwords[]='pass3'}</strong>

&#123;* and use it as the value for the parameter passwords *}
<strong>&#123;content_protect passwords=$passwords}</strong>

&#123;* or *}
<strong>&#123;content_protect action='set' passwords=$passwords}</strong>
    </pre>

      <h3>Notes</h3>

      <h4>Persistent Parameters</h4>
      <p>Some of the parameters used by this plugin are persistent for the duration of the request, that is to say, through all of the current rendered page. This means that you can set them once, knowing they will be used later on the same request on subsequent calls to the plugin. That also means that they can be changed on subsequent calls, if needed.</p>

      <h4>Passwords</h4>

      <div class="warning" style="display:block;">
      <p><strong>Note:</strong> Unless you use an array to set the passwords, avoid the use of commas (<strong>,</strong>) and of vertical slashes (<strong>|</strong>) as password symbols as these are reserved to internal use and will unavoidably lead to passwords not being recognized by the plugin.</p>
      </div>

      <h3>Cookies</h3>

      <p>If the <strong>timeout</strong> parameter is used, this plugin will generate a frontend cookie. By using this parameter you may be violating some countries laws of user privacy. Please make sure you provide a fair warning on the front pages if needed, or avoid using the <strong>timeout</strong> parameter, thus disabling the use of cookies. The only drawback of not using cookies is that the authentication only lasts for a single page request.</p>

    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="files_list" class="al">&#123;files_list} <span class="small">List files and folders</span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin that lists files and folders in a specific directory with file name, file size, file date and other optional info. Simple as it may look, it is a sophisticated plugin with many features including files download and download link obfuscation, sorting, limits, etc.</p>
      <p>The plugin has the ability to list files under the root of the CMS Made Simple installation thus making easier to prevent file hot linking. Together with a module or plugin that protects access to pages it makes it impossible to share links to files.</p>
      <p>The plugin returns a object (which can easily be converted to an array) which properties hold the needed data (most of it in the form of arrays) to be used in templates in any way you wish.</p>
      <h4>Parameters</h4>
      <ul>
        <li> <strong>folder</strong> - folder to list files from (default is <strong>'uploads'</strong>). <em>(optional)</em></li>
        <li> <strong>root</strong> - a full path from the root of the account file system, required if the files are located under the site installation root. (default is <strong>''</strong>) i.e. an empty string. When empty, the plugin will derive the above <strong>folder</strong> parameter starting from the root of the site. Having a full path from the root, allows to list files from non-public folders below the site root. Those files can be downloaded but no files can be executed.<em>(optional)</em><br>
          Note: Having root set to anything but an empty string automatically activates download obfuscation for security purposes.</li>
        <li> <strong>show_hidden</strong> - whether to show hidden files. Default is false. Keep in mind that the downloads count data file is a hidden file and that its default location is the same as <strong>folder</strong>. <em>(optional)</em></li>
        <li> <strong>count_downloads</strong> - whether to count the downloads. Default is false. Keep in mind that the downloads count data file is a hidden file and that its default location is the same as <strong>folder</strong>. <em>(optional)</em></li>
        <li> <strong>counter_db_dir</strong> - where the downloads data file should be located.  Default location is the same as <strong>folder</strong>. <em>(optional)</em><br>
          Note: changing this setting without moving the file to the set location will cause data being lost as a new file will be created as soon as a new download is attempted.<br>
          Note: to reset the downloads count you'll need to delete or edit the downloads data file.
        </li>
        <li>
          <strong>dl_counter_db_fn</strong> - the name of the downloads data file, default is <em>'.counter_db'</em>.<em>(optional)</em><br>
          Note: in most non windows OSs file systems a file starting with a dot is hidden by default.
        </li>
        <li>
          <strong>strip_extension</strong> - whether to hide the file extension from the list, default is <em>false</em>.<em>(optional)</em>
        </li>
        <li>
          <strong>obfuscate_download</strong> - whether to obfuscate the location of the files, default is <em>true</em>.<em>(optional)</em>
        </li>
        <li>
          <strong>security_token</strong> - whether to require a security token to allow to download, default is <em>true</em>.<em>(optional)</em>
        </li>
        <li>
          <strong>security_token_qv</strong> - the name of the query variable for the security token, default is <em>_st</em>.<em>(optional)</em>
        </li>
        <li> <strong>sort</strong> - sort order:
          <ul>
            <li><strong>d</strong> - sort by date ascending;</li>
            <li><strong>dd</strong> - sort by date descending;</li>
            <li><strong>s</strong> - sort by size ascending;</li>
            <li><strong>sd</strong> - sort by size descending;</li>
            <li><strong>ns</strong> - sort by name ascending;</li>
            <li><strong>nd</strong> - sort by name descending;</li>
          </ul>
          (default is sort by filetype then file name). <em>(optional)</em></li>
        <li> <strong>delimiter='your delimiter'</strong> - Default is _, this is the element that will be stripped and replaced with spaces, this enables you to have accessible file name links while also having pretty looking file names on the front end.<em>(optional)</em></li>
        <li> <strong>showsize='false'</strong> - Default is true, but setting this to false will disable file size being shown.<em>(optional)</em></li>
{*      <li> <strong>fileextension='extension'</strong> - Add any extensions tags you want included in the removal list including the dot. Defaults are .pdf, .doc, .docx, .txt, .rtf, .avi, .mov, .exe.<em>(optional)</em></li> *}
        <li> <strong>date='false'</strong> - Default is true, but setting this to false will disable date being shown.<em>(optional)</em></li>
        <li> <strong>dateformat='Y-m-d'</strong> - <a href="http://php.net/manual/en/function.date.php" target="_blank">Date format</a>.<em>(optional)</em></li>
        <li> <strong>browse_subdirs='1'</strong> - Allow browse subdirectories. Default is 0.<em>(optional)</em></li>
          {* <li> <strong>prettyurls='true'</strong> - Default is false. This only becomes an issue when used in conjunction with the browsesubdirs parameter.<em>(optional)</em></li> *}
        <li> <strong>listtype='ol'</strong> - Default is ul. This parameter allows you to specify whether your list should be an Ordered or Unordered list. Only options available are ul and ol.<em>(optional)</em></li>
        <li> <strong>maxentries='10'</strong> - Default is all. This parameter allows you to specify the maximum number of files/folders to display.<em>(optional)</em></li>
        <li> <strong>target='_blank'</strong> - Default is none. Possible options are _blank, _self, _parent, _top.<em>(optional)</em></li>
        <li> <strong>tracking='original'</strong> - Default is universal. Specify what version of the analytics event tracking code appears. Possible options are universal or original.<em>(optional)</em></li>
      </ul>
        <p>Example:</p>
        <pre class="click-to-copy" title="Click to copy to clipboard">&#123;files_list folder='myfiles'}</pre>
      <p>This plugin is still a work in progress, so there are some bugs yet to be fixed. Do some thorough tests to see if it fits your needs.</p>
      <p>You can use the template below as a starting point to build your own template:</p>

      <pre class="click-to-copy" title="Click to copy to clipboard">
  &lt;ul&gt;
    &#123;foreach $files_list-&gt;files as $one}
      &lt;li&gt;
        &#123;if $one.sub_type=='image'}
            &#123;*
              if you wish to use thumbs by default the folder name is thumbs
              &lt;a href="&#123;$one.url}"&gt;&lt;img src="&#123;$one.thumbs_url}" alt="&#123;$one.name}"&gt;&lt;/a&gt;
              but that can be overriden by using the thumbs_folder='foldername' parameter on the tag.
            *}
          &lt;a href="&#123;$one.url}"&gt;&lt;img src="&#123;$one.url}" alt="&#123;$one.name}"&gt;&lt;/a&gt;
        &#123;else}
         &lt;a href="&#123;$one.url}"&gt;&#123;$one.name}&lt;/a&gt;
        &#123;/if}
        &lt;ul&gt;
          &lt;li&gt;name: &#123;$one.name}&lt;/li&gt;
          &lt;li&gt;type: &#123;$one.type}&lt;/li&gt;
          &lt;li&gt;url: &#123;$one.url}&lt;/li&gt;
        &#123;if $one.type == 'file'}
          &lt;li&gt;file_ext: &#123;$one.file_ext}&lt;/li&gt;
          &lt;li&gt;sub_type: &#123;$one.sub_type}&lt;/li&gt;
          &lt;li&gt;title: &#123;$one.title}&lt;/li&gt;
          &lt;li&gt;size: &#123;$one.size}&lt;/li&gt;
          &lt;li&gt;date: &#123;$one.date}&lt;/li&gt;
          &#123;if isset($one.download_count)}&lt;li&gt;downloads: &#123;$one.download_count}&lt;/li&gt;&#123;/if}
        &#123;elseif $one.type == 'dir'}
          &lt;li&gt;file_ext: &#123;$one.file_ext}&lt;/li&gt;
          &lt;li&gt;title: &#123;$one.title}&lt;/li&gt;
          &lt;li&gt;date: &#123;$one.date}&lt;/li&gt;
        &#123;/if}
        &lt;/ul&gt;
      &lt;/li&gt;
    &#123;/foreach}
  &lt;/ul&gt;
  &lt;hr&gt;
  Total Size Text: &#123;$files_list-&gt;total_size_text}
  Total Size: &#123;$files_list-&gt;total_size}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="mod_action_link" class="al">&#123;mod_action_link} <span class="small">Create a link to a module action</span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin that can create a link to a module action.</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>module</strong> - The module to create a link to. This argument is optional, the system will attempt to detect the current module name (if within a module action)</li>
        <li><strong>action</strong> (default) - The action to call within the module</li>
        <li><strong>text</strong> - The text to put in the link</li>
        <li><strong>page</strong> - Specify the destination page</li>
        <li><strong>urlonly</strong> - Instead of generating a link, generate just the url</li>
        <li><strong>jsfriendly</strong> | <strong>forjs</strong> - Turns on the urlonly parameter, and indicates that javascript friendly urls are output.</li>
        <li><strong>forajax</strong> - Turns on the jsfriendly parameter (and the urlonly parameter), and also appends showtemplate=false to the URL output for AJAX requests</li>
        <li><strong>confmessage</strong> - A confirmation message to display when the link is clicked.</li>
        <li><strong>image</strong> - An image to use on the link</li>
        <li><strong>imageonly</strong> - If an image is specified, create a link only consisting of the image. The text will be used for the title attribute</li>
        <li><strong>assign</strong> - Assign the output of the plugin to the named Smarty variable.</li>
      </ul>

      <p>Any other arguments to the Smarty plugin will be added to the URL generated.</p>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;mod_action_link module='News' action='fesubmit' text='Submit a New News Article'}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="mod_action_url" class="al">&#123;mod_action_url} <span class="small">Create an url to a module action</span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin that can create an url to a module action.</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>module</strong> - The module to create a link to. This argument is optional, the system will attempt to detect the current module name (if within a module action)</li>
        <li><strong>action</strong> (default) - The action to call within the module</li>
        <li><strong>page</strong> - Specify the destination page</li>
        <li><strong>jsfriendly</strong> | <strong>forjs</strong> - Turns on the urlonly parameter, and indicates that javascript friendly urls are output.</li>
        <li><strong>forajax</strong> - Turns on the jsfriendly parameter (and the urlonly parameter), and also appends showtemplate=false to the URL output for AJAX requests</li>
        <li><strong>assign</strong> - Assign the output of the plugin to the named Smarty variable.</li>
      </ul>

      <p>Any other parameters to the Smarty plugin will be added to the URL generated.</p>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;mod_action_url module='News' action='fesubmit' assign='foo'}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="sess_erase" class="al">&#123;sess_erase} <span class="small">Remove a session variable recorded by &#123sess_put}</span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin that will erase data from the PHP session.</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>var</strong> - The name/key of the variable to erase.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;sess_erase var='test'}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="sess_put" class="al">&#123;sess_put} <span class="small">Store a variable and its value in the PHP session</span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin that will store data in the PHP session. This data is then accessible via the $smarty.session array in subsequent pages.</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>var</strong> - The name/key of the variable to create.</li>
        <li><strong>value</strong> - The desired value of the variable.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;sess_put var='test' value='blah'}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="trigger_403" class="al">&#123;trigger_403} <span class="small">Trigger a 403 error <span style="color:green"><strong>(new)</strong></span></span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin that will trigger a server 403 error. The way the error will be handled depends on whether there is a custom CMSMS 403 error page set in Content Manager or not.</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>active</strong> -Needs to be &apos;truthy&apos; (non-0 number, true, or non-empty string) to trigger the error;</li>
        <li><strong>msg</strong> - The message to display when the error is triggered (default <strong>'Permission denied!'</strong>);</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;trigger_403 msg='this' active=$do_trigger}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="trigger_404" class="al">&#123;trigger_404} <span class="small">Trigger a 404 error <span style="color:green"><strong>(new)</strong></span></span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin that will trigger a server 404 error. The way the error will be handled depends on whether there is a custom CMSMS 404 error page set in Content Manager or not.</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>active</strong> - Needs to be &apos;truthy&apos; (non-0 number, true, or non-empty string) to trigger the error;</li>
        <li><strong>msg</strong> - The message to display when the error is triggered (default <strong>'This content is not available'</strong>;)</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;trigger_404 msg='this' active=$do_trigger}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="xt_anchor_link" class="al">&#123;xt_anchor_link} <span class="small">Generate a link to an anchor on the same page</span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin to generate a link to an anchor that is on the same page.</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>n</strong> | <strong>name</strong> - <em>(string)</em> The name of the anchor to link to.</li>
        <li><strong>text</strong> - <em>(string></em> The text portion of the link. If not specified, the anchor name will be used.</li>
        <li><strong>urlonly</strong> - <em>(bool)</em> Optionally only generate the URL portion of the link. see smx::anchor_url()</li>
        <li><strong>assign</strong> - <em>(string></em> Optionally assign the output of the plugin to the named Smarty variable.</li>
      </ul>
      <p>Any other arguments to the Smarty plugin will be added as attributes to the link generated.</p>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;xt_anchor_link name=bottom class="anchor_class" id="anchor_id"}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="xt_getvar" class="al">&#123;xt_getvar} <span class="small">Retrieve a variable value recorded by &#123xt_setvar}</span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin to retieve a recorded variable, or a default value if the specified variable is not recorded</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>var</strong> - Name/key of the wanted variable</li>
        <li><strong>v</strong> - Alias of the var property</li>
        <li><strong>dflt</strong> - The value to return if the specified variable is not found. If not specified, the default will be null.</li>
        <li><strong>assign</strong> - Assign the output to the specified Smarty variable</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;xt_getvar args}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="xt_repeat" class="al">&#123;xt_repeat} <span class="small">Generate repeating text</span></h3>
    <div class="accordion-list-item-body">
      <p>Another Smarty plugin that allows repeating text</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>text</strong> - The text to be repeated</li>
        <li><strong>count</strong> - The number of times it should be repeated</li>
        <li><strong>assign</strong> - Assign the output to the specified Smarty variable</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;xt_repeat text='this' count='5'}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="xt_setvar" class="al">&#123;xt_setvar} <span class="small">Record or remove variable value(s) for use in the current request</span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin to set or clear one or more variables for use during the current request.<br>To clear, specify &apos;_unset_&apos; for the variable value.</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>varname1</strong> - Identifier of 1st variable to be set or cleared</li>
        <li style="list-style-type:none">...</li>
        <li><strong>varnameN</strong> - Identifier of Nth variable to be set or cleared</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;xt_setvar name1=&apos;woowoo is my name&apos; name2=&apos;_unset_&apos;}</pre>
    </div>
  </li>
{***********************************************************************}
  <li class="accordion-list-item">
    <h3 id="xt_unsetvar" class="al">&#123;xt_unsetvar} <span class="small">Clear variable(s) recorded by &#123xt_setvar}</span></h3>
    <div class="accordion-list-item-body">
      <p>A plugin to clear one or more recorded variables, specified either as a comma-separated series of names, or one or more parameter-specifed individual names</p>
      <h4>Parameters</h4>
      <ul>
        <li><strong>unset</strong> - Clear all variables specified in the value, a comma-separated series</li>
        <li><strong>varname1</strong> - Identifier of 1st variable to be cleared</li>
        <li style="list-style-type:none">...</li>
        <li><strong>varnameN</strong> - Identifier of Nth variable to be cleared</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;xt_unsetvar username}</pre>
    </div>
  </li>
</ul>
