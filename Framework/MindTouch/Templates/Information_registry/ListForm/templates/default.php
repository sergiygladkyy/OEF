{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var params = args[3] ?? {};
   var root   = args[4] ?? 'Template:Entities';
   var prefix = args[5] ?? 'default';
   var fields = {};
   var field_type = {};
   var field_prec = {};
   var field_view = {};
   var references = {}; 
   var recorders  = {};
   var forms_view = {};
   var kind   = '';
   var type   = puid.type;
   var list   = data.list;
   var links  = data.links;
   var class  = '';
   var pagination = data.pagination;

   var tmpList = string.Split(uid,'.');
   var header = string.Remove(string.ToUpperFirst(tmpList[0]),string.Length(tmpList[0])-1,1)..' '..tmpList[1];
}}
<h3>{{header;}}</h3>
<eval:if test="puid is nil">
  <ul class="ae_errors">
    <li class="ae_error">Unknow entities</li>
  </ul>
</eval:if>
<eval:elseif test="list is nil">
  <ul class="ae_errors">
    <li class="ae_error">Invalid list params</li>
  </ul>
</eval:elseif>
<eval:else>
  {{
      if (#puid.main_kind != 0) {
         let kind = puid.main_kind..'.'..puid.main_type..'.'..puid.kind;
      }
      else {
         let kind = puid.kind;
      }
      
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let field_view = entities.getInternalConfiguration(kind..'.field_view', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      let recorders  = entities.getInternalConfiguration(kind..'.recorders', type);
      let forms_view = entities.getInternalConfiguration(kind..'.forms_view', type);
      let forms_view = forms_view.ListForm ?? {};
      
      var columns = forms_view.columns ?? fields;
      var hasRec  = #recorders > 0;
      let class   = string.replace(kind, '.', '_')..'_'..type;
  }}
  <div class="{{ class..'_message systemmsg' }}" style="display: none;">
    <div class="inner">
      <ul class="flashMsg">
        <li>&nbsp;</li>
      </ul>
    </div>
  </div>
  <div class="ae_listform">
  <table>
  <thead>
    <tr>
      <th style="display: none;">ID</th>
      <eval:if test="hasRec == true">
        <th width="200">Recorder</th>
      </eval:if>
      <eval:foreach var="field" in="columns">
        <th>{{ field_view[field]['synonim'] ?? string.ToUpperFirst(field) }}</th>
      </eval:foreach>
    </tr>
  </thead>
  <tbody id="{{ class..'_list_block' }}" class="ae_list_block">
    <eval:foreach var="item" in="list">
      <tr class="{{ class..'_list_item ae_list_item' }}">
        <eval:if test="item._id &gt; 0">
          <td style="display: none;">
            <span class="{{ class..'_item_id ae_item_id' }}" style="display: none;">{{ item._id }}</span>
          </td>
          <eval:if test="hasRec == true">
            <td onclick="{{ 'javascript:selectColumn(this, \''..class..'\');' }}">
              <span><nobr>{{ links['_rec_id'][item._rec_type][item._rec_id]['text'] }}</nobr></span>
            </td>
          </eval:if>
          <eval:foreach var="field" in="columns">
            <td onclick="{{ 'javascript:selectColumn(this, \''..class..'\');' }}">
              <pre class="script">
                var value  = '';
                if (!(references[field] is nil)) {
                   if (links[field][item[field]])
                   {
                      let value = links[field][item[field]];
                   }
                   else let value = {value: item[field]};
                }
                else {
                   let value = item[field];
                }
              
                var tpl_params = {
                   reference: references[field],
                   precision: field_prec[field],
                   view:      field_view[field]
                };
              
                var template = root..'/ListFormFields';
                var content  = wiki.template(template, [field_type[field], value, tpl_params, type, template, prefix]);
              
                if (string.contains(content, 'href="'..template..'"')) {
                  let content = 'Template not found';
                }
              
                content;
              </pre>
            </td>
          </eval:foreach>
        </eval:if>
        <eval:else>
          <td colspan="{{ #columns + 1 + (hasRec == true ? 1 : 0) }}">Wrong data</td>
        </eval:else>
      </tr>
    </eval:foreach>
  </tbody>
  </table>
  </div>
  <eval:if test="pagination is map">
    <div class="ae_list_pagination">
    {{
       var template = root..'/Pagination';
       var content  = wiki.template(template, [uid, puid, root, template, pagination, prefix]);
       
       if (string.contains(content, 'href="'..template..'"')) {
          let content = 'Template not found';
       }
          
       content;
    }}
    </div>
  </eval:if>
  <eval:if test="hasRec != true">
    <a href="#" target="_blank" onclick="{{ 'javascript: newListItem(this, \''..kind..'\', \''..type..'\');  return false;' }}">New</a>&nbsp;|
  </eval:if>
  <a href="#" target="_blank" onclick="{{ 'javascript: editListItem(this, \''..kind..'\', \''..type..'\'); return false;' }}">Edit</a>&nbsp;|
  <a href="#" target="_blank" onclick="{{ 'javascript: viewListItem(this, \''..kind..'\', \''..type..'\'); return false;' }}">View</a>&nbsp;|
  <a href="#" onclick="{{ 'javascript:deleteListItem(\''..kind..'\', \''..type..'\', \''..class..'\'); return false;' }}">Delete</a>
</eval:else>
