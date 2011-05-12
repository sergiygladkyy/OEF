{{
   var uid     = args[0];
   var puid    = args[1];
   var root    = args[2];
   var current = args[3];
   var params  = args[4];
   var prefix  = args[5] ?? 'default';
   var kind    = puid.kind;
   var type    = puid.type ?? 'null';
   var data    = entities.displayConstantsForm();
}}
<eval:if test="data.status != True">
  <ul class="ae_errors">
    <eval:foreach var="error" in="data.errors">
      <li class="ae_error">{{ error }}</li>
    </eval:foreach>
  </ul>
</eval:if>
<eval:else>
  {{
     var inst_conf = extconfig.Fetch('installer');
     var js_path   = inst_conf['base_dir']..inst_conf['framework_dir']..'/MindTouch/Js';
   
     var header = string.ToUpperFirst(kind);
   
     let data = data.result;
     var name_prefix = 'aeform['..kind..']';
     var field_type  = entities.getInternalConfiguration(kind..'.field_type', type);
     var field_prec  = entities.getInternalConfiguration(kind..'.field_prec', type);
     var fields      = entities.getInternalConfiguration(kind..'.fields', type);
     var required    = entities.getInternalConfiguration(kind..'.required', type);
     var references  = entities.getInternalConfiguration(kind..'.references', type);
     var item   = data.item is map ? data.item : {};
     var select = data.select;
     var class  = string.replace(kind, '.', '_');
     var js_uid = class;
  }}
  <h3>{{header;}}</h3>
  <form method="post" action="#" class="ae_constants_edit_form" id="{{ class..'_item' }}">
    <div class="{{ class..'_message systemmsg' }}" style="display: none;">
      <div class="inner">
        <ul class="flashMsg">
          <li>&nbsp;</li>
        </ul>
      </div>
    </div>
    <table>
    <tbody>
    <eval:foreach var="field" in="fields">
      <tr>
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ string.ToUpperFirst(field); }}:</td>
        <td class="{{ class..'_value ae_editform_field_value' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {select: select[field], required: list.contains(required, field), precision: field_prec[field]};
            
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
      <tr>
        <td class="ae_submit" colspan="2">
          {{ &lt;input type="button" value="Save" class="ae_command" command="save" /&gt;&nbsp; }}
        </td>
      </tr>
    </tbody>
    </table>
  </form>
  {{ &lt;script type="text/javascript"&gt;" ae_name_prefix[\'"..js_uid.."\'] = \'"..name_prefix.."[attributes]\';"&lt;/script&gt; }}
  {{
     &lt;html&gt;
       &lt;head&gt;
         &lt;script type="text/javascript" src=(js_path..'/oe_global.js')&gt;&lt;/script&gt;
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
</eval:else>
