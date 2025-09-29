<style>
* {
  box-sizing: border-box;
}

.click-to-copy {
  cursor:pointer;
}

.small {
  font-size: 85%;
  margin: 0 0 0 15px;
}

ul.accordion-list {
  position: relative;
  display: block;
  width: 96% !important;
  height: auto;
  padding: 20px;
  margin: 0;
  list-style: none;
  background-color: #f9f9fA;
}

ul.accordion-list .accordion-list-item {
  position: relative;
  display: block;
  width: 100%;
  height: auto;
  background-color: #FFF;
  padding: 20px;
  margin: 0 auto 15px auto;
  border: 1px solid #eee;
  border-radius: 5px;
}

ul.accordion-list .accordion-list-item.active h3.al:after {
  transform: rotate(45deg);
}

ul.accordion-list .accordion-list-item h3.al {
  font-weight: 700;
  /*text-decoration-line: underline;*/
  position: relative;
  display: block;
  width: 100%;
  height: auto;
  padding: 0 0 0 0;
  margin: 0;
  font-size: 15px;
  letter-spacing: 0.01em;
  cursor: pointer;
}

ul.accordion-list .accordion-list-item h3.al:after {
  content: "+";
/* font-family: "material-design-iconic-font";*/
  position: absolute;
  right: 0;
  top: 0;
  color: #147fdb;
  transition: all 0.3s ease-in-out;
  font-size: 18px;
}

ul.accordion-list .accordion-list-item div.accordion-list-item-body {
  position: relative;
  display: block;
  width: 100%;
  height: auto;
  margin: 35px 0 15px 0;
  padding: 0;
}

ul.accordion-list .accordion-list-item div.accordion-list-item-body p {
  position: relative;
  display: block;
  font-weight: 300;
  padding: 0 0 0 0;
  line-height: 150%;
  margin: 0 0 0 0;
  font-size: 14px;
}

ul.highlight-list {
  background-color: #eeeeee;
  position: relative;
  display: block;
  width: 80%;
  height: auto;
  margin: 15px;
  padding: 25px;
}
</style>

<script src="{$baseurl}/js/js.cookie.js"></script>
<script src="{$baseurl}/js/js.functions.js"></script>
<script>
$(function() {
  $('#page_tabs > #general').trigger('click');
});
</script>
<div id="page_tabs">
  <div id="general">
    General
  </div>
  <div id="plugins">
    Plugins
  </div>
  <div id="class">
    Class
  </div>
  <div id="new">
    What's New
  </div>
  <div id="about">
    About
  </div>
</div>
<div class="clearb"></div>
<div id="page_content">
  <div id="general_c">
    {include file='module_file_tpl:SmartyExt;help_general_tab.tpl'}
  </div>
  <div id="plugins_c">
    {include file='module_file_tpl:SmartyExt;help_plugins_tab.tpl'}
  </div>
  <div id="class_c">
    {include file='module_file_tpl:SmartyExt;help_class_tab.tpl'}
  </div>
  <div id="new_c">
    {include file='module_file_tpl:SmartyExt;help_what_is_new_tab.tpl'}
  </div>
  <div id="about_c">
    {include file='module_file_tpl:SmartyExt;help_about_tab.tpl'}
  </div>
</div>
