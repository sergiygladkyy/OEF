{{
   var uid      = args[0];
   var puid     = args[1];
   var headline = args[2] ?? {};
   var data     = args[3];
   var root     = args[4] ?? 'Template:Entities';
   var prefix   = args[5] ?? 'default';
   var fields   = {};
   var field_type = {};
   var field_prec = {};
   var required = []; 
   var kind     = '';
   var type     = puid.type;
   var select   = data.select;
   var report   = data.report ?? nil;

   var inst_conf = extconfig.Fetch('installer');
   var js_path   = inst_conf['base_dir']..inst_conf['framework_dir']..'/MindTouch/Js';
   
   &lt;html&gt;
     &lt;head&gt;
       &lt;script type="text/javascript" src=(js_path..'/oe_multiselect/oe_multiselect.js')&gt;&lt;/script&gt;
       &lt;link   type="text/css" rel="stylesheet" href=(js_path..'/oe_multiselect/oe_multiselect.css')&gt;&lt;/link&gt;
     &lt;/head&gt;
     &lt;body&gt;&lt;/body&gt;
     &lt;tail&gt;&lt;/tail&gt;
   &lt;/html&gt;
}}
<eval:if test="puid is nil">
  <ul class="ae_errors">
    <li class="ae_error">Unknow entity</li>
  </ul>
</eval:if>
<eval:else>
  {{
      if (#puid.main_kind != 0) {
         let kind = puid.main_kind..'.'..puid.main_type..'.'..puid.kind;
      }
      else {
         let kind = puid.kind;
      }
      var name_prefix = 'aeform['..type..']';
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let fields   = entities.getInternalConfiguration(kind..'.fields', type);
      let required = entities.getInternalConfiguration(kind..'.required', type);
      
      let fields = list.select(fields, "$ != 'Projects'");
      var class  = string.replace(kind, '.', '_')..'_'..type;
  }}
  <form method="post" action="#" class="ae_report_form" id="{{ class..'_report' }}">
    <div class="{{ class..'_message systemmsg' }}" style="display: none;">
      <div class="inner">
        <ul class="flashMsg">
          <li>&nbsp;</li>
        </ul>
      </div>
    </div>
    <table>
    <tbody>
      <tr>
        <td colspan="2">
          <fieldset>
            <legend><b>Projects:</b></legend>
            <div id="oe_report_project_performance_projects" style="padding: 3px 0 10px 0;">&nbsp;</div>
            {{
               &lt;script type="text/javascript"&gt;"
                  var oeMSelect = new oeMultiselect();
                  var options = {
	                 tag_id: 'oe_report_project_performance_projects',
	                 name: '"..name_prefix.."[attributes][ex_projects][]'
                  };
                  var data = {list: "..json.emit(select['Projects']).."};
                  oeMSelect.showMultiselect(data, options);
               "&lt;/script&gt;
            }}
          </fieldset>
        </td>
      </tr>
    <eval:foreach var="field" in="fields">
      <tr>
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ string.ToUpperFirst(field); }}:</td>
        <td class="{{ class..'_value ae_editform_field_value' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {select: select[field], required: list.contains(required, field), precision: field_prec[field]};
          
            var template = root..'/EditFormFields';
            var value    = headline[field] ? headline[field] : '';
            var content  = wiki.template(template, [field_type[field], name, value, params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
      <tr>
        <td class="ae_submit" colspan="2"><input type="submit" name="submit" value="Generate" /></td>
      </tr>
    </tbody>
    </table>
  </form>
  {{ 
     &lt;div class="ae_report" type=(type)&gt;
     report.output is nil ? '&nbsp;' : web.html(report.output);
     &lt;/div&gt;
  }}
</eval:else>
