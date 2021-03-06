{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var field_prec = {};
   var field_use  = {};
   var field_view = {};
   var required   = [];
   var dynamic    = {};
   var hierarchy  = {};
   var owners     = {};
   var references = [];
   var forms_view = {};
   var kind     = '';
   var type     = puid.type;
   var item     = data.item is map ? data.item : {};
   var select   = data.select;
   var tabulars = data.tabulars;
   
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
      
      var enctype = '';
      var name_prefix = 'aeform['..kind..']['..type..']';
      
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let field_use  = entities.getInternalConfiguration(kind..'.field_use', type);
      let field_view = entities.getInternalConfiguration(kind..'.field_view', type);
      let required   = entities.getInternalConfiguration(kind..'.required', type);
      let dynamic    = entities.getInternalConfiguration(kind..'.dynamic', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      let hierarchy  = entities.getInternalConfiguration(kind..'.hierarchy', type);
      let owners     = entities.getInternalConfiguration(kind..'.owners', type);
      let forms_view = entities.getInternalConfiguration(kind..'.forms_view', type);
      let forms_view = forms_view.EditForm ?? {};
      
      var tab_s = entities.getInternalConfiguration(kind..'.'..type..'.tabulars.tabulars');
      
      if (item._id > 0) {
         var header = 'Edit ';
         var hidden = '&lt;input type="hidden" name="'..name_prefix..'[attributes][_id]" value="'..item._id..'" /&gt;';
      }
      else {
         var header = 'New ';
         var hidden = '';
         
         if (hierarchy.type == 2) {
            let item ..= {_folder: (__request.args.type == 'group' ? 1 : 0)};
         }
      }
      
      if (hierarchy.type == 2) {
         if (item._folder == 1) {
            let fields = field_use[2];
         }
         else {
            let fields = field_use[1];
         }
         
         let hidden ..= '&lt;input type="hidden" name="'..name_prefix..'[attributes][_folder]" value="'..item._folder..'" /&gt;';
      }
      
      if (#map.select(field_type, "$.value=='file'") > 0)
      {
         let enctype = 'multipart/form-data';
      }
      
      var columns = forms_view.columns ?? fields;
      var class   = string.replace(kind, '.', '_')..'_'..type;
      var js_uid  = class;
  }}
  <form method="post" action="#" class="oe_custom_edit_form" id="{{ class..'_item' }}" enctype="{{ enctype }}">
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
    <eval:foreach var="field" in="columns">
      <eval:if test="#owners == 0 || field != 'OwnerId'">
        {{
           if (#owners > 0 && field == 'OwnerType') {
              var name   = name_prefix..'[attributes]';
              var value  = {OwnerType: item[field], OwnerId: item['OwnerId']};
              var f_name = 'Owner';
              var f_type = 'OwnerReference';
              var params = {
                 owners:    owners,
                 select:    select[field],
                 required:  list.contains(required, field),
                 dynamic:   list.contains(dynamic, field),
                 precision: field_prec[field],
                 view:      field_view[field]
              };
           }
           else {
              var name   = name_prefix..'[attributes]['..field..']';
              var value  = item[field];
              var f_name = field_view[field]['synonim'] ?? string.ToUpperFirst(field);
              var f_type = field_type[field];
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
           }
        }}      
        <tr>
          <td class="{{ class..'_name ae_editform_field_name' }}">{{ f_name }}:</td>
          <td class="{{ class..'_value ae_editform_field_value' }}">
            <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
            <pre class="script">
              var template   = root..'/EditFormFields';
              var content    = wiki.template(template, [f_type, name, value, params, type, template, prefix]);
              
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
             var template = root..'/Tabulars/EditForm';
             var tpl_params = [
               uid,
               {main_kind: puid.kind, main_type: puid.type, kind: 'tabulars', type: tabular},
               tabulars[tabular],
               root,
               template,
               {name_prefix: name_prefix..'[tabulars]'},
               prefix
             ];
             var content  = wiki.template(template, tpl_params);
      
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
        <td colspan="2">
          <div style="width: 702px;">
          {{
            &lt;div id="oef_custom_form"&gt;&nbsp;&lt;/div&gt;;
            &lt;script type="text/javascript"&gt;"
              displayCustomForm('"..uid.."', 'LoginRecords', {person: "..(item._id > 0 ? item._id : 0).."}, 'oef_custom_form');
              
              function onEndProcess(params)
              {
                 if (Context.getLastStatus())
                 {
                    displayCustomForm('"..uid.."', 'LoginRecords', {person: "..(item._id > 0 ? item._id : 0).."}, 'oef_custom_form');
                 }
              }
              
              Context.addListener('"..js_uid.."_end_process', onEndProcess);
            "&lt;/script&gt;;
          }}
          </div>
        </td>
      </tr>
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