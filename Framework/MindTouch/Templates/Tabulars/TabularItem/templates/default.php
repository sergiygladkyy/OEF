{{
    var kind   = args[0];
    var type   = args[1];
    var data   = args[2];
    var root   = args[3] ?? 'Template:Entities';
    var prefix = args[4] ?? 'default';
    
    var item    = data.item;
    var select  = data.select;
    var links   = data.links;
    var class   = data.class;
    var fields  = data.fields;
    var field_type  = data.field_type;
    var field_prec  = data.field_prec;
    var references  = data.references;
    var required    = data.required;
    var name_prefix = data.name_prefix ?? 'aeform['..kind..']';
    var i = data.numb;
}}
<tr class="tabular_item" id="{{ class..'_'..i..'_item' }}">
  <eval:if test="item._id &gt; 0">
    <input type="hidden" name="{{ name_prefix..'['..i..'][_id]' }}" value="{{ item._id }}" />
  </eval:if>
  <td><input type="checkbox" name="{{ name_prefix..'[ids][]' }}" value="{{ item._id ?? 0 }}" /></td>
  <eval:foreach var="field" in="fields">
    {{ var name = name_prefix..'['..i..']['..field..']'; }}
    <eval:if test="field != 'Owner'">
      {{ var _prefix = class..'_'..i..'_'..field; }}
      <td class="tabular_col">
        <ul class="{{ class..'_'..i..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
        <pre class="script">
          var params = {select: select[field], required: list.contains(required, field), precision: field_prec[field], attrs: {id: _prefix..'_field'}};
          
          var template = root..'/EditFormFields';
          var content  = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
          if (string.contains(content, 'href="'..template..'"')) {
             let content = 'Template not found';
          }
          
          content;
        </pre>
      </td>
    </eval:if>
  </eval:foreach>
</tr>
