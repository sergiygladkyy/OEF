{{
   var uid     = args[0];
   var puid    = args[1];
   var root    = args[2];
   var current = args[3];
   var params  = args[4];
   var prefix  = args[5] ?? 'default';
   var type    = puid.type;
   var id      = __request.args.id ?? params.id ?? 0;
   var data    = {};
   var content = '';
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
      var options = {};
      var kind = '';
      if (#puid.main_kind != 0) {
         let kind = puid.main_kind..'.'..puid.main_type..'.'..puid.kind;
      }
      else {
         let kind = puid.kind;
      }
      
      var tabulars = entities.getInternalConfiguration(kind..'.tabulars.tabulars', type);
      
      foreach (var tabular in tabulars) {
         var ppName = tabular..'Page';
         let options ..= { (tabular): { 
               page:   __request.args[ppName] ?? 1,
               config: { max_per_page: 10 }  
         }};
      }
      
      let data = entities.displayEditForm(uid, id, {options: options});
      
      if (id > 0 && data.status != True) {
        let content = '&lt;ul class="ae_errors"&gt;';
        foreach (var error in data.errors) {
          let content = content..'&lt;li class="ae_error"&gt;'..error..'&lt;/li&gt;';
        }
        let content = content..'&lt;/ul&gt;';
        web.html(content);
      }
      else {
         var tpl_params = [uid, puid, data.result, root, prefix];
         var template   = current..'/'..prefix..string.ToUpperFirst(type);
         let content    = wiki.template(template, tpl_params);
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
      }
    </pre>
  </eval:else>
