{{
  var name   = args[0];
  var value  = args[1];
  var params = args[2];
  var attrs  = params.attrs ?? {};
  var attributes = '';
  
  foreach (var name in  map.Keys(attrs)) {
    let attributes ..= '" '..name..'="'..attrs[name];
  }
  
  if (#attributes != 0) {
    let name ..= attributes;
  }
  
  web.html('&lt;input type="checkbox" name="'..name..'" value="1"'..(#value > 0 && value != '0' ? ' checked' : '')..' /&gt;');
}}
