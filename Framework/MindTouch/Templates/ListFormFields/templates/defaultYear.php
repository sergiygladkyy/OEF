{{
    var value   = args[0];
    var params  = args[1];
    var attrs   = params.attrs ?? {};
    var options = params.params ?? {};
    var defval = options['default_value'] ?? 'not set';
    var attributes = '';

    foreach (var name in  map.Keys(attrs)) {
      let attributes = attributes..' '..name..'="'..attrs[name]..'"';
    }
    
    if (!string.startswith(value, '%%')) {
       let value = entities.GetFormattedDate(value, '%Y');
    }
    
    web.html('&lt;span'..attributes..'&gt;'..(#value > 0 ? value : defval)..'&lt;/span&gt;');
}}
