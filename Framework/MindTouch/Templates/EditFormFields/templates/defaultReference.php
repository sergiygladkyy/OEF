{{
    var name    = args[0];
    var value   = args[1];
    var params  = args[2];
    let options = params.select ?? {};
    var attrs   = params.attrs ?? {};
    var attributes = '';
    var content = '';
    
    foreach (var name in  map.Keys(attrs)) {
       let attributes = attributes..'" '..name..'="'..attrs[name];
    }
  
    if (#attributes != 0) {
       let name = name..attributes;
    }

    let content = '&lt;select name="'..name..'"&gt;';
    if (params.required != True) {
       let content = content..'&lt;option value="0"&gt;--&lt;/option&gt;';
    }
    foreach (var param in options) {
       let content = content..'&lt;option value="'..param.value..'"';
       if (param.value == value) {
          let content = content..' selected="selected"';
       }
       let content = content..'&gt;'..param.text..'&lt;/option&gt;';
    }
    
    web.html(content);
}}
