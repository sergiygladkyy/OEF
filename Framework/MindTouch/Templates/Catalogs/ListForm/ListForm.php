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
   {{
      if (#puid.main_kind != 0) {
         var kind = puid.main_kind..'.'..puid.main_type..'.'..puid.kind;
      }
      else {
         var kind = puid.kind;
      }
      
      var hierarchy = entities.getInternalConfiguration(kind..'.hierarchy', type);
      var owners    = entities.getInternalConfiguration(kind..'.owners', type);
      var isTree    = false;
      
      if (#owners == 0 && hierarchy.type is num && prefix == 'default') {
         let prefix = 'tree';
         let isTree = true;
         let data   = entities.displayTreeList(uid, params);
      }
      else {
         let params ..= { page: __request.args.page ?? 1 };
         let data = entities.displayListForm(uid, params);
      }
      
      if (data.status != True) {
        let content = '&lt;ul class="ae_errors"&gt;';
        foreach (var error in data.errors) {
          let content = content..'&lt;li class="ae_error"&gt;'..error..'&lt;/li&gt;';
        }
        let content = content..'&lt;/ul&gt;';
        web.html(content);
      }
      else {
         var tpl_params = [uid, puid, data.result, params, root, prefix];
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
      
      var js_path = params.js_path;
      
      &lt;html&gt;
        &lt;head&gt;
          &lt;script type="text/javascript" src=(js_path..'/jquery.form.js')&gt;&lt;/script&gt;
          &lt;script type="text/javascript" src=(js_path..'/ae_list_form.js')&gt;&lt;/script&gt;
          if (isTree)
          {
            &lt;script type="text/javascript" src=(js_path..'/oe_tree.js')&gt;&lt;/script&gt;
          }
        &lt;/head&gt;
        &lt;body&gt;&lt;/body&gt;
        &lt;tail&gt;
          if (!isTree)
          {
            &lt;script type="text/javascript"&gt;"
              setInterval(\"updateListForm('"..page.api.."', null, 3000)\", 5000);
            "&lt;/script&gt;
          }
        &lt;/tail&gt;
      &lt;/html&gt;
   }}
  </eval:else>
