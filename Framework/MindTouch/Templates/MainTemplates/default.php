{{
   var uid      = __request.args.uid ?? args[0];
   var action   = __request.args.actions ?? args[1];
   var params   = args[2] ?? {};
   var prefix   = args[3] ?? 'default';
   var puid     = #uid > 0 ? entities.parseUID(uid) : nil;
   var root     = 'Template:Entities';
   var tpl_map  = {
      displayListForm: 'ListForm', 
      displayEditForm: 'EditForm', 
      displayItemForm: 'ItemForm', 
      displayReportForm: 'ReportForm',
      displayImportForm: 'ImportForm'
   };
   
   if (action is nil)
   {
      if (puid.status is nil)
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
      else let action = 'displayListForm';
   }
   
   var tpl_name = tpl_map[action];
   
   var inst_conf = extconfig.Fetch('installer');
   var js_path = inst_conf['base_dir']..inst_conf['framework_dir']..'/MindTouch/Js';
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
  <div class="oef_content">
    <pre class="script">
      var template = root..'/'..string.ToUpperFirst(puid.kind)..'/'..tpl_name;
      var content  = wiki.template(template, [uid, puid, root, template, params, prefix]);
      
      if (string.contains(content, 'href="'..template..'"'))
      {
         let content = 'Template not found';
      }
      
      content;
    </pre>
  </div>
  <eval:if test="action == 'displayEditForm'">
    {{
       &lt;html&gt;
         &lt;head&gt;
           &lt;script type="text/javascript" src=(js_path..'/jquery.form.js')&gt;&lt;/script&gt;
           &lt;script type="text/javascript" src=(js_path..'/ae_edit_form.js')&gt;&lt;/script&gt;
           &lt;script type="text/javascript" src=(js_path..'/datetimepicker/datetimepicker.js')&gt;&lt;/script&gt;
         &lt;/head&gt;
         &lt;body&gt;&lt;/body&gt;
         &lt;tail&gt;&lt;/tail&gt;
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
             jQuery(document).ready(function() { setInterval(\"updateItemForm('"..page.api.."', null, 3000)\", 5000); });
           "&lt;/script&gt;
         &lt;/tail&gt;
       &lt;/html&gt;
    }}
  </eval:elseif>
  <eval:elseif test="action == 'displayListForm'">
    {{
       &lt;html&gt;
         &lt;head&gt;
           &lt;script type="text/javascript" src=(js_path..'/jquery.form.js')&gt;&lt;/script&gt;
           &lt;script type="text/javascript" src=(js_path..'/ae_list_form.js')&gt;&lt;/script&gt;
         &lt;/head&gt;
         &lt;body&gt;&lt;/body&gt;
         &lt;tail&gt;
           &lt;script type="text/javascript"&gt;"
             setInterval(\"updateListForm('"..page.api.."', null, 3000)\", 5000);
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
