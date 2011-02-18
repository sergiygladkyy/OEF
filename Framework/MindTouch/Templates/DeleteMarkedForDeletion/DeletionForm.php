{{
   var uid     = args[0];
   var puid    = args[1];
   var root    = args[2];
   var current = args[3];
   var params  = args[4];
   var prefix  = args[5] ?? 'default';
   var data    = nil;
   let data    = entities.displayDeletionForm();
}}
<eval:if test="data is nil">
  <ul class="ae_errors">
    <li class="ae_error">DekiExt Error</li>
  </ul>
</eval:if>
<eval:elseif test="data.status != True">
  <ul class="ae_errors">
    <eval:foreach var="error" in="data.errors">
      <li class="ae_error">{{ error }}</li>
    </eval:foreach>
  </ul>
</eval:elseif>
<eval:else>
  {{
     var inst_conf = extconfig.Fetch('installer');
     var js_path   = inst_conf['base_dir']..inst_conf['framework_dir']..'/MindTouch/Js';
     
     let data = data.result;
     
     var class   = 'delete_marked_for_deletion';
     var js_uid  = class;
  }}
  <h3>Delete marked for deletion</h3>
  <div class="{{ class..'_message systemmsg' }}" style="display: none;">
    <div class="inner">
      <ul class="flashMsg">
        <li>&nbsp;</li>
      </ul>
    </div>
  </div>
  <form method="post" action="#" id="{{ class..'_item' }}">
    <div id="oef_marked_for_deletion_container">
      <table>
      <eval:foreach var="kind" in="map.keys(data)">
        <tr>
          <td colspan="2" class="oef_group_header">{{ string.toupperfirst(kind) }}</td>
        <tr>
        {{ var types = data[kind] }}
        <eval:foreach var="type" in="map.keys(types)">
          {{
             var params = types[type];
             var name_prefix = 'aeform['..kind..']['..type..']';
          }}
          <eval:foreach var="row" in="params">
            <tr>
              <td>
                <input type="checkbox" name="{{ name_prefix..'[ids][]' }}" value="{{ row['value'] ?? 0 }}" checked />
              </td>
              <td>
                <a href="{{ page.path..'?uid='..kind..'.'..type..'&actions=displayEditForm&id='..row['value']}}" target="_blank" class="oef_msg_link">{{ row['text'] }}</a>
              </td>
            </tr>
          </eval:foreach>
        </eval:foreach>
      </eval:foreach>
      </table>
    </div>
    <div id="oef_related_entities_container">
    </div>
    <div>
      {{ &lt;input type="button" value="Save and Close" class="ae_command" command="save_and_close" /&gt;&nbsp; }}
      {{ &lt;input type="button" value="Save" class="ae_command" command="save" /&gt;&nbsp; }}
      {{ &lt;input type="button" value="Close" class="ae_command" command="cancel" /&gt; }}
    </div>
  </form>
  {{ &lt;script type="text/javascript"&gt;" ae_name_prefix[\'"..js_uid.."\'] = \'"..name_prefix.."[attributes]\';"&lt;/script&gt; }}
  {{
     &lt;html&gt;
       &lt;head&gt;
         &lt;script type="text/javascript" src=(js_path..'/oe_global.js')&gt;&lt;/script&gt;
         &lt;script type="text/javascript" src=(js_path..'/jquery.form.js')&gt;&lt;/script&gt;
         &lt;script type="text/javascript" src=(js_path..'/ae_edit_form.js')&gt;&lt;/script&gt;
         &lt;script type="text/javascript" src=(js_path..'/datetimepicker/datetimepicker.js')&gt;&lt;/script&gt;
       &lt;/head&gt;
       &lt;body&gt;&lt;/body&gt;
       &lt;tail&gt;
         &lt;script type="text/javascript"&gt;"
           pageAPI = '"..page.api.."';
         "&lt;/script&gt;
       &lt;/tail&gt;
     &lt;/html&gt;
  }}
</eval:else>
