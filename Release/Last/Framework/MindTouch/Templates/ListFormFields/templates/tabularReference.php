{{
    var values  = args[0];
    var params  = args[1];
    var ref     = params.reference;
    var attrs   = params.attrs ?? {};
    var attributes = '';
    var value   = values.value ?? 0;
    var deleted = values.deleted ?? 0;
    var text    = values.text;
}}
<eval:if test="ref is nil">
  <ul class="ae_errors">
    <li class="ae_error">Invalid reference params</li>
  </ul>
</eval:if>
<eval:else>
  {{
      var href = page.path..'?uid='..ref.kind..'.'..ref.type..'&actions=displayItemForm&id='..value;
      
      foreach (var name in  map.Keys(attrs)) {
         if (string.Compare(name, "class", true) != 0) {
            let attributes = attributes..'" '..name..'="'..attrs[name];
         }
      }
      
      if (deleted != 0) {
         let attributes = attributes..'" class="'..attrs.class..' deleted';
      }
      else {
         if (!(attrs.class is nil)) {
            let attributes = attributes..'" class="'..attrs.class;
         }
      }
  
      if (#attributes != 0) {
         let href = href..attributes;
      }
      
      var content = '';
      
      if (value <= 0) {
         let text = 'not set';
         let href = href..'" style="display: none;';
      }
      
      let content = '&lt;span id="'..params.prefix..'_val" style="float:left; padding-right: 10px;"&gt;'..text..'&lt;/span&gt;&nbsp;';
      web.html(content..'&lt;a href="'..href..'"&gt;open&lt;/a&gt;');
  }}
</eval:else>
