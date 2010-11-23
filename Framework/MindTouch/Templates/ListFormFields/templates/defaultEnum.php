{{
  var value  = args[0];
  var params = args[1];
  var attrs  = params.attrs ?? {};
  var attributes = '';

  foreach (var name in  map.Keys(attrs)) {
    let attributes = attributes..' '..name..'="'..attrs[name]..'"';
  }
  
  web.html('&lt;span'..attributes..'&gt;'..value..'&lt;/span&gt;');
}}
