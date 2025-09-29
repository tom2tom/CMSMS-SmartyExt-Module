<h2>Smarty Usable Class</h2>
<p>When this module is installed, a new smarty class named smx is automatically available to your page templates and various module templates. This smarty class has numerous functions that you can call at any time.</p>
<h3>Available Functions:</h3>
<ul class="accordion-list">
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::self_url()} <span class="small">Return the current URL</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::self_url</strong>()
      </p>
      <p>Returns the current URL</p>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::anchor_url(...)} <span class="small">URL to an anchor on the same page</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::anchor_url</strong>($name)
      </p>
      <p>Generate the absolute URL to an anchor that is on the same page.</p>
      <p>Arguments:</p>
      <ul>
        <li>$name - The name of the anchor to link to.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&lt;a href="&#123;smx::anchor_url('bottom')}"&gt;Go to the bottom&lt;/a&gt;&lt;a name="bottom"&gt;The bottom&lt;a&gt;</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::smx::module_installed(...)} <span class="small">Test if a module is installed</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::module_installed</strong>($modulename)
      </p>
      <p>Test if a particular module is installed.</p>
      <p>Arguments:</p>
      <ul>
        <li>$modulename - The name of the module to check</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;if smx::module_installed('MAMS')}Found MAMS&#123;/if}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::module_version(...)} <span class="small">Return the version of a module</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::module_version</strong>($modulename)e
      </p>
      <p>Return the version number of a specific installed module.</p>
      <p>Arguments:</p>
      <ul>
        <li>$modulename - The name of the module to check</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;$version=smx::module_version('MAMS')}We have Version &#123;$feu_version} of MAMS</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_parent_alias(...)} <span class="small">Returns the alias of page's parent</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::get_parent_alias</strong>([$alias]).
      </p>
      <p>Returns the alias of the specified page's parent. Returns an empty string if there is no parent.</p>
      <p>Arguments:</p>
      <ul>
        <li>[$alias] - (optional) The page alias to find the parent of. If no value is specified, the current page is used.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">The parent page alias is &#123;smx::get_parent_alias()}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::is_child_of(...)} <span class="small">Tests whether is a child of the specified parent alias</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::is_child_of</strong>($test_parent,$test_child)
      </p>
      <p>Tests whether the specified child alias or ancestor is a child of the specified parent alias. This function can be used to test the root alias or any parent page. It is particularly useful on sites with multiple levels of organization.</p>
      <p>Arguments:</p>
      <ul>
        <li>$test_parent - string The parent page alias to test against.</li>
        <li>$test_child - string The child page alias to test against.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;if smx::is_child_of('home',$page_alias)} This is a child of the home page &#123;/if}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_root_alias(...)} <span class="small">Returns the alias of a page's root parent</span>
    </h3>
    <div class="accordion-list-item-body">
      <strong>smx::get_root_alias</strong>([$alias]) <p>Returns the alias of the specified page's root parent. Returns an empty string if there is no root parent.</p>
      <p>Arguments:</p>
      <ul>
        <li>[$alias] - (optional) The page alias to find the root parent of. If no value is specified, the current page is used.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">The root parent page alias is &#123;smx::get_root_alias()}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_page_title(...)} <span class="small">Returns the title of a page</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::get_page_title</strong>([$alias]).
      </p>
      <p>Returns the title of the specified page.</p>
      <p>Arguments:</p>
      <ul>
        <li>[$alias] - (optional) The page alias to find the title of. If no value is specified, the current page is used.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">The title of the current page is &#123;smx::get_page_title()}</pre>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_page_menutext(...)} <span class="small">Returns the menu text of a page</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::get_page_menutext</strong>([$alias]).
      </p>
      <p>Returns the menutext of the specified page.</p>
      <p>Arguments:</p>
      <ul>
        <li>[$alias] - (optional) The page alias to find the title of. If no value is specified, the current page is used.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">The menutext of the current page is &#123;smx::get_page_menutext()}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_page_type(...)} <span class="small">Returns the content type by alias</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::get_page_type</strong>([$alias]).
      </p>
      <p>Returns the name of the content type of the specified content object by alias</p>
      <p>Arguments:</p>
      <ul>
        <li>[$alias] - (optional) The page alias to find the type of. If no value is specified, the current page is used.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">The type of the current page is &#123;smx::get_page_type()}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::has_children(...)} <span class="small">Test if a page has children</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::has_children</strong>([$alias]).
      </p>
      <p>Test if the specified page has children.</p>
      <p>Arguments:</p>
      <ul>
        <li>[$alias] - (optional) The page alias to test. If no value is specified, the current page is used.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">&#123;$has_children=smx::has_children()}&#123;if $has_children}The current page has children&#123;else}The current page has no children&#123;/if}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_children(...)} <span class="small">Get information about a page's children</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::get_children</strong>([$alias][, $showinactive]).
      </p>
      <p>Return an array containing information about a page's children if any exist.</p>
      <p>Arguments:</p>
      <ul>
        <li>[$alias] - (optional) The page alias to test. If no value is specified, the current page is used.</li>
        <li>[$showinactive] - (optional) Wether inactive pages should be included in the result (defaults to false).</li>
      </ul>
      <p>Fields:</p>
      <ul>
        <li>alias - the page alias of the child</li>
        <li>id - the page id of the child</li>
        <li>title - the title page of the child page.</li>
        <li>menutext - the menu text of the child</li>
        <li>show_in_menu - whether this child page is visible in menus.</li>
        <li>type - The type of child content object.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">
  &#123;$children=smx::get_children()}
  &#123;if count($children)}
    &#123;foreach from=$children item='child'}
      &#123;if $child.show_in_menu}
        Child:  id = &#123;$child.id} alias = &#123;$child.alias}&lt;br/&gt;
      &#123;/if}
    &#123;/foreach}
  &#123;/if}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_page_content(...)} <span class="small">Returns the text of a content block of another page</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::get_page_content</strong>($alias[,$block]).
      </p>
      <p>Returns the text of a specific content block of another page.</p>
      <p>Arguments:</p>
      <ul>
        <li>$alias - The page alias to extract content from.</li>
        <li>[$block] - (optional) The name of the content block in the specified page. If this variable is not specified the value of the default content block <em>(content_en)</em> is assumed. </li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">The 'second' block of the 'about' page is &#123;$foo=smx::get_page_content('about','second')}&#123;eval var=$foo}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_sibling(...)} <span class="small">Returns the alias of the next or previous sibling</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::get_sibling</strong>($direction,$alias).
      </p>
      <p>Returns the alias of the next or previous sibling to the specified page. or false.</p>
      <p>Arguments:</p>
      <ul>
        <li>$direction - the direction to look in. possible values are prev,previous,-1,next,1</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">Link to previous sibling: &#123;$prev_sibling=smx::get_sibling(&quot;prev&quot)}&#123;if !empty($prev_sibling)}&#123;cms_selflink page=&quot;$prev_sibling&quot; text=&quot;Previous&quot;}&#123;/if}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_parallel_page(...)} <span class="small">Get the alias of a parallel page in the page structure</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::get_parallel_page</strong>($new_root[,$current_page = null]).
      </p>
      <p>Get the alias of a parallel page in the page structure given a different root alias.</p>
      <p>In a multilanguage site where the root levels represent different languages, it may be useful to retrieve the alias to an equivalent page in a different language. i.e.: if the user is currently browsing the french page and wishes to see the same page in english (if it exists).</p>
      <p>This function returns the alias of the specified page under the new root. If it exists. If nothing is found, null is returned.</p>
      <p>Arguments:</p>
      <ul>
        <li>new root - The alias of the new root parent. Note, if a top level page is not specified, the alias provided is used to find the top level page (see get_root_alias).</li>
        <li>current_page - (optional) The page alias to find the peer of. If no value is specified, the current page is used.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">Find the equivalent french page for this page: &#123;$tmp=smx::get_parallel_page('FR')}&#123;if $tmp != '' && $tmp != $page_alias}&#123;cms_selflink page=$tmp}&#123;/if}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_parallel_url(...)} <span class="small">Get the URL of a parallel page in the page structure</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::get_parallel_url</strong>($new_root[,$current_page = null]).
      </p>
      <p>Get the URL of a parallel page in the page stricture given a different root alias.</p>
      <p>In a multilanguage site where the root levels represent different languages, it may be useful to retrieve the URL to an equivalent page in a different language. i.e.: if the user is currently browsing the french page and wishes to see the same page in english (if it exists).</p>
      <p>This function returns the alias of the specified page under the new root. If it exists. If nothing is found, null is returned.</p>
      <p>Arguments:</p>
      <ul>
        <li>new root - The alias of the new root parent. Note, if a top level page is not specified, the alias provided is used to find the top level page (see get_root_alias).</li>
        <li>current_page - (optional) The page alias to find the peer of. If no value is specified, the current page is used.</li>
      </ul>
      <p>Example:</p>
      <pre class="click-to-copy" title="Click to copy to clipboard">Find the equivalent french page for this page: &#123;$url=smx::get_parallel_page('FR')}
    &#123;if $url}&lt;a href="&#123;$url}">FR&lt;/a>&#123;/if}</pre>
    </div>
  </li>
  <li class="accordion-list-item">
    <h3 class="al">&#123;smx::get_file_listing(...)} <span class="small">Return a list of files in a directory</span>
    </h3>
    <div class="accordion-list-item-body">
      <p>
        <strong>smx::get_file_listing</strong>($dir[,$excludeprefix]).
      </p>
      <p>Return a list of files in a directory.</p>
      <p>Arguments:</p>
      <ul>
        <li>$dir - The directory to scan (should be an absolute directory)</li>
        <li>[$excludeprefix] - (optional) Exclude files strting with the specified prefix.</li>
      </ul>
    </div>
  </li>
</ul>