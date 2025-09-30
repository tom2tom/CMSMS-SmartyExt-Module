<link rel="stylesheet" href="{$baseurl}/css/help{$dir|default:''}.css">
<script src="{$baseurl}/js/js.cookie.js"></script>
<script src="{$baseurl}/js/js.help.js"></script>
<script>
$(function() {
 $('#index_c').find('span').on('click', function(e) {
  e.preventDefault();
  var fm = '#' + $(this).data('frag');
  var tab = $(this).closest('ul').data('tab');
  $('#' + tab).trigger('mousedown');
  window.location.hash = fm;
  $(fm).trigger('click');
 });
});
</script>
{tab_header name='general' label='General'}
{tab_header name='index' label='Index'}
{tab_header name='plugins' label='Plugins'}
{tab_header name='methods' label='Methods'}
{tab_start name='general'}
{include file='module_file_tpl:SmartyExt;help_about_tab.tpl'}
{tab_start name='index'}
{include file='module_file_tpl:SmartyExt;help_index_tab.tpl'}
{tab_start name='plugins'}
{include file='module_file_tpl:SmartyExt;help_plugins_tab.tpl'}
{tab_start name='methods'}
{include file='module_file_tpl:SmartyExt;help_class_tab.tpl'}
{tab_end}
