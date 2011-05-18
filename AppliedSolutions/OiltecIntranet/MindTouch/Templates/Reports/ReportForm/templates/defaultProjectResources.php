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
   var forms_view = {};
   var kind     = '';
   var type     = puid.type;
   var select   = data.select;
   var report   = data.report ?? nil;
   var dates    = entities.executeQuery("SELECT Date FROM information_registry.ProjectTimeRecords GROUP BY Date ORDER BY Date ASC");
}}
<eval:if test="puid is nil">
  <ul class="ae_errors">
    <li class="ae_error">Unknow entity</li>
  </ul>
</eval:if>
<eval:elseif test="dates.status != True">
  <ul class="ae_errors">
    <li class="ae_error">Can not get list of valid date</li>
  </ul>
</eval:elseif>
<eval:elseif test="#dates.result &lt; 1">
  <span>Nothing was imported</span>
</eval:elseif>
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
      let forms_view = entities.getInternalConfiguration(kind..'.forms_view', type);
      let forms_view = forms_view.ReportForm ?? {};
      
      let dates = dates.result;
      
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
          {{
             var name  = name_prefix..'[attributes]['..field..']';
             var value = headline[field] ? headline[field] : '';
          }}
          <eval:if test="field == 'Date'">
            <select name="{{ name_prefix..'[attributes]['..field..']' }}">
              <eval:foreach var="current" in="dates">
                <eval:if test="date == value">
                  <option value="{{ current.Date }}" selected="selected">{{ entities.GetFormattedDate(current.Date, '%d %b %Y') }}</option>
                </eval:if>
                <eval:else>
                  <option value="{{ current.Date }}">{{ entities.GetFormattedDate(current.Date, '%d %b %Y') }}</option>
                </eval:else>
              </eval:foreach>
            </select>
          </eval:if>
          <eval:else>
            <pre class="script">
              var params = {
                 select:    select[field],
                 required:  list.contains(required, field),
                 precision: field_prec[field],
                 view:      field_view[field]
              };
              var template = root..'/EditFormFields';
              var content  = wiki.template(template, [field_type[field], name, value, params, type, template, prefix]);
              
              if (string.contains(content, 'href="'..template..'"')) {
                let content = 'Template not found';
              }
              
              content;
            </pre>
          </eval:else>
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
