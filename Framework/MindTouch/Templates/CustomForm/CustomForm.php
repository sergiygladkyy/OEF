{{
   var uid     = args[0];
   var puid    = args[1];
   var root    = args[2];
   var current = args[3];
   var params  = args[4] ?? {};
   var prefix  = args[5] ?? 'default';
   var type    = puid.type;
   var data    = {};
   var content = '';
}}
  <eval:if test="params['form'] is nil">
    <ul class="ae_errors">
      <li class="ae_error">Not set form name</li>
    </ul>
  </eval:if>
  <eval:elseif test="root is nil">
    <ul class="ae_errors">
      <li class="ae_error">Not set root template</li>
    </ul>
  </eval:elseif>
  <eval:elseif test="type is nil">
    <ul class="ae_errors">
      <li class="ae_error">Unknow entity</li>
    </ul>
  </eval:elseif>
  <eval:elseif test="uid is nil">
    <ul class="ae_errors">
      <li class="ae_error">Unknow entity</li>
    </ul>
  </eval:elseif>
  <eval:else>
    <pre class="script">
      &lt;div id="oef_custom_form"&gt;&nbsp;&lt;/div&gt;;
      &lt;script type="text/javascript"&gt;"displayCustomForm('"..uid.."', '"..params['form'].."', "..json.emit(params)..", 'oef_custom_form');"&lt;/script&gt;;
    </pre>
  </eval:else>
