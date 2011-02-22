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
     
     var class = 'delete_marked_for_deletion';
  }}
  <h3>Delete marked for deletion</h3>
  <div class="{{ class..'_message systemmsg' }}" style="display: none;">
    <div class="inner">
      <ul class="flashMsg">
        <li>&nbsp;</li>
      </ul>
    </div>
  </div>
  <form method="post" action="#" id="{{ class..'_form' }}">
    <div id="oef_marked_for_deletion_container">
      <table>
      <eval:if test="data is map">
        <eval:foreach var="kind" in="map.keys(data)">
          <tr>
            <td colspan="2" class="oef_group_header">{{ string.toupperfirst(kind) }}</td>
          </tr>
          {{ var types = data[kind] }}
          <eval:foreach var="type" in="map.keys(types)">
            {{
               var params = types[type];
               var name_prefix = 'aeform['..kind..']['..type..']';
               var cnt = 0;
            }}
            <eval:foreach var="row" in="params">
              {{ var linkclass = 'oef_link oef_'..kind..'_'..type..'_'..row['value']; }}
              <tr class="{{ 'oef_marked_item oef_'..kind..'_'..type..'_item' }}">
                <td class="{{ 'oef_deletion_checkboxes'..(cnt % 2 == 0 ? ' oef_even' : ' oef_not_even') }}">
                  <input type="checkbox" name="{{ name_prefix..'[]' }}" value="{{ row['value'] ?? 0 }}" checked />
                </td>
                <td class="{{ 'oef_deletion_links'..(cnt % 2 == 0 ? ' oef_even' : ' oef_not_even') }}">
                  <a href="{{ page.path..'?uid='..kind..'.'..type..'&actions=displayItemForm&id='..row['value']}}" target="_blank" class="{{ linkclass }}">{{ row['text'] }}</a>
                </td>
              </tr>
              {{ let cnt += 1 }}
            </eval:foreach>
          </eval:foreach>
        </eval:foreach>
      </eval:if>
      <eval:else>
        {{ let data = false }}
      </eval:else>
      </table>
    </div>
    <div id="oef_related_entities_container">
      &nbsp;
    </div>
    <div>
      {{ &lt;input type="button" value="Check relations" class="ae_command" command="related" /&gt;&nbsp; }}
      {{ &lt;input type="button" value="Delete" class="ae_command" command="delete" /&gt;&nbsp; }}
      {{ &lt;input type="button" value="Unmarked for deletion" class="ae_command" command="unmarked" /&gt; }}
    </div>
  </form>
  {{
     &lt;html&gt;
       &lt;head&gt;
         &lt;script type="text/javascript" src=(js_path..'/oe_global.js')&gt;&lt;/script&gt;
         &lt;script type="text/javascript" src=(js_path..'/jquery.form.js')&gt;&lt;/script&gt;
         &lt;script type="text/javascript" src=(js_path..'/oe_deletion_form.js')&gt;&lt;/script&gt;
       &lt;/head&gt;
       &lt;body&gt;&lt;/body&gt;
       &lt;tail&gt;
       if (data == false) {
         &lt;script type="text/javascript"&gt;"disabledForm('#"..class.."_form');"&lt;/script&gt; 
       }
       &lt;/tail&gt;
     &lt;/html&gt;
  }}
</eval:else>
