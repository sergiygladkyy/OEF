{{
   var kind    = args[0];
   var type    = args[1];
   var data    = args[2];
   var root    = args[3];
   var current = args[4];
   var prefix  = args[5] ?? 'default';
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
      var template   = current..'/'..prefix..string.ToUpperFirst(type);
      var content    = wiki.template(template, tpl_params);
      if (string.contains(content, 'href="'..template..'"'))
      {
         if (prefix != 'default')
         {
            let template = current..'/default'..string.ToUpperFirst(type);
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
