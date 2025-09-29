<h2>What Does This Do?</h2>
<p>This module provides some simple smarty utilities for use in applications or for customizing the behaviour of your CMS Made Simple pages.</p>
<p>The current plugins list:</p>
<ul class="highlight-list">
  <li><strong>&#123content_fetch}</strong> - Fetch and reuse CMSMS content</li>
  <li><strong>&#123content_protect}</strong> - Protect content with a password</li>
  <li><strong>&#123files_list}</strong> - List files and folders</li>
  <li><strong>&#123mod_action_link}</strong> - Create a link to a module action(See also &#123cms_action_url})</li>
  <li><strong>&#123mod_action_url}</strong> - Create an url to a module action (See also &#123cms_action_url})</li>
  <li><strong>&#123sess_erase}</strong> - Erase data recorded by &#123sess_put}</li>
  <li><strong>&#123sess_put}</strong> - Record data in the PHP session</li>
  <li><strong>&#123trigger_403}</strong> - Trigger a 403 error <span style="color:green"><strong>(new)</strong> </span></li>
  <li><strong>&#123trigger_404}</strong> - Trigger a 404 error <span style="color:green"><strong>(new)</strong> </span></li>
  <li><strong>&#123xt_anchor_link}</strong> - Generate a link to an anchor on the same page (See also &#123anchor})</li>
  <li><strong>&#123xt_getvar}</strong> - Retrieve a variable value recorded by &#123xt_setvar}</li>
  <li><strong>&#123xt_repeat}</strong> - Repeating text (See also &#123repeat})</li>
  <li><strong>&#123xt_setvar}</strong> - Record a variable value for use in the current request</li>
  <li><strong>&#123xt_unsetvar}</strong> - Clear a variable recorded by &#123xt_setvar}</li>
</ul>
<p>Additionally, the module registers a class, <strong>smx</strong> with the following methods:</p>
<ul class="highlight-list">
  <li><strong>&#123smx::self_url()}</strong> - Return the current URL</li>
  <li><strong>&#123smx::anchor_url(...)}</strong> - URL to an anchor on the same page</li>
  <li><strong>&#123smx::smx::module_installed(...)}</strong> - Test if a module is installed</li>
  <li><strong>&#123smx::module_version(...)}</strong> - Return the version of a module</li>
  <li><strong>&#123smx::get_parent_alias(...)}</strong> - Returns the alias of page's parent</li>
  <li><strong>&#123smx::is_child_of(...)}</strong> - Tests whether is a child of the specified parent alias</li>
  <li><strong>&#123smx::get_root_alias(...)}</strong> - Returns the alias of a page's root parent</li>
  <li><strong>&#123smx::get_page_title(...)}</strong> - Returns the title of a page</li>
  <li><strong>&#123smx::get_page_menutext(...)}</strong> - Returns the menu text of a page</li>
  <li><strong>&#123smx::get_page_type(...)}</strong> - Returns the content type by alias</li>
  <li><strong>&#123smx::has_children(...)}</strong> - Test if a page has children</li>
  <li><strong>&#123smx::get_children(...)}</strong> - Get information about a page's children</li>
  <li><strong>&#123smx::get_page_content(...)}</strong> - Returns the text of a content block of another page</li>
  <li><strong>&#123smx::get_sibling(...)}</strong> - Returns the alias of the next or previous sibling</li>
  <li><strong>&#123smx::get_parallel_page(...)}</strong> - Get the alias of a parallel page in the pages structure</li>
  <li><strong>&#123smx::get_parallel_url(...)}</strong> - Get the URL of a parallel page in the pages structure</li>
  <li><strong>&#123smx::get_file_listing(...)}</strong> - Return a list of files in a directory </li>
</ul>

<p>Please refer to the tabs describing each plugin and function for help on how to use it.</p>
