{{
  var name    = args[0];
  var value   = args[1];
  var params  = args[2];
  var attrs   = params.attrs ?? {};
  var options = params.options ?? {};
  var attributes = '';
  
  let attrs ..= {maxlength: 4};
  
  foreach (var name in  map.Keys(attrs)) {
    let attributes = attributes..'" '..name..'="'..attrs[name];
  }
  
  if (#attributes != 0) {
    let name = name..attributes;
  }
  
  web.html('&lt;input type="text" name="'..name..'" value="'..value..'" /&gt;');
}}
