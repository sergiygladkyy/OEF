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
<eval:elseif test="value &lt; 1">
  <span>not set</span>
</eval:elseif>
<eval:else>
  {{
      var href = page.path..'?uid='..ref.kind..'.'..ref.type..'&actions=displayItemForm&id='..value;
      
      foreach (var name in  map.Keys(attrs)) {
         if (string.Compare(name, "class", true) != 0) {
            let attributes = attributes..' '..name..'="'..attrs[name]..'"';
         }
      }
      
      if (deleted != 0) {
         let attributes = attributes..' class="'..attrs.class..' ae_deleted"';
      }
      else {
         if (!(attrs.class is nil)) {
            let attributes = attributes..' class="'..attrs.class..'"';
         }
      }
  
      web.html('&lt;span'..attributes..'&gt;'..text..'&lt;/span&gt;');
  }}
</eval:else>
