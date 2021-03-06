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
    var field_view  = data.field_view;
    var references  = data.references;
    var required    = data.required;
    var dynamic     = data.dynamic;
    var name_prefix = data.name_prefix ?? 'aeform['..kind..']';
    var forms_view  = data.forms_view;
    var i = data.numb;
    
    var columns = forms_view.columns ?? fields;
}}
<tr class="tabular_item" id="{{ class..'_'..i..'_item' }}">
  <eval:if test="item._id &gt; 0">
    <input type="hidden" name="{{ name_prefix..'['..i..'][_id]' }}" value="{{ item._id }}" />
  </eval:if>
  <td style="vertical-align: middle !important;"><input type="checkbox" name="{{ name_prefix..'[ids][]' }}" value="{{ item._id ?? 0 }}" /></td>
  <eval:foreach var="field" in="columns">
    {{ var name = name_prefix..'['..i..']['..field..']'; }}
    <eval:if test="field != 'Owner'">
      {{ var _prefix = class..'_'..i..'_'..field; }}
      <td class="tabular_col">
        <ul class="{{ class..'_'..i..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
        <pre class="script">
          var params = {
             select:    select[field],
             required:  list.contains(required, field),
             dynamic:   list.contains(dynamic, field),
             precision: field_prec[field],
             view:      field_view[field],
             attrs:     {id: _prefix..'_field', style: 'width: 99%; height: 54px;'}
          };
            
          if (references[field]) {
            let params ..= {reference: references[field]};
          }
          
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
