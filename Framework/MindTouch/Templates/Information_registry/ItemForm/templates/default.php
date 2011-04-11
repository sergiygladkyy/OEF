{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var references = {}; 
   var kind     = '';
   var type     = puid.type;
   var item     = data.item;

   var tmpList = string.Split(uid,'.');
   var header = string.Remove(string.ToUpperFirst(tmpList[0]),string.Length(tmpList[0])-1,1)..' '..tmpList[1];
}}
<h3>{{header;}}</h3>
<eval:if test="puid is nil">
  <ul class="ae_errors">
    <li class="ae_error">Unknow entity</li>
  </ul>
</eval:if>
<eval:elseif test="item._id &lt; 1">
  <ul class="ae_errors">
    <li class="ae_error">Invalid entity id</li>
  </ul>
</eval:elseif>
<eval:else>
  {{
      if (#puid.main_kind != 0) {
         let kind = puid.main_kind..'.'..puid.main_type..'.'..puid.kind;
      }
      else {
         let kind = puid.kind;
      }
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
  }}
  <table>
  <tbody>
    <eval:foreach var="field" in="fields">
      <tr>
        <td class="ae_itemform_field_name">{{ string.ToUpperFirst(field); }}:</td>
        <td class="ae_itemform_field_value">
          <pre class="script">
            var params = {reference: references[field]};
            
            var template = root..'/ItemFormFields';
            var content  = wiki.template(template, [field_type[field], item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
      <tr>
        <td class="ae_edit" colspan="2">
          <a href="#" target="_self" onclick="{{ 'javascript: editItem(this, \''..kind..'\', \''..type..'\', '..item._id..'); return false;' }}">Edit</a>&nbsp;|
          <a href="#" onclick="{{ 'window.self.close(); if (window.opener && window.opener.length) { window.opener.focus(); } return false;' }}">Close</a>
        </td>
      </tr>
  </tbody>
  </table>
</eval:else>

