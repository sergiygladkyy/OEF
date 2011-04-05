{{
   var puid    = args[0];
   var kind    = args[1];
   var type    = args[2];
   var data    = args[3];
   var root    = args[4];
   var current = args[5];
   var prefix  = args[6] ?? 'default';
   var name    = (#puid.main_kind != 0) ? string.ToUpperFirst(puid.main_kind)..string.ToUpperFirst(puid.main_type) : '';
   
   let name ..= string.ToUpperFirst(type);
}}
  <eval:if test="current is nil">
    <ul class="ae_errors">
      <li class="ae_error">Not set basepath to TabularFields template</li>
    </ul>
  </eval:if>
  <eval:elseif test="root is nil">
    <ul class="ae_errors">
      <li class="ae_error">Not set root template</li>
    </ul>
  </eval:elseif>
  <eval:elseif test="kind is nil">
    <ul class="ae_errors">
      <li class="ae_error">Unknow entity</li>
    </ul>
  </eval:elseif>
  <eval:elseif test="type is nil">
    <ul class="ae_errors">
      <li class="ae_error">Unknow entity</li>
    </ul>
  </eval:elseif>
  <eval:else>
    <pre class="script">
      var tpl_params = [kind, type, data, root, prefix];
      var template   = current..'/'..prefix..name;
      var content    = wiki.template(template, tpl_params);
      if (string.contains(content, 'href="'..template..'"'))
      {
         if (prefix != 'default')
         {
            let template = current..'/default'..name;
            let content  = wiki.template(template, tpl_params);
         
            if (string.contains(content, 'href="'..template..'"'))
            {
               let template = current..'/'..prefix;
               let content  = wiki.template(template, tpl_params);
            }
         }
         
         if (string.contains(content, 'href="'..template..'"'))
         {
            let template = current..'/default';
            let content  = wiki.template(template, tpl_params);
         }
      }
      
      if (string.contains(content, 'href="'..template..'"'))
      {
         let content = 'Template not found';
      }
         
      content;
    </pre>
  </eval:else>
