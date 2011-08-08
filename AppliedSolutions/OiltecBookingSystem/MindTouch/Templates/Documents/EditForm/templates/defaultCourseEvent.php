{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var class  = puid.kind..'_'..puid.type;
   var item   = data.item is map ? data.item : {};
   var kind   = puid.kind;
   var type   = puid.type;

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
  <div class="{{ puid.kind..'_'..puid.type..'_message systemmsg' }}" style="display: none;">
    <div class="inner">
      <ul class="flashMsg">
        <li>&nbsp;</li>
      </ul>
    </div>
  </div>
  <div class="{{ class..'_actions ae_editform_actions' }}" style="{{ item._id &gt; 0 ? 'display: block;' : 'display: none;' }}">
    &nbsp;
  </div>
  {{
     var item = data.item is map ? data.item : {};
     var name_prefix = 'aeform['..puid.kind..']['..puid.type..']';
     var js_uid = puid.kind..'_'..puid.type;
     
     &lt;div id="oef_custom_ResourcesReservation_form"&gt;&nbsp;&lt;/div&gt;;
     &lt;script type="text/javascript"&gt;"displayCustomForm('"..uid.."', 'ResourcesReservation', {document: "..(item._id > 0 ? item._id : 0).."}, 'oef_custom_ResourcesReservation_form');"&lt;/script&gt;;
     
     &lt;script type="text/javascript"&gt;"
        ae_name_prefix['"..js_uid.."'] = '"..name_prefix.."[attributes]';
        ae_name_prefix['"..js_uid.."_tabulars_Schedule'] = '"..name_prefix.."[attributes][tabulars][Schedule]';
     "&lt;/script&gt;
  }}
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
