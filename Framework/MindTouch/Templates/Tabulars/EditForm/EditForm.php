{{
   var uid     = args[0];
   var puid    = args[1];
   var data    = args[2];
   var root    = args[3];
   var current = args[4];
   var params  = args[5];
   var prefix  = args[6] ?? 'default';
   var type    = puid.type;
   var name    = (#puid.main_kind != 0) ? string.ToUpperFirst(puid.main_kind)..string.ToUpperFirst(puid.main_type) : '';
   
   let name ..= string.ToUpperFirst(type);
}}
  <eval:if test="current is nil">
    <ul class="ae_errors">
      <li class="ae_error">Not set basepath to EditForm template</li>
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
      if (!(data is nil) && (data.status != True)) {
         let content = '&lt;ul class="ae_errors"&gt;';
         foreach (var error in data.errors) {
            let content = content..'&lt;li class="ae_error"&gt;'..error..'&lt;/li&gt;';
         }
         let content = content..'&lt;/ul&gt;';
         web.html(content);
      }
      else {
         var tpl_params = [uid, puid, data.result, root, prefix, params.name_prefix];
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
      }
    </pre>
  </eval:else>
