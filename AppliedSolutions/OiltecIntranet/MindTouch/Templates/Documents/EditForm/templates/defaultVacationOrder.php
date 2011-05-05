{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var field_prec = {};
   var required = []; 
   var kind     = '';
   var type     = puid.type;
   var item     = data.item is map ? data.item : {};
   var select   = data.select;
   var tabulars = data.tabulars;

   var tmpList = string.Split(uid,'.');
   var header = string.Remove(string.ToUpperFirst(tmpList[0]),string.Length(tmpList[0])-1,1)..' '..tmpList[1];
}}
<eval:if test="puid is nil">
  <ul class="ae_errors">
    <li class="ae_error">Unknow entity</li>
  </ul>
</eval:if>
<eval:else>
  <h3>{{header;}}</h3>
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
      let fields   = entities.getInternalConfiguration(kind..'.fields', type);
      let required = entities.getInternalConfiguration(kind..'.required', type);
      
      var tab_s    = entities.getInternalConfiguration(kind..'.'..type..'.tabulars.tabulars');
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
  <div class="{{ class..'_message systemmsg' }}" style="display: none;">
    <div class="inner">
      <ul class="flashMsg">
        <li>&nbsp;</li>
      </ul>
    </div>
  </div>
  <div class="{{ class..'_actions ae_editform_actions' }}" style="{{ item._id &gt; 0 ? 'display: block;' : 'display: none;' }}">
    &nbsp;
  </div>
  <form method="post" action="#" class="ae_object_edit_form" id="{{ class..'_item' }}">
    {{ web.html(hidden) }}
    <table>
    <tbody>
      <tr id="{{ class..'_post_flag' }}" style="{{ item._id &gt; 0 ? '' : 'display: none;' }}">
        <td class="{{ class..'_name ae_editform_field_name' }}">Posted:</td>
        <td class="{{ class..'_value ae_editform_field_value' }}">
          <div class="{{ item._post &gt; 0 ? 'ae_field_posted' : 'ae_field_not_posted' }}">
            <span class="ae_field_posted_text" style="{{ item._post &gt; 0 ? 'display: block;' : 'display: none;' }}">This document is posted.</span>
            <span class="ae_field_not_posted_text" style="{{ item._post &gt; 0 ? 'display: none;' : 'display: block;' }}">This document is not posted.</span>
          </div>
        </td>
      </tr>
    <eval:foreach var="field" in="fields">
      <tr>
        <td class="{{ class..'_name ae_editform_field_name' }}">{{ string.ToUpperFirst(field); }}:</td>
        <td class="{{ class..'_value ae_editform_field_value' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {select: select[field], required: list.contains(required, field), precision: field_prec[field]};
          
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
        <td class="ae_submit" colspan="2">
          <eval:if test="tabulars.Employees.status == True && #tabulars.Employees.result.list == 0">
            <style type="text/css">
              #oe_fill_by_department {
                position: fixed;
	            width: 160px;
	            top: 50%;
	            left: 50%;
	            margin: -11% 0 0 -90px;
	            background-color: #F7F7F7;
	            padding: 6px 10px;
	            z-index: 1000;
	            border-radius: 5px;
	            -moz-border-radius: 8px;
	          }
	          #oe_fill_by_department .oe_label {
	            color: #555555;
	          }
	          #oe_fill_by_department .oe_select {
	            padding: 10px 0 13px 0;
	          }
	          #oe_fill_by_department select {
	            width: 160px;
	          }
	          #oe_fill_by_department .oe_buttons {
	            text-align: center;
	          }
            </style>
            <div id="oe_fill_by_department" style="display: none;">
              <div class="oe_label">Choose department:</div>
              <div class="oe_select">
                <pre class="script">
                  var name   = name_prefix..'[department]';
                  var params = {select: select['department'], required: true, precision: {}};
                  
                  var template   = root..'/EditFormFields';
                  var content    = wiki.template(template, ['reference', name, item['department'], params, type, template, prefix]);
                  
                  if (string.contains(content, 'href="'..template..'"')) {
                    let content = 'Template not found';
                  }
                  
                  content;
                </pre>
              </div>
              <div class="oe_buttons">
                {{
                   var fClick = "javascript: jQuery('#oe_fill_by_department').css('display','none'); ";
                   let fClick = fClick.."notifyFormEvent('"..uid.."', 'Default', 'onFormUpdateRequest', {'action': 'Fill'});";
                }}
                <input type="button" value="Fill" onclick="{{ fClick }}" />&nbsp;
                <input type="button" value="Cancel" onclick="jQuery('#oe_fill_by_department').css('display','none'); appActive();" />
              </div>
            </div>
            {{
               let fClick = "javascript: appInactive(); ";
               let fClick = fClick.."if (confirm('Tabular section Employees will be cleared. Continue?')) { ";
               let fClick = fClick.." jQuery('#oe_fill_by_department').css('display','block'); }";
               let fClick = fClick.."else { appActive(); }";
            }}
            <input type="button" value="Fill by Department" onclick="{{ fClick }}" />
          </eval:if>
          <input type="button" value="Calculate" onclick="{{ 'javascript: notifyFormEvent(\''..uid..'\', \'Default\', \'onFormUpdateRequest\', {\'action\': \'Calculate\'});' }}" />
          {{ &lt;input type="button" value="Save and Close" class="ae_command" command="save_and_close" /&gt;&nbsp; }}
          {{ &lt;input type="button" value="Save" class="ae_command" command="save" /&gt;&nbsp; }}
          {{ &lt;input type="button" value="Close" class="ae_command" command="cancel" /&gt; }}
        </td>
      </tr>
    </tbody>
    </table>
  </form>
  {{ &lt;script type="text/javascript"&gt;" ae_name_prefix[\'"..js_uid.."\'] = \'"..name_prefix.."[attributes]\';"&lt;/script&gt; }}
  <eval:if test="item._id &gt; 0">
    {{ &lt;script type="text/javascript"&gt;" generateActionsMenu('."..class.."_actions', '"..kind.."', '"..type.."', "..item._id..");"&lt;/script&gt; }}
  </eval:if>
  <eval:if test="item._post &gt; 0">
    {{ 
       &lt;script type="text/javascript"&gt;"
         disabledForm('#"..class.."_item');
         displayMessage('"..class.."', 'To edit the document you must &lt;a href=\"#\" onclick=\"javascript:clearPosting(\\\'"..kind.."\\\', \\\'"..type.."\\\', "..item._id..", \\\'"..class.."\\\'); return false;\"&gt;clear posting&lt;/a&gt;', 2);
       "&lt;/script&gt;
    }}
  </eval:if>
</eval:else>
