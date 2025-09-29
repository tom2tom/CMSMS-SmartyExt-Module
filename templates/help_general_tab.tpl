<h2>What Does This Do?</h2>
<p>This module provides some simple smarty utilities for use in applications or for customizing the behaviour of your CMS Made Simple pages.</p>
<p>The current plugins list:.</p>
<ul class="highlight-list">
  <li><strong>&#123;mod_action_url}</strong> - Create an url to a module action;</li>
  <li><strong>&#123;mod_action_link}</strong> - Create a link to a module action;</li>
  <li><strong>&#123;xt_anchor_link}</strong> - Generate a link to an anchor on the same page;</li>
  <li><strong>&#123;sess_put}</strong> - Store data in the user session;</li>
  <li><strong>&#123;sess_erase}</strong> - Erase data from the user session;</li>
  <li><strong>&#123;xt_repeat}</strong> - Repeating text;</li>
  <li><strong>&#123;content_fetch}</strong> - Fetch and reuse CMSMS content;</li>
  <li><strong>&#123;files_list}</strong> - List files and folders;</li>
  <li><strong>&#123;content_protect}</strong> - Protect content with a password;</li>
  <li><strong>&#123;trigger_404}</strong> - Trigger a 404 error <span style="color:green"><strong>(new)</strong> </span>;</li>
  <li><strong>&#123;trigger_403}</strong> - Trigger a 403 error <span style="color:green"><strong>(new)</strong> </span>;</li>
</ul>
<p>Additionally, the module registers a class, <strong>&#123;smx}</strong> with the following methods:</p>
<ul class="highlight-list">
  <li><strong>&#123;smx::self_url()}</strong> - Return the current URL;</li>
  <li><strong>&#123;smx::anchor_url(...)}</strong> - URL to an anchor on the same page;</li>
  <li><strong>&#123;smx::smx::module_installed(...)}</strong> - Test if a module is installed;</li>
  <li><strong>&#123;smx::module_version(...)}</strong> - Return the version of a module;</li>
  <li><strong>&#123;smx::get_parent_alias(...)}</strong> - Returns the alias of page's parent;</li>
  <li><strong>&#123;smx::is_child_of(...)}</strong> - Tests whether is a child of the specified parent alias;</li>
  <li><strong>&#123;smx::get_root_alias(...)}</strong> - Returns the alias of a page's root parent;</li>
  <li><strong>&#123;smx::get_page_title(...)}</strong> - Returns the title of a page;</li>
  <li><strong>&#123;smx::get_page_menutext(...)}</strong> - Returns the menu text of a page;</li>
  <li><strong>&#123;smx::get_page_type(...)}</strong> - Returns the content type by alias;</li>
  <li><strong>&#123;smx::has_children(...)}</strong> - Test if a page has children;</li>
  <li><strong>&#123;smx::get_children(...)}</strong> - Get information about a page's children;</li>
  <li><strong>&#123;smx::get_page_content(...)}</strong> - Returns the text of a content block of another page;</li>
  <li><strong>&#123;smx::get_sibling(...)}</strong> - Returns the alias of the next or previous sibling;</li>
  <li><strong>&#123;smx::get_parallel_page(...)}</strong> - Get the alias of a parallel page in the page structure;</li>
  <li><strong>&#123;smx::get_parallel_url(...)}</strong> - Get the URL of a parallel page in the page structure;</li>
  <li><strong>&#123;smx::get_file_listing(...)}</strong> - Return a list of files in a directory ;</li>
</ul>

<p>Please refer to the tabs describing each plugin and function for help on how to use it.</p>