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
   var periods  = [
      {value: '',             text: ' &nbsp;'},
      {value: 'This Week',    text: 'This Week'},
      {value: 'This Month',   text: 'This Month'},
      {value: 'This Quarter', text: 'This Quarter'},
      {value: 'This Year',    text: 'This Year'},
      {value: 'Last Week',    text: 'Last Week'},
      {value: 'Last Month',   text: 'Last Month'},
      {value: 'Last Quarter', text: 'Last Quarter'},
      {value: 'Last Year',    text: 'Last Year'}
   ];
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
      
      var class = string.replace(kind, '.', '_')..'_'..type;
  }}
  <h3 id="{{ class..'_header' }}">{{ string.ToUpperFirst(string.remove(puid.kind, string.length(puid.kind)-1, 1))..' '..type }}</h3>
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
    <eval:foreach var="field" in="fields">
      <tr>
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ string.ToUpperFirst(field); }}:</td>
        <td class="{{ class..'_value ae_editform_field_value' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          {{
             var name  = name_prefix..'[attributes]['..field..']';
             var value = headline[field] ? headline[field] : '';
          }}
          <eval:if test="field == 'Period'">
            <select name="{{ name_prefix..'[attributes]['..field..']' }}">
              <eval:foreach var="current" in="periods">
                <eval:if test="current.value == value">
                  <option value="{{ current.value }}" selected="selected">{{ current.text }}</option>
                </eval:if>
                <eval:else>
                  <option value="{{ current.value }}">{{ current.text }}</option>
                </eval:else>
              </eval:foreach>
            </select>
          </eval:if>
          <eval:else>
            <pre class="script">
              var params   = {select: select[field], required: list.contains(required, field), precision: field_prec[field]};
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
