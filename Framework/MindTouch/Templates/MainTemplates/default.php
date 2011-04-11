<eval:if test="#args[0] &lt; 1">
  <ul class="ae_errors">
    <li class="ae_error">Unknow root template</li>
  </ul>
</eval:if>
<eval:else>
  {{
     var root     = args[0];
     var uid      = __request.args.uid ?? args[1];
     var action   = __request.args.actions ?? args[2];
     var params   = args[3] ?? {};
     var prefix   = args[4] ?? 'default';
     var isPopup  = __request.args.popup ?? 0;
     let isPopup  = isPopup == 0 ? false : true;
     
     if (uid == 'Constants')
     {
        let action   = 'displayConstantForm';
        var tpl_name = 'EditForm';
        var puid     = {kind: 'Constants', type: nil};
     }
     else if (uid == 'Deletion')
     {
        let action   = 'displayDeletionForm';
        var tpl_name = 'DeletionForm';
        var puid     = {kind: 'DeleteMarkedForDeletion', type: nil};
     }
     else
     {
        var puid     = #uid > 0 ? entities.parseUID(uid) : nil;
        var tpl_map  = {
           displayListForm: 'ListForm', 
           displayEditForm: 'EditForm', 
           displayItemForm: 'ItemForm', 
           displayReportForm: 'ReportForm',
           displayImportForm: 'ImportForm',
           displayCustomForm: 'CustomForm'
        };
        
        if (puid.status is nil)
        {
           if (action is nil)
           {
              if (puid.kind == 'reports')
              {
                 let action = 'displayReportForm';
              }
              else if (puid.kind == 'data_processors')
              {
                 let action = 'displayImportForm';
              }
              else let action = 'displayListForm';
           }
           
           var tpl_name = tpl_map[action];
        }
     }
  }}
  <eval:if test="puid is nil">
    <ul class="ae_errors">
      <li class="ae_error">Unknow entity</li>
    </ul>
  </eval:if>
  <eval:elseif test="puid.status == false">
    <ul class="ae_errors">
      <eval:foreach var="error" in="puid.errors">
        <li class="ae_error">{{ error }}</li>
      </eval:foreach>
    </ul>
  </eval:elseif>
  <eval:elseif test="tpl_name is nil">
    <ul class="ae_errors">
      <li class="ae_error">Unknow action</li>
    </ul>
  </eval:elseif>
  <eval:else>
    {{
      var inst_conf = extconfig.Fetch('installer');
      var js_path = inst_conf['base_dir']..inst_conf['framework_dir']..'/MindTouch/Js';
      
      &lt;html&gt;
        &lt;head&gt;
          &lt;script type="text/javascript" src=(js_path..'/oe_global.js')&gt;&lt;/script&gt;
          &lt;script type="text/javascript" src=(js_path..'/oe_print.js')&gt;&lt;/script&gt;
          &lt;script type="text/javascript"&gt;"
            OEF_PAGE_PATH = '"..page.path.."';
          "&lt;/script&gt;
        &lt;/head&gt;
        &lt;body&gt;&lt;/body&gt;
        &lt;tail&gt;&lt;/tail&gt;
      &lt;/html&gt;
      
      if (!isPopup)
      {       
        &lt;html&gt;
          &lt;head&gt;
            &lt;script type="text/javascript"&gt;"
              jQuery(window).unload(function() {
                 var popup = new oefPopup();
	             popup.closeAllWindow();
              });
            "&lt;/script&gt;
          &lt;/head&gt;
        &lt;/html&gt;
      }
      else
      {
            awpskin.hideAll();
      }
    }}
    <div class="oef_content">
      <eval:if test="action == 'displayCustomForm'">
        {{
           var template = root..'/'..tpl_name;
           var content  = wiki.template(template, [uid, puid, root, template, params, prefix]);
           
           if (string.contains(content, 'href="'..template..'"'))
           {
              let content = 'Template not found';
           }
           
           content;
           
           &lt;html&gt;
             &lt;head&gt;
               &lt;script type="text/javascript" src=(js_path..'/jquery.form.js')&gt;&lt;/script&gt;
               &lt;script type="text/javascript" src=(js_path..'/ae_edit_form.js')&gt;&lt;/script&gt;
               &lt;script type="text/javascript" src=(js_path..'/datetimepicker/datetimepicker.js')&gt;&lt;/script&gt;
             &lt;/head&gt;
             &lt;body&gt;&lt;/body&gt;
             &lt;tail&gt;
               &lt;script type="text/javascript"&gt;"
                 pageAPI = '"..page.api.."';
               "&lt;/script&gt;
             &lt;/tail&gt;
           &lt;/html&gt;
        }}
      </eval:if>
      <eval:elseif test="action == 'displayListForm'">
        {{
           if (#puid.main_kind != 0) {
              var kind = puid.main_kind..'.'..puid.main_type..'.'..puid.kind;
           }
           else {
              var kind = puid.kind;
           }
           
           if (kind != 'catalogs')
           {
              &lt;html&gt;
                &lt;head&gt;
                  &lt;script type="text/javascript" src=(js_path..'/jquery.form.js')&gt;&lt;/script&gt;
                  &lt;script type="text/javascript" src=(js_path..'/ae_list_form.js')&gt;&lt;/script&gt;
                &lt;/head&gt;
                &lt;body&gt;&lt;/body&gt;
                &lt;tail&gt;
                  &lt;script type="text/javascript"&gt;"
                    setInterval(\"updateListForm('"..page.api.."', null, 5000)\", 30000);
                  "&lt;/script&gt;
                &lt;/tail&gt;
              &lt;/html&gt;
           }
           else let params ..= {js_path: js_path};
           
           var template = root..'/'..string.ToUpperFirst(puid.kind)..'/'..tpl_name;
           var content  = wiki.template(template, [uid, puid, root, template, params, prefix]);
           
           if (string.contains(content, 'href="'..template..'"'))
           {
              let content = 'Template not found';
           }
           
           content;
        }}
      </eval:elseif>
      <eval:else>
        <pre class="script">
          var template = root..'/'..string.ToUpperFirst(puid.kind)..'/'..tpl_name;
          var content  = wiki.template(template, [uid, puid, root, template, params, prefix]);
          
          if (string.contains(content, 'href="'..template..'"'))
          {
             let content = 'Template not found';
          }
          
          content;
        </pre>
        <eval:if test="action == 'displayEditForm'">
          {{
             &lt;html&gt;
               &lt;head&gt;
                 &lt;script type="text/javascript" src=(js_path..'/oe_edit_box.js')&gt;&lt;/script&gt;
                 &lt;script type="text/javascript" src=(js_path..'/jquery.form.js')&gt;&lt;/script&gt;
                 &lt;script type="text/javascript" src=(js_path..'/ae_edit_form.js')&gt;&lt;/script&gt;
                 &lt;script type="text/javascript" src=(js_path..'/datetimepicker/datetimepicker.js')&gt;&lt;/script&gt;
               &lt;/head&gt;
               &lt;body&gt;&lt;/body&gt;
               &lt;tail&gt;
                 &lt;script type="text/javascript"&gt;"
                   pageAPI = '"..page.api.."';
                 "&lt;/script&gt;
               &lt;/tail&gt;
             &lt;/html&gt;
          }}
        </eval:if>
        <eval:elseif test="action == 'displayItemForm'">
          {{
             &lt;html&gt;
               &lt;head&gt;
                 &lt;script type="text/javascript" src=(js_path..'/ae_item_form.js')&gt;&lt;/script&gt;
               &lt;/head&gt;
               &lt;body&gt;&lt;/body&gt;
               &lt;tail&gt;
                 &lt;script type="text/javascript"&gt;"
                   jQuery(document).ready(function() { setInterval(\"updateItemForm('"..page.api.."', null, 5000)\", 30000); });
                 "&lt;/script&gt;
               &lt;/tail&gt;
             &lt;/html&gt;
          }}
        </eval:elseif>
        <eval:elseif test="action == 'displayReportForm'">
          {{
             &lt;html&gt;
               &lt;head&gt;
                 &lt;script type="text/javascript" src=(js_path..'/jquery.form.js')&gt;&lt;/script&gt;
                 &lt;script type="text/javascript" src=(js_path..'/ae_report_form.js')&gt;&lt;/script&gt;
                 &lt;script type="text/javascript" src=(js_path..'/datetimepicker/datetimepicker.js')&gt;&lt;/script&gt;
               &lt;/head&gt;
               &lt;body&gt;&lt;/body&gt;
               &lt;tail&gt;&lt;/tail&gt;
             &lt;/html&gt;
          }}
        </eval:elseif>
        <eval:elseif test="action == 'displayImportForm'">
          {{
             &lt;html&gt;
               &lt;head&gt;
                 &lt;script type="text/javascript" src=(js_path..'/jquery.form.js')&gt;&lt;/script&gt;
                 &lt;script type="text/javascript" src=(js_path..'/ae_import_form.js')&gt;&lt;/script&gt;
                 &lt;script type="text/javascript" src=(js_path..'/datetimepicker/datetimepicker.js')&gt;&lt;/script&gt;
               &lt;/head&gt;
               &lt;body&gt;&lt;/body&gt;
               &lt;tail&gt;&lt;/tail&gt;
             &lt;/html&gt;
          }}
        </eval:elseif>
      </eval:else>
    </div>
  </eval:else>
</eval:else>
