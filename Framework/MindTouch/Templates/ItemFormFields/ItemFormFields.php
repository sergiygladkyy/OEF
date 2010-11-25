{{
   var ftype   = args[0]; 
   var value   = args[1] ?? {};
   var params  = args[2] ?? {};
   var etype   = args[3];
   var current = args[4];
   var prefix  = args[5] ?? 'default';
}}
  <eval:if test="ftype is nil">
    <ul class="ae_errors">
      <li class="ae_error">Unknow field type</li>
    </ul>
  </eval:if>
  <eval:elseif test="etype is nil">
    <ul class="ae_errors">
      <li class="ae_error">Unknow entity</li>
    </ul>
  </eval:elseif>
  <eval:elseif test="current is nil">
    <ul class="ae_errors">
      <li class="ae_error">Not set basepath to EditForm template</li>
    </ul>
  </eval:elseif>
  <eval:else>
    {{
       var tpl_params = [value, params];
       var template   = current..'/'..prefix..string.ToUpperFirst(etype)..string.ToUpperFirst(ftype);
       var content    = wiki.template(template, tpl_params);
       if (string.contains(content, 'href="'..template..'"'))
       {
          if (prefix != 'default')
          {
             let template = current..'/default'..string.ToUpperFirst(etype)..string.ToUpperFirst(ftype);
             let content  = wiki.template(template, tpl_params);
             
             if (string.contains(content, 'href="'..template..'"'))
             {
                let template = current..'/'..prefix..string.ToUpperFirst(ftype);
                let content  = wiki.template(template, tpl_params);
             }
          }
          
          if (string.contains(content, 'href="'..template..'"'))
          {
             let template = current..'/default'..string.ToUpperFirst(ftype);
             let content  = wiki.template(template, tpl_params);
          }
       }
       
       if (string.contains(content, 'href="'..template..'"'))
       {
          let content = 'Template not found';
       }
          
       content;
    }}
  </eval:else>
