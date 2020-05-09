jQuery(function ($) {
  var appName = 'sociallogin';
  var showError = function(text) {
    OC.Notification.showTemporary('<div style="font-weight:bold;color:red">'+text+'<div>', {isHTML: true});
  }, attributeMappingToogleQuery = "input[type=checkbox][data-field=attributeMapping]",
  addAttributeMappingButton = "button[data-field=attributeMapping]",
  deleteAttributeMappingButtons = "div[data-action=delete][data-field=attributeMapping]";
  $('#sociallogin_settings').submit(function (e) {
    e.preventDefault();
    $.post(this.action, $(this).serialize())
      .success(function (data) {
        if (data) {
          if (data.success) {
            OC.Notification.showTemporary(t(appName, 'Settings for social login successfully saved'));
          } else {
            showError(data.message);
          }
        }
      })
      .error(function () {
        showError(t(appName, 'Some error occurred while saving settings'));
      });
  });

  $('#disable_registration').change(function () {
    if (this.checked) {
      $('#prevent_create_email_exists').attr('disabled', true);
    } else {
      $('#prevent_create_email_exists').attr('disabled', false);
    }
  }).change();

  $(attributeMappingToogleQuery).each(function(i, e){addAttributeMappingToogleListener(e);});
  $(addAttributeMappingButton).each(function(i, e){addAttributeMappingListener(e);});
  $(deleteAttributeMappingButtons).each(function(i, e){addAttributeMappingDeleteListener(e);});

  initProviderType('openid');
  initProviderType('custom_oidc');
  initProviderType('custom_oauth2');

  function initProviderType(providerType){
    createDelegate(providerType);
    createAdd(providerType);
  }

  function createDelegate(providerType){
    $('#'+providerType+'_providers').delegate('.'+providerType+'-remove', 'click', function () {
      var $provider = $(this).parents('.provider-settings');
      var providerTitle = $provider.find('[name$="[title]"]').val();
      var needConfirm = $provider.find('input').filter(function () {return this.value}).length > 0;
      if (needConfirm) {
        OC.dialogs.confirm(
          t(appName, 'Do you realy want to remove {providerTitle} provider ?', {'providerTitle': providerTitle}),
          t(appName, 'Confirm remove'),
          function (confirmed) {
            if (!confirmed) {
              return;
            }
            $provider.remove();
          },
          true
        );
      } else {
        $provider.remove();
      }
    }).delegate('.group-mapping-add', 'click', function () {
      var $provider = $(this).parents('.provider-settings');
      var $tpl = $provider.find('.group-mapping-tpl');
      $provider.append('<div>'+$tpl.html()+'</div>');
    }).delegate('.group-mapping-remove', 'click', function () {
      $(this).parent().remove();
    }).delegate('.foreign-group', 'input', function () {
      var $this = $(this);
      var newName = this.value ? $this.data('name-tpl')+'['+this.value+']' : '';
      $this.next('.local-group').attr('name', newName)
    });
  }

  function createAdd(providerType){
    $('#'+providerType+'_add').click(function () {
      var $tpl = $('#'+providerType+'_provider_tpl');
      var newId = $tpl.data('new-id');
      $tpl.data('new-id', newId+1);
      var html = $tpl.html().replace(/{{provider_id}}/g, newId);
      $('#'+providerType+'_providers').append('<div class="provider-settings">'+html+'</div>');
      addAttributeMappingToogleListener($(attributeMappingToogleQuery+"[data-provider="+newId+"]")[0]);
      addAttributeMappingListener($(addAttributeMappingButton+"[data-provider="+newId+"]")[0]);
    })
  }

  function addAttributeMappingToogleListener(element){
    if(!element){
      return;
    }
    element.addEventListener("change", function(e){
      var target = $(e.target);
      if(target){
        var element = $(".sociallogin-detail-options[data-field="+target.data("field")+"][data-provider="+target.data("provider")+"]"),
          tableBody = $("tbody[data-field="+target.data("field")+"][data-provider="+target.data("provider")+"]");
        if(!element.hasClass("hidden")){
          tableBody.children().each(function(i, e){
            var e = $(e);
            if(e.data("action") !== "new"){
              e.remove();
            }
          })
        }
        element.toggleClass("hidden");
      }
    });
  }

  function addAttributeMappingListener(element){
    if(!element){
      return;
    }
    element.addEventListener("click", function(e){
      var target, originalAttribute, newAttribute, tpl, tableBody, html;
      if(e.target && e.target.tagName === "DIV"){
        target = $(e.target).parent();
      }else if(e.target && e.target.tagName === "TD"){
        target = $(e.target).children();
      }else{
        target = $(e.target);
      }
      if(target){
        originalAttribute = $("input[name=original_attribute][data-provider="+target.data("provider")+"]")[0];
        newAttribute = $("input[name=new_attribute][data-provider="+target.data("provider")+"]")[0];
        tpl = $('#'+appName+'-'+target.data("provider-type")+'-attributeMapping-tpl')[0];
        tableBody = $("tbody[data-field="+target.data("field")+"][data-provider="+target.data("provider")+"]");
        html = "<tr>";
        tpl.children.forEach(function(e){
          html+="<td>"+e.outerHTML.replace(/{{provider_id}}/g, target.data("provider")).replace(/{{oa_value}}/g, originalAttribute.value).replace(/{{na_value}}/g, newAttribute.value)+"</td>";
        })
        html += "</tr>";
        html = $(html).insertBefore(tableBody.children(":last-child"));
        addAttributeMappingDeleteListener(html.find("div")[0]);
        originalAttribute.value = "";
        newAttribute.value = "";
      }
    });
  }

  function addAttributeMappingDeleteListener(element){
    if(!element){
      return;
    }
    element.addEventListener("click", function(e){
      var target;
      if(e.target){
        target = $(e.target).closest("tr");
        target.remove();
      }
    });
  }

});
