{{
    var value  = args[0];
    var params = args[1];
    var attrs  = params.attrs ?? {};
    var text   = value > 0 ? 'yes' : 'no';
    var attributes = '';

    foreach (var name in  map.Keys(attrs)) {
      let attributes ..= ' '..name..'="'..attrs[name]..'"';
    }
    
    web.html('&lt;span'..attributes..'&gt;'..text..'&lt;/span&gt;');
}}
