{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var owners     = {};
   var references = {}; 
   var kind     = '';
   var type     = puid.type;
   var item     = data.item;
   var tabulars = data.tabulars;
   
   var tmpList = string.Split(uid,'.');
   var header  = string.Remove(string.ToUpperFirst(tmpList[0]),string.Length(tmpList[0])-1,1)..' '..tmpList[1];
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
      let owners     = entities.getInternalConfiguration(kind..'.owners', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      var tab_s      = entities.getInternalConfiguration(kind..'.'..type..'.tabulars.tabulars');
  }}
  <eval:if test="item._deleted != 0">
  <div  style="float: right; margin: 30px 20px 0px 10px; font-size:12px; font-weight: bold; color: rgb(218, 6, 32);">Mark for deletion</div>
  </eval:if>
  <div style="clear: both; height: 0;">&nbsp;</div>
  <table>
  <tbody>
    <eval:foreach var="field" in="fields">
      <eval:if test="#owners == 0 || field != 'OwnerId'">
        {{
           if (field == 'OwnerType' && #owners > 0) {
              var value  = item['OwnerId'];
              var params = {reference: {kind: kind, type: item[field]}};
              var f_name = 'Owner';
              var c_field_type = 'reference';
           }
           else {
              var value  = item[field];
              var params = {reference: references[field]};
              var f_name = string.ToUpperFirst(field);
              var c_field_type = field_type[field];
           }
        }}
        <tr>
          <td class="ae_itemform_field_name">{{ f_name }}:</td>
          <td class="ae_itemform_field_value">
            <pre class="script">
              var template = root..'/ItemFormFields';
              var content  = wiki.template(template, [c_field_type, value, params, type, template, prefix]);
              
              if (string.contains(content, 'href="'..template..'"')) {
                 let content = 'Template not found';
              }
              
              content;
            </pre>
          </td>
        </tr>
      </eval:if>
    </eval:foreach>
    <eval:foreach var="tabular" in="tab_s">
      <tr>
        <td colspan="2">
          {{
             var template = root..'/Tabulars/ItemForm';
             var tpl_params = [
               uid,
               {main_kind: puid.kind, main_type: puid.type, kind: 'tabulars', type: tabular},
               tabulars[tabular],
               root,
               template,
               [],
               prefix
             ];
             var content = wiki.template(template, tpl_params);
      
             if (string.contains(content, 'href="'..template..'"'))
             {
                let content = 'Template not found';
             }
          
             content;
          }}
        </td>
      </tr>
    </eval:foreach>
      <tr>
        <td class="ae_edit" colspan="2"><a href="{{ page.path..'?uid='..uid..'&actions=displayEditForm&id='..item._id }}">Edit</a></td>
      </tr>
  </tbody>
  </table>
</eval:else>
