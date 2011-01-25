{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
}}
<eval:if test="puid is nil">
  <ul class="ae_errors">
    <li class="ae_error">Unknow entity</li>
  </ul>
</eval:if>
<eval:else>
  <div class="{{ puid.kind..'_'..puid.type..'_message systemmsg' }}" style="display: none;">
    <div class="inner">
      <ul class="flashMsg">
        <li>&nbsp;</li>
      </ul>
    </div>
  </div>
  {{
     var item = data.item is map ? data.item : {};
     var name_prefix = 'aeform['..puid.kind..']['..puid.type..']';
     var js_uid = puid.kind..'_'..puid.type;
     
     &lt;div id="oef_custom_time_card_form"&gt;&nbsp;&lt;/div&gt;;
     &lt;script type="text/javascript"&gt;"displayCustomForm('"..uid.."', 'TimeCard', {document: "..(item._id > 0 ? item._id : 0).."}, 'oef_custom_time_card_form');"&lt;/script&gt;;
     
     &lt;script type="text/javascript"&gt;"
        ae_name_prefix['"..js_uid.."'] = '"..name_prefix.."[attributes]';
        ae_name_prefix['"..js_uid.."_tabulars_TimeRecords'] = '"..name_prefix.."[tabulars][TimeRecords]';
     "&lt;/script&gt;
  }}
</eval:else>
