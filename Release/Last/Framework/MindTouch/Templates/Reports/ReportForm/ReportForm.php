{{
   var uid     = args[0];
   var puid    = args[1];
   var root    = args[2];
   var current = args[3];
   var params  = args[4];
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
    <pre class="script">
      /* Retrieve headline */
      
      var headline = {};
      var fields = entities.getInternalConfiguration((#puid.main_kind != 0 ? puid.main_kind..'.'..puid.main_type..'.'..puid.kind : puid.kind)..'.fields', type);
      foreach (var field in fields) {
         let headline ..= {(field):__request.args['headline_'..field] ?? ''};
      }
      
      /* Get page data */
      
      var options = {headline: headline};
      if (__request.args.generate == true) {
         let options ..= {options: {with_report: true}};
      }
      let data = entities.displayReportForm(uid, options);
      
      /* Show page */
      
      if (data.status != True) {
        let content = '&lt;ul class="ae_errors"&gt;';
        foreach (var error in data.errors) {
          let content = content..'&lt;li class="ae_error"&gt;'..error..'&lt;/li&gt;';
        }
        let content = content..'&lt;/ul&gt;';
        web.html(content);
      }
      else {
         var tpl_params = [uid, puid, headline, data.result, root, prefix];
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
