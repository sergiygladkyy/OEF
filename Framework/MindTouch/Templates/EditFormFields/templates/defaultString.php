{{
  var name    = args[0];
  var value   = args[1];
  var params  = args[2];
  var attrs   = params.attrs ?? {};
  var view    = params.view  ?? {};
  var attributes = '';
  
  if (!(params.precision.max_length is nil)) {
     let attrs ..= {maxlength: params.precision.max_length};
  }
  
  foreach (var name in  map.Keys(attrs)) {
    let attributes = attributes..'" '..name..'="'..attrs[name];
  }
  
  if (#attributes != 0) {
    let name = name..attributes;
  }
  
  if (view.multiline) {
     web.html('&lt;textarea name="'..name..'"&gt;'..value..'&lt;/textarea&gt;');
  }
  else {
     web.html('&lt;input type="text" name="'..name..'" value="'..value..'" /&gt;');
  }
}}
