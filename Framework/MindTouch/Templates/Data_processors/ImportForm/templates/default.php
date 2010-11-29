{{
   var uid      = args[0];
   var puid     = args[1];
   var data     = args[2];
   var root     = args[3] ?? 'Template:Entities';
   var prefix   = args[4] ?? 'default';
   var fields   = {};
   var field_type = {};
   var field_prec = {};
   var required = []; 
   var kind     = '';
   var type     = puid.type;
   var select   = data.select;
   var report   = data.report ?? nil;
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
      
      var class  = string.replace(kind, '.', '_')..'_'..type;
  }}
  <form method="post" action="#" class="ae_import_form" id="{{ class..'_import' }}">
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
          
            var template = root..'/EditFormFields';
            var content  = wiki.template(template, [field_type[field], name, nil, params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
      <tr>
        <td class="ae_submit" colspan="2"><input type="submit" name="submit" value="Import" /></td>
      </tr>
    </tbody>
    </table>
  </form>
</eval:else>
