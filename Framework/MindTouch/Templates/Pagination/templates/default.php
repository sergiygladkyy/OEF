{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var name   = data.name ?? 'page';
}}
<eval:if test="puid is nil">
  <ul class="ae_errors">
    <li class="ae_error">Unknow entities</li>
  </ul>
</eval:if>
<eval:elseif test="data.current is nil">
  <ul class="ae_errors">
    <li class="ae_error">Invalid page number</li>
  </ul>
</eval:elseif>
<eval:elseif test="data.FOR_MT is nil">
  <ul class="ae_errors">
    <li class="ae_error">Invalid pagination params</li>
  </ul>
</eval:elseif>
<eval:else>
{{
   var scroll_line = '';
   
   if (#__request.args > 0)
   {
      var hs = '&';
      var query_params = {};
      foreach (var key in list.sort(map.keys(__request.args))) {
         if (key != name) {
            let query_params ..= {(key): __request.args[key]};
         }
      }
      var href = uri.appendquery(page.uri, query_params);
   }
   else {
      var hs = '?';
      var href = page.uri;
   }
   
   var _href = href..hs..name;
   
   foreach (var page in data.FOR_MT)
   {
      if (page == data.current) {
         let scroll_line = scroll_line..'&lt;span class="ae_pagination"&gt;'..page..'&lt;/span&gt;&nbsp;';
      }
      else {
         let scroll_line = scroll_line..'&lt;a href="'.._href..'='..page..'" class="ae_pagination"&gt;'..page..'&lt;/a&gt;&nbsp;';
      }
   }
   
   if (data.shooter is num)
   {
      var left_sh  = '&lt;a href="'.._href..'='..(data.current - 1)..'" class="ae_pagination ae_shooter"&gt;\'&lt;\'&lt;/a&gt;&nbsp;';
      var right_sh = '&lt;a href="'.._href..'='..(data.current + 1)..'" class="ae_pagination ae_shooter"&gt;>&lt;/a&gt;&nbsp;';

      if (data.shooter == 0)
      {
         let scroll_line = left_sh..scroll_line..right_sh;
      }
      else if (data.shooter == 1)
      {
         let scroll_line = left_sh..scroll_line;
      }
      else if (data.shooter == 2)
      {
         let scroll_line = scroll_line..right_sh;
      }
   }
   
   web.html(scroll_line);
}}
</eval:else>