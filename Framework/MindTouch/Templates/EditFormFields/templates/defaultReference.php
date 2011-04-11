{{
    var name    = args[0];
    var value   = args[1];
    var params  = args[2];
    var options = params.select ?? {};
    var attrs   = params.attrs ?? {};
    var reference  = params.reference ?? {};
    var precision  = params.precision ?? {};
    var dynamic = params.dynamic ?? false;
    var select  = '';
    var content = '&lt;option value="0"&gt;&amp;nbsp;&lt;/option&gt;';
    
    if (#attrs.class != 0) {
      let attrs ..= {class: attrs.class..' oef_'..reference.kind..'_'..reference.type};
    }
    else {
      let attrs ..= {class: 'oef_'..reference.kind..'_'..reference.type};
    }
    
    if (dynamic == True && #reference.kind != 0 && reference.type !=0)
    {
       let attrs ..= {class: attrs.class..' oef_dynamic_update'};
       let attrs ..= {kind: reference.kind, type: reference.type};
       let content ..= '&lt;option class="oef_edit_form_field_add_new" value="new"&gt;add new&lt;/option&gt;';
    }
    
    if (options is map)
    {
       let groups = map.Keys(options);
       let groups = list.Sort(groups);
       
       foreach (var group in groups)
       {
          let content ..= '&lt;optgroup label="'..group..'"&gt;';
          
          foreach (var param in options[group])
          {
             let content ..= '&lt;option value="'..param.value..'"';
             if (param.value == value) {
                let content ..= ' selected="selected" current="true"';
             }
             let content ..= '&gt;'..param.text..'&lt;/option&gt;';
          }
          
          let content ..= '&lt;/optgroup&gt;';
       }
    }
    else
    {
       foreach (var param in options)
       {
          let content ..= '&lt;option value="'..param.value..'"';
          if (param.value == value) {
             let content ..= ' selected="selected" current="true"';
          }
          let content ..= '&gt;'..param.text..'&lt;/option&gt;';
       }
    }
    
    &lt;select id=(attrs.id) name=(name) class=(attrs.class) rkind=(reference.kind) rtype=(reference.type)&gt;
      web.html(content);
    &lt;/&gt;;
}}
