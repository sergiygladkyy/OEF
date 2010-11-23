{{
    var name    = args[0];
    var value   = args[1];
    var params  = args[2];
    var options = params.precision ?? {};
    let options = options.in ?? {};
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
    
    var opt_keys = map.keys(options);
    let opt_keys = list.Sort(opt_keys);
    
    foreach (var key in opt_keys) {
       let content = content..'&lt;option value="'..key..'"';
       if (options[key] == value) {
          let content = content..' selected="selected"';
       }
       let content = content..'&gt;'..options[key]..'&lt;/option&gt;';
    }
    
    web.html(content);
}}
