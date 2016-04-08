/*global btoa, console, hexEncode, isPhoneNumberValid, Lightbox, rc4Encrypt, unescape */

// IE 9< console polyfill
window.console = window.console || {log: function () {}};

var VuFind = (function() {
  var defaultSearchBackend = null;
  var path = null;
  var _submodules = [];
  var _translations = {};

  var register = function(name, module) {
    _submodules.push(name);
    this[name] = 'function' == typeof module ? module() : module;
  };
  var init = function() {
    for (var i=0; i<_submodules.length; i++) {
      this[_submodules[i]].init();
    }
  };

  var addTranslations = function(s) {
    for (var i in s) {
      _translations[i] = s[i];
    }
  };
  var translate = function(op) {
    return _translations[op] || op;
  };

  //Reveal
  return {
    defaultSearchBackend: defaultSearchBackend,
    path: path,

    addTranslations: addTranslations,
    init: init,
    register: register,
    translate: translate
  };
})();

/* --- GLOBAL FUNCTIONS --- */
function htmlEncode(value) {
  if (value) {
    return jQuery('<div />').text(value).html();
  } else {
    return '';
  }
}
function extractClassParams(str) {
  str = $(str).attr('class');
  if (typeof str === "undefined") {
    return [];
  }
  var params = {};
  var classes = str.split(/\s+/);
  for(var i = 0; i < classes.length; i++) {
    if (classes[i].indexOf(':') > 0) {
      var pair = classes[i].split(':');
      params[pair[0]] = pair[1];
    }
  }
  return params;
}
// Turn GET string into array
function deparam(url) {
  if(!url.match(/\?|&/)) {
    return [];
  }
  var request = {};
  var pairs = url.substring(url.indexOf('?') + 1).split('&');
  for (var i = 0; i < pairs.length; i++) {
    var pair = pairs[i].split('=');
    var name = decodeURIComponent(pair[0].replace(/\+/g, ' '));
    if(name.length == 0) {
      continue;
    }
    if(name.substring(name.length-2) == '[]') {
      name = name.substring(0,name.length-2);
      if(!request[name]) {
        request[name] = [];
      }
      request[name].push(decodeURIComponent(pair[1].replace(/\+/g, ' ')));
    } else {
      request[name] = decodeURIComponent(pair[1].replace(/\+/g, ' '));
    }
  }
  return request;
}

// Sidebar
function moreFacets(id) {
  $('.'+id).removeClass('hidden');
  $('#more-'+id).addClass('hidden');
}
function lessFacets(id) {
  $('.'+id).addClass('hidden');
  $('#more-'+id).removeClass('hidden');
}

// Phone number validation
function phoneNumberFormHandler(numID, regionCode) {
  var phoneInput = document.getElementById(numID);
  var number = phoneInput.value;
  var valid = isPhoneNumberValid(number, regionCode);
  if(valid != true) {
    if(typeof valid === 'string') {
      valid = VuFind.translate(valid);
    } else {
      valid = VuFind.translate('libphonenumber_invalid');
    }
    $(phoneInput).siblings('.help-block.with-errors').html(valid);
    $(phoneInput).closest('.form-group').addClass('sms-error');
    return false;
  } else {
    $(phoneInput).closest('.form-group').removeClass('sms-error');
    $(phoneInput).siblings('.help-block.with-errors').html('');
  }
}

function bulkFormHandler(event, data) {
  if ($('.checkbox-select-item:checked,checkbox-select-all:checked').length == 0) {
    VuFind.lightbox.alert(VuFind.translate('bulk_noitems_advice'), 'danger');
    return false;
  }
  var keys = [];
  for (var i in data) {
    if ('print' == data[i].name) {
      return true;
    }
  }
}

