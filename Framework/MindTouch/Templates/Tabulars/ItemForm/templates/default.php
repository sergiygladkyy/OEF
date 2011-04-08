{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var references = {}; 
   var kind   = '';
   var type   = puid.type;
   var _list  = data.list;
   var links  = data.links;
   var pagination = data.pagination;
}}
<eval:if test="puid is nil">
  <ul class="ae_errors">
    <li class="ae_error">Unknow entities</li>
  </ul>
</eval:if>
<eval:else>
  {{
      if (#puid.main_kind != 0) {
         let kind = puid.main_kind..'.'..puid.main_type..'.'..puid.kind;
      }
      else {
         let kind = puid.kind;
      }

      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
            
      var class = string.replace(kind, '.', '_')..'_'..type;
  }}
  <h3>{{ string.ToUpperFirst(type) }}</h3>
<div class="ae_tabular_section">
  <table class="{{ class }}">
  <thead>
    <tr>
      <eval:foreach var="field" in="fields">
        <eval:if test="field != 'Owner'">
          <th>{{ string.ToUpperFirst(field); }}</th>
        </eval:if>
      </eval:foreach>
    </tr>
  </thead>
  <tbody id="{{ class..'_edit_block' }}">
    <eval:foreach var="item" in="_list">
      <tr class="tabular_item">
        <eval:foreach var="field" in="fields">
          <eval:if test="field != 'Owner'">
            <td class="tabular_col">
              <pre class="script">
                var params = {reference: references[field]};
                
                if (references[field] is nil)
                {
                   var value = item[field];
                }
                else
                {
                   var value = links[field][item[field]];
                }
                
                var template = root..'/ItemFormFields';
                var content  = wiki.template(template, [field_type[field], value, params, type, template, prefix]);
                
                if (string.contains(content, 'href="'..template..'"')) {
                   let content = 'Template not found';
                }
                
                content;
              </pre>
            </td>
          </eval:if>
        </eval:foreach>
      </tr>
    </eval:foreach>
  </tbody>
  </table>
</div>
<eval:if test="pagination is map">
  <div class="ae_list_pagination">
    {{
       let pagination ..= {name: type..'Page'};
       
       var template = root..'/Pagination';
       var content  = wiki.template(template, [uid, puid, root, template, pagination, prefix]);
       
       if (string.contains(content, 'href="'..template..'"')) {
          let content = 'Template not found';
       }
          
       content;
    }}
  </div>
</eval:if>
{{ &lt;script type="text/javascript"&gt;" jQuery('.ae_tabular_section tr:nth-child(even)').addClass('ae_even'); "&lt;/script&gt; }}
</eval:else>
{{awpskin.hideAll();}}