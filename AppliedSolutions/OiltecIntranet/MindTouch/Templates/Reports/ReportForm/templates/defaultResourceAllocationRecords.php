{{
   var uid      = args[0];
   var puid     = args[1];
   var headline = args[2] ?? {};
   var data     = args[3];
   var root     = args[4] ?? 'Template:Entities';
   var prefix   = args[5] ?? 'default';
   var fields     = {};
   var field_type = {};
   var field_prec = {};
   var field_view = {};
   var required   = [];
   var dynamic    = {};
   var references = [];
   var forms_view = {};
   var kind     = '';
   var type     = puid.type;
   var select   = data.select;
   var report   = data.report ?? nil;
   
   var tmpList = string.Split(uid,'.');
   var header = string.Remove(string.ToUpperFirst(tmpList[0]),string.Length(tmpList[0])-1,1)..' '..tmpList[1];
}}
<h3>{{header;}}</h3>
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
      
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let field_view = entities.getInternalConfiguration(kind..'.field_view', type);
      let required   = entities.getInternalConfiguration(kind..'.required', type);
      let dynamic    = entities.getInternalConfiguration(kind..'.dynamic', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      let forms_view = entities.getInternalConfiguration(kind..'.forms_view', type);
      let forms_view = forms_view.ReportForm ?? {};
      
      let field_type ..= {Period: 'date'};
      
      var columns = forms_view.columns ?? fields;
      var class   = string.replace(kind, '.', '_')..'_'..type;
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
    <eval:foreach var="field" in="columns">
      <tr>
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ field_view[field]['synonim'] ?? string.ToUpperFirst(field) }}:</td>
        <td class="{{ class..'_value ae_editform_field_value' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field],
               view:      field_view[field]
            };
          
            if (references[field]) {
              let params ..= {reference: references[field]};
            }
            
            var template = root..'/EditFormFields';
            var value    = headline[field] ? headline[field] : '';
            var content  = wiki.template(template, [field_type[field], name, value, params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
          <eval:if test="field == 'Period'">
            <img src="/skins/common/icons/silk/help.png" style="position: relative; left: 13px; top: 2px;" title="You can use periods: “Last Year”, “Last Month”, “Last Quarter”, “Last Week”, “This Year”, “This Month”, “This Quarter”, “This Week”, “Today”, “Next Week”, “Next Month”, “Next Quarter”, “Next Year”">
          </eval:if>
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
     report.output is nil ? '&nbsp;' : web.xml(report.output);
     &lt;/div&gt;
  }}
</eval:else>