// Ready functions
function setupOffcanvas() {
  if($('.sidebar').length > 0) {
    $('[data-toggle="offcanvas"]').click(function () {
      $('body.offcanvas').toggleClass('active');
      var active = $('body.offcanvas').hasClass('active');
      var right = $('body.offcanvas').hasClass('offcanvas-right');
      if((active && !right) || (!active && right)) {
        $('.offcanvas-toggle .fa').removeClass('fa-chevron-right').addClass('fa-chevron-left');
      } else {
        $('.offcanvas-toggle .fa').removeClass('fa-chevron-left').addClass('fa-chevron-right');
      }
    });
    $('[data-toggle="offcanvas"]').click().click();
  } else {
    $('[data-toggle="offcanvas"]').addClass('hidden');
  }
}

function setupAutocomplete() {
  // Search autocomplete
  $('.autocomplete').each(function(i, op) {
    $(op).autocomplete({
      maxResults: 10,
      loadingString: VuFind.translate('loading')+'...',
      handler: function(query, cb) {
        var searcher = extractClassParams(op);
        var hiddenFilters = [];
        $(op).closest('.searchForm').find('input[name="hiddenFilters[]"]').each(function() {
          hiddenFilters.push($(this).val());
        });
        $.fn.autocomplete.ajax({
          url: VuFind.path + '/AJAX/JSON',
          data: {
            q:query,
            method:'getACSuggestions',
            searcher:searcher['searcher'],
            type:searcher['type'] ? searcher['type'] : $(op).closest('.searchForm').find('.searchForm_type').val(),
            hiddenFilters:hiddenFilters
          },
          dataType:'json',
          success: function(json) {
            if (json.data.length > 0) {
              var datums = [];
              for (var i=0;i<json.data.length;i++) {
                datums.push(json.data[i]);
              }
              cb(datums);
            } else {
              cb([]);
            }
          }
        });
      }
    });
  });
  // Update autocomplete on type change
  $('.searchForm_type').change(function() {
    var $lookfor = $(this).closest('.searchForm').find('.searchForm_lookfor[name]');
    $lookfor.autocomplete('clear cache');
    $lookfor.focus();
  });
}

/**
 * Handle arrow keys to jump to next record
 * @returns {undefined}
 */
function keyboardShortcuts() {
    var $searchform = $('.searchForm_lookfor');
    if ($('.pager').length > 0) {
        $(window).keydown(function(e) {
          if (!$searchform.is(':focus')) {
            var $target = null;
            switch (e.keyCode) {
              case 37: // left arrow key
                $target = $('.pager').find('a.previous');
                if ($target.length > 0) {
                    $target[0].click();
                    return;
                }
                break;
              case 38: // up arrow key
                if (e.ctrlKey) {
                    $target = $('.pager').find('a.backtosearch');
                    if ($target.length > 0) {
                        $target[0].click();
                        return;
                    }
                }
                break;
              case 39: //right arrow key
                $target = $('.pager').find('a.next');
                if ($target.length > 0) {
                    $target[0].click();
                    return;
                }
                break;
              case 40: // down arrow key
                break;
            }
          }
        });
    }
}

