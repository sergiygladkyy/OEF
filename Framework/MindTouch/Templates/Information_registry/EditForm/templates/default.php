{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var field_prec = {};
   var required   = [];
   var dynamic    = {};
   var references = [];
   var kind     = '';
   var type     = puid.type;
   var item     = data.item is map ? data.item : {};
   var select   = data.select;
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
      var name_prefix = 'aeform['..kind..']['..type..']';
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let required   = entities.getInternalConfiguration(kind..'.required', type);
      let dynamic    = entities.getInternalConfiguration(kind..'.dynamic', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      
      if (item._id > 0) {
         var header = 'Edit ';
         var hidden = '&lt;input type="hidden" name="'..name_prefix..'[attributes][_id]" value="'..item._id..'" /&gt;';
      }
      else {
         var header = 'New ';
         var hidden = '';
      }
      
      var class  = string.replace(kind, '.', '_')..'_'..type;
      var js_uid = class;
  }}
  <form method="post" action="#" class="ae_edit_form" id="{{ class..'_item' }}">
    {{ web.html(hidden) }}
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
            var params = {
               select:    select[field],
               required:  list.contains(required, field),
               dynamic:   list.contains(dynamic, field),
               precision: field_prec[field]
            };
            
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
          {{ &lt;input type="button" value="Save and Close" class="ae_command" command="save_and_close" /&gt;&nbsp; }}
          {{ &lt;input type="button" value="Save" class="ae_command" command="save" /&gt;&nbsp; }}
          {{ &lt;input type="button" value="Close" class="ae_command" command="cancel" /&gt; }}
        </td>
      </tr>
    </tbody>
    </table>
  </form>
  {{ &lt;script type="text/javascript"&gt;" ae_name_prefix[\'"..js_uid.."\'] = \'"..name_prefix.."[attributes]\';"&lt;/script&gt; }}
</eval:else>