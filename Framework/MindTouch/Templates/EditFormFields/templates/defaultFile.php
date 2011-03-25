{{
  var name   = args[0];
  var value  = args[1];
  var params = args[2];
  var attrs  = params.attrs ?? {};
  var precision  = params.precision ?? {};
  var attributes = '';
  
  if (!attrs.size) let attrs ..= {size: '24'};
  
  if (!(params.precision.max_length is nil)) {
     let attrs ..= {maxlength: params.precision.max_length};
  }
  
  foreach (var name in  map.Keys(attrs)) {
    let attributes = attributes..'" '..name..'="'..attrs[name];
  }
  
  if (#attributes != 0) {
    let name = name..attributes;
  }
  
  if (precision.max_file_size is num)
  {
     &lt;input type="hidden" name="MAX_FILE_SIZE" value=(precision.max_file_size) /&gt;
  }
  
  web.html('&lt;input type="file" name="'..name..'" /&gt;');
}}
