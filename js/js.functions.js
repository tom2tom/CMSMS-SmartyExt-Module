(function(global, $, jQuery, Cookies, help_functions)
{
  var OE = global.OE;
  var messages_template = '<aside id="helper_message" class="message pagemcontainer" role="status"><p>{0}</p></aside>';
  var errors_template = '<aside id="helper_error" class="message pageerrorcontainer" role="alert"><p>{0}</p></aside>';
  
  jQuery(document).ready(function()
  {
    var active_tab = Cookies.get('smarty_ext_tab_active');
    var active_accordion_item = Cookies.get('smarty_ext_acc_active');
    
    /* active tabs logic */
    if(typeof active_tab === "undefined")
    {
      _set_active_tab('general');
    }
    else
    {
      _set_active_tab(active_tab);
    }
  
    $('#page_tabs > div')
      .each(function()
      {
        $(this).click(function()
        {
          Cookies.set('smarty_ext_tab_active', this.id);
        });
      });
  
    /*  copy to clipboard stuff */
    $(".click-to-copy").click(function (e)
    {
      e.preventDefault();
      help_functions.detectDragOrClick(this);
    });
  
    /* the accordion stuff */
    _set_incremental_ids('accordion-list-item');
    $('.accordion-list > .accordion-list-item > .accordion-list-item-body').hide();
    $('.accordion-list-item')
  
    if(typeof active_accordion_item !== "undefined")
    {
      $('#' + active_accordion_item)
        .addClass("active")
        .find(".accordion-list-item-body")
        .slideDown();
    }
    
    $('.al').click(function()
    {
      var current_item = $(this).closest(".accordion-list-item");
      
      if($(this).closest(".accordion-list-item").hasClass("active"))
      {
        $(this).closest(".accordion-list-item").removeClass("active").find(".accordion-list-item-body").slideUp();
        _set_active_accordion_item('undefined');
      }
      else
      {
        $(".accordion-list > .accordion-list-item.active .accordion-list-item-body").slideUp();
        $(".accordion-list > .accordion-list-item.active").removeClass("active");
        $(this).closest(".accordion-list-item").addClass("active").find(".accordion-list-item-body").slideDown();
        _set_active_accordion_item( $(current_item).attr('id') );
      }
      return false;
    });
    
    
  });
  
  function _set_incremental_ids(a_class)
  {
    var count = 1;
    var sel = '.' + a_class;
    $(sel).each(function()
    {
      $(this).attr("id", a_class + "-" + count);
      count++;
    });
  }
  
  function _set_active_accordion_item(id)
  {
    Cookies.set('smarty_ext_acc_active', id);
  }
  
  function _set_active_tab(tab_name)
  {
    $('#page_tabs > div')
      .each(function()
      {
        $(this).removeClass('active');
      });
    
    $('#page_content > div')
      .each(function()
      {
        $(this).hide();
      });
    
    $('#' + tab_name).addClass('active');
    $('#' + tab_name + '_c').show();
    Cookies.set('smarty_ext_tab_active', tab_name);
  }

  
  String.prototype.endsWith = function (suffix)
  {
    return (this.substr(this.length - suffix.length) === suffix);
  }
  
  String.prototype.startsWith = function(prefix)
  {
    return (this.substr(0, prefix.length) === prefix);
  }
  
  /**
   * Usage:
   * 'Added {0} by {1} to your collection'.f(title, artist)
   * 'Your balance is {0} USD'.f(77.7)
   * @type {function(): String}
   */
  String.prototype.format = String.prototype.format = function() {
    var s = this,
        i = arguments.length;
    
    while (i--) {
      s = s.replace(new RegExp('\\{' + i + '\\}', 'gm'), arguments[i]);
    }
    return s;
  };
  
  /**
   * taps into the core function to show an error when needed
   *
   * @param message_text
   */
  help_functions.help_error_message =  function (message_text)
  {
    var output = errors_template.format(message_text);
    $('#helper_error').detach();
    $('#oe_mainarea').prepend(output);
    $('#helper_error').hide();
    OE.view.showNotifications();
  }
  
  /**
   * taps into the core function to show a message when needed
   *
   * @param message_text
   */
  help_functions.help_message =  function (message_text)
  {
    var output = messages_template.format(message_text);
    $('#helper_message').detach();
    $('#oe_mainarea').prepend(output);
    $('#helper_message').hide();
    OE.view.showNotifications();
  }
  
  
  help_functions.GetSelectedText = function()
  {
    if(window.getSelection)
    {
      // all browsers, except IE before version 9
      var range = window.getSelection();
      return range.toString();
    }
    else
    {
      if(document.selection.createRange)
      {
        // Internet Explorer
        var range = document.selection.createRange();
        return range.text;
      }
    }
  }
  /**
   * what is copied depends on  whether
   * we clicked and dragged, or just clicked:
   * dragged: copy the selected text on mouse up
   * clicked: select all and copy on mouse up
   * @param element
   */
  help_functions.detectDragOrClick = function(element)
  {
    var isDragging = false;
    var startingPos = [];

    $(element)
      .mousedown(function(evt)
      {
        isDragging  = false;
        startingPos = [evt.pageX, evt.pageY];
      })
      .mousemove(function(evt)
      {
        if( !(  evt.pageX === startingPos[0] && evt.pageY === startingPos[1] ) )
        {
          isDragging = true;
        }
      })
      .mouseup(function()
      {
        help_functions.copyToClipboard(element, isDragging);
        isDragging  = false;
        startingPos = [];
      });
  }
  
  
  help_functions.copyToClipboard = function(element, dragging)
 {
   var $temp = $("<input>");
   $("body").append($temp);
   
   if(!dragging)
   {
     //help_functions.GetSelectedText();
     $temp.val($(element).text()).select();
   }
   
   document.execCommand("copy");
   $temp.remove();
   help_functions.help_message('Copied to Clipboard!');
 }
}(this, $, jQuery, Cookies, help_functions = window.help_functions || {}));
