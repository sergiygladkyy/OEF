{{
   var data    = entities.getAppliedSolutionName();
   var content = '';
   
   if (data.status != True)
   {
      let content = '&lt;ul class="ae_errors"&gt;';
      
      foreach (var error in data.errors)
      {
        let content = content..'&lt;li class="ae_error"&gt;'..error..'&lt;/li&gt;';
      }
      
      let content = content..'&lt;/ul&gt;';
      
      web.html(content);
   }
   else
   {
      var root = 'Template:OEF/'..data.result;
      
      var params = [root, args[0], args[1], args[2], args[3], args[4]];
      
      var template = root..'/MainTemplates/default';
      
      let content = wiki.template(template, params);
      
      if (string.contains(content, 'href="'..template..'"'))
      {
         let content = 'Template not found';
      }
      
      content;
   }
}}