$(document).ready(function() {
  // Start up all of our submodules
  VuFind.init();
  // Setup search autocomplete
  setupAutocomplete();
  // Off canvas
  setupOffcanvas();
  // Keyboard shortcuts in detail view
  keyboardShortcuts();

  // support "jump menu" dropdown boxes
  $('select.jumpMenu').change(function(){ $(this).parent('form').submit(); });

  // Checkbox select all
  $('.checkbox-select-all').change(function() {
    $(this).closest('form').find('.checkbox-select-item').prop('checked', this.checked);
  });
  $('.checkbox-select-item').change(function() {
    $(this).closest('form').find('.checkbox-select-all').prop('checked', false);
  });

  // handle QR code links
  $('a.qrcodeLink').click(function() {
    if ($(this).hasClass("active")) {
      $(this).html(VuFind.translate('qrcode_show')).removeClass("active");
    } else {
      $(this).html(VuFind.translate('qrcode_hide')).addClass("active");
    }

    var holder = $(this).next('.qrcode');
    if (holder.find('img').length == 0) {
      // We need to insert the QRCode image
      var template = holder.find('.qrCodeImgTag').html();
      holder.html(template);
    }
    holder.toggleClass('hidden');
    return false;
  });

  // Print
  var url = window.location.href;
  if(url.indexOf('?' + 'print' + '=') != -1  || url.indexOf('&' + 'print' + '=') != -1) {
    $("link[media='print']").attr("media", "all");
    $(document).ajaxStop(function() {
      window.print();
    });
    // Make an ajax call to ensure that ajaxStop is triggered
    $.getJSON(VuFind.path + '/AJAX/JSON', {method: 'keepAlive'});
  }

  // Advanced facets
  $('.facetOR').click(function() {
    $(this).closest('.collapse').html('<div class="list-group-item">'+VuFind.translate('loading')+'...</div>');
    window.location.assign($(this).attr('href'));
  });

  $('[name=bulkActionForm]').submit(function() {
    return bulkActionSubmit($(this));
  });
  $('[name=bulkActionForm]').find("[type=submit]").click(function() {
    // Abort requests triggered by the lightbox
    $('#modal .fa-spinner').remove();
    // Remove other clicks
    $(this).closest('form').find('[type="submit"][clicked=true]').attr('clicked', false);
    // Add useful information
    $(this).attr("clicked", "true");
  });

  /******************************
   * LIGHTBOX DEFAULT BEHAVIOUR *
   ******************************/
  Lightbox.addOpenAction(registerLightboxEvents);

  Lightbox.addFormCallback('newList', Lightbox.changeContent);
  Lightbox.addFormCallback('accountForm', newAccountHandler);
  Lightbox.addFormCallback('bulkDelete', function(html) {
    location.reload();
  });
  Lightbox.addFormCallback('bulkSave', function(html) {
    Lightbox.addCloseAction(updatePageForLogin);
    Lightbox.confirm(vufindString['bulk_save_success']);
  });
  Lightbox.addFormCallback('bulkRecord', function(html) {
    Lightbox.close();
    checkSaveStatuses();
  });
  Lightbox.addFormCallback('emailSearch', function(html) {
    Lightbox.confirm(vufindString['bulk_email_success']);
  });
  Lightbox.addFormCallback('saveRecord', function(html) {
    Lightbox.close();
    checkSaveStatuses();
  });

  Lightbox.addFormHandler('exportForm', function(evt) {
    $.ajax({
      url: path + '/AJAX/JSON?' + $.param({method:'exportFavorites'}),
      type:'POST',
      dataType:'json',
      data:Lightbox.getFormData($(evt.target)),
      success:function(data) {
        if(data.data.export_type == 'download' || data.data.needs_redirect) {
          document.location.href = data.data.result_url;
          Lightbox.close();
          return false;
        } else {
          Lightbox.changeContent(data.data.result_additional);
        }
      },
      error:function(d,e) {
        //console.log(d,e); // Error reporting
      }
    });
    return false;
  });
  
  Lightbox.addFormHandler('exportListForm', function(evt) {
    $.ajax({
      url: path + '/AJAX/JSON?' + $.param({method:'getLightbox',submodule:'Cart',subaction:'Export'}),
      type:'POST',
      dataType:'html',
      data:Lightbox.getFormData($(evt.target)),
      success:function(data) {
          Lightbox.changeContent(data);
      },
      error:function(d,e) {
        //console.log(d,e); // Error reporting
      }
    });
    return false;
  });
  
  Lightbox.addFormHandler('emailListForm', function(evt) {
    $.ajax({
      url: path + '/AJAX/JSON?' + $.param({method:'getLightbox',submodule:'Cart',subaction:'Email'}),
      type:'POST',
      dataType:'html',
      data:Lightbox.getFormData($(evt.target)),
      success:function(data) {
          Lightbox.changeContent(data);
      },
      error:function(d,e) {
        //console.log(d,e); // Error reporting
      }
    });
    return false;
  });
  
  Lightbox.addFormHandler('deleteListForm', function(evt) {
    $.ajax({
      url: path + '/AJAX/JSON?' + $.param({method:'getLightbox',submodule:'MyResearch',subaction:'Delete'}),
      type:'POST',
      dataType:'html',
      data:Lightbox.getFormData($(evt.target)),
      success:function(data) {
          Lightbox.changeContent(data);
      },
      error:function(d,e) {
        //console.log(d,e); // Error reporting
      }
    });
    return false;
  });
  
  Lightbox.addFormHandler('doExportForm', function(evt) {
    $.ajax({
      url: path + '/Cart/doExport',
      type:'POST',
      dataType:'html',
      data:Lightbox.getFormData($(evt.target)),
      success:function(data) {
        var win = window.open();
        $(win.document.body).html(data);
        Lightbox.close();
      },
      error:function(d,e) {
        //console.log(d,e); // Error reporting
      }
    });
    return false;
  });
  
  Lightbox.addFormHandler('editListForm', function(evt) {
    var listId = $(this).find("[name='id']").val();
    
    $.ajax({
      url: path + '/MyResearch/EditList/' + listId,
      type:'POST',
      dataType:'html',
      data:Lightbox.getFormData($(evt.target)),
      success:function(data) {
        Lightbox.close();
        location.reload();
      },
      error:function(d,e) {
        //console.log(d,e); // Error reporting
      }
    });
    return false;
  });
  
  Lightbox.addFormHandler('newList', function(evt) {
    var listId = $(this).find("[name='id']").val();
    
    $.ajax({
      url: path + '/MyResearch/EditList/' + listId,
      type:'POST',
      dataType:'html',
      data:Lightbox.getFormData($(evt.target)),
      success:function(data) {
        Lightbox.close();
        location.reload();
      },
      error:function(d,e) {
        //console.log(d,e); // Error reporting
      }
    });
    return false;
  });
  
  Lightbox.addFormHandler('feedback', function(evt) {
    var $form = $(evt.target);
    // Grabs hidden inputs
    var formSuccess     = $form.find("input#formSuccess").val();
    var feedbackFailure = $form.find("input#feedbackFailure").val();
    var feedbackSuccess = $form.find("input#feedbackSuccess").val();
    // validate and process form here
    var name  = $form.find("input#name").val();
    var email = $form.find("input#email").val();
    var comments = $form.find("textarea#comments").val();
    if (name.length == 0 || comments.length == 0) {
      Lightbox.displayError(feedbackFailure);
    } else {
      Lightbox.get('Feedback', 'Email', {}, {'name':name,'email':email,'comments':comments}, function() {
        Lightbox.changeContent('<div class="alert alert-info">'+formSuccess+'</div>');
      });
    }
    return false;
  });
  Lightbox.addFormHandler('loginForm', function(evt) {
    ajaxLogin(evt.target);
    return false;
  });

  // Feedback
  $('#feedbackLink').click(function() {
    return Lightbox.get('Feedback', 'Home');
  });
  // Help links
  $('.help-link').click(function() {
    var split = this.href.split('=');
    return Lightbox.get('Help','Home',{topic:split[1]});
  });
  // Hierarchy links
  $('.hierarchyTreeLink a').click(function() {
    var id = $(this).parent().parent().parent().find(".hiddenId")[0].value;
    var hierarchyID = $(this).parent().find(".hiddenHierarchyId")[0].value;
    return Lightbox.get('Record','AjaxTab',{id:id},{hierarchy:hierarchyID,tab:'HierarchyTree'});
  });
  // Login link
  $('#loginOptions a.modal-link').click(function() {
    return Lightbox.get('MyResearch','UserLogin');
  });
  // Email search link
  $('.mailSearch').click(function() {
    return Lightbox.get('Search','Email',{url:document.URL});
  });
  // Save record links
  $('.save-record').click(function() {
    var parts = this.href.split('/');
    return Lightbox.get(parts[parts.length-3],'Save',{id:$(this).attr('id')});
  });
});
