{{
    var name    = args[0];
    var value   = args[1];
    var params  = args[2];
    var options = params.select ?? [];
    var attrs   = params.attrs ?? {};
    var reference  = params.reference ?? {};
    var precision  = params.precision ?? {};
    var dynamic = params.dynamic ?? false;
    var select  = '';
    var content = '';
    
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
    
    var generate_success = true;
    
    if (options is map)
    {
       let groups = map.Keys(options);
       let groups = list.Sort(groups);
       
       foreach (var group in groups)
       {
          if (generate_success)
          {
             if (options[group]['text'] is nil)
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
             else
             {
                let generate_success = false;
             }
          }
       }
       
       if (!generate_success) let content = '';
    }
    
    if (options is list || !generate_success)
    {
       let generate_success = true;
       
       foreach (var param in options)
       {
          if (generate_success)
          {
             if (param.text is nil)
             {
                let generate_success = false;
             }
             else
             {
                let content ..= '&lt;option value="'..param.value..'"';
                if (param.value == value) {
                   let content ..= ' selected="selected" current="true"';
                }
                let content ..= '&gt;'..param.text..'&lt;/option&gt;';
             }
          }
       }
       
       if (!generate_success) let content = '';
    }
    
    let content = '&lt;option value="0"&gt;&amp;nbsp;&lt;/option&gt;'..content;
    
    &lt;select id=(attrs.id) name=(name) class=(attrs.class) rkind=(reference.kind) rtype=(reference.type)&gt;
      web.html(content);
    &lt;/&gt;;
}}
