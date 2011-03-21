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
   var hierarchy  = {};
   var owners     = {};
   var references = {};
   var basis_for  = {}; 
   var kind   = '';
   var type   = puid.type;
   var list   = data.list;
   var links  = data.links;
   var class  = '';
   var defval = '&nbsp;';
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
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      let hierarchy  = entities.getInternalConfiguration(kind..'.hierarchy', type);
      let owners     = entities.getInternalConfiguration(kind..'.owners', type);
      let basis_for  = entities.getInternalConfiguration(kind..'.basis_for', type);
      
      var htype  = hierarchy.type is num ? hierarchy.type : 0;
      let class  = string.replace(kind, '.', '_')..'_'..type;
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
      <th>Description</th>
      <eval:foreach var="field" in="fields">
        <eval:if test="field != 'Description' && (htype == 0 || field != 'Parent') && (#owners == 0 || field != 'OwnerId')">
          <eval:if test="field == 'OwnerType'">
            <th>Owner</th>
          </eval:if>
          <eval:else>
            <th>{{ string.ToUpperFirst(field); }}</th>
          </eval:else>
        </eval:if>
      </eval:foreach>
    </tr>
  </thead>
  <tbody id="{{ class..'_list_block' }}" class="ae_list_block">
    <eval:foreach var="item" in="list">
      <tr class="{{ class..'_list_item ae_list_item'..(item._deleted != 0  ? ' ae_deleted_col' : '') }}">
        <eval:if test="item._id &gt; 0">
          <td style="display: none;">
            <span class="{{ class..'_item_id ae_item_id' }}" style="display: none;">{{ item._id }}</span>
          </td>
          <td onclick="{{ 'javascript:selectColumn(this, \''..class..'\');' }}">
            <div class="oef_tree_control">
              <eval:if test="htype == 2 && item._folder == 1">
                <div class="oef_tree_folder oef_tree_closed">&nbsp;</div>
              </eval:if>
              <eval:else>
                <div class="oef_tree_item">&nbsp;</div>
              </eval:else>
              <div class="oef_desc"><nobr>{{ item['Description'] }}</nobr></div>
            </div>
          </td>
          <eval:foreach var="field" in="fields">
            <eval:if test="(field != 'Description' && (htype == 0 || field != 'Parent') && (#owners == 0 || field != 'OwnerId'))">
              <td onclick="{{ 'javascript:selectColumn(this, \''..class..'\');' }}">
                <pre class="script">
                  var value  = '';
                  
                  if (field == 'OwnerType' && #owners > 0) {
                     let value = links[field][item[field]][item['OwnerId']];
                     
                     var c_field_type = 'reference';
                     var tpl_params   = {
                        reference: {kind: kind, type: item[field]},
                        precision: {}
                     };
                  }
                  else {
                     if (!(references[field] is nil)) {
                        let value = links[field][item[field]];
                     }
                     else {
                        let value = item[field];
                     }
                     
                     var c_field_type = field_type[field];
                     var tpl_params   = {
                        reference: references[field],
                        precision: field_prec[field],
                        params: {default_value: defval}
                     };
                  }
                  
                  var template = root..'/ListFormFields';
                  var content  = wiki.template(template, [c_field_type, value, tpl_params, type, template, prefix]);
                  
                  if (string.contains(content, 'href="'..template..'"')) {
                     let content = 'Template not found';
                  }
                  
                  content;
                </pre>
              </td>
            </eval:if>
          </eval:foreach>
        </eval:if>
        <eval:else>
          <td colspan="{{ #fields }}">Wrong data</td>
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
  <div class="oe_list_action_menu oef_menu">
    <eval:if test="hierarchy.type == 2">
      <a href="#" target="_blank" onclick="{{ 'javascript:newListItem(this, \'/'..page.path..'?uid='..kind..'.'..type..'&actions=displayEditForm'..'\', \''..class..'\');' }}" >New Item</a>&nbsp;|
      <a href="#" target="_blank" onclick="{{ 'javascript:newListItem(this, \'/'..page.path..'?uid='..kind..'.'..type..'&actions=displayEditForm&type=group'..'\', \''..class..'\');' }}" >New Group</a>&nbsp;|
    </eval:if>
    <eval:else>
      <a href="#" target="_blank" onclick="{{ 'javascript:newListItem(this, \'/'..page.path..'?uid='..kind..'.'..type..'&actions=displayEditForm'..'\', \''..class..'\');' }}" >New</a>&nbsp;|
    </eval:else>
    <a href="#" target="_blank" onclick="{{ 'javascript:if (!editListItem(this, \'/'..page.path..'?uid='..kind..'.'..type..'&actions=displayEditForm'..'\', \''..class..'\')) return false;' }}">Edit</a>&nbsp;|
    <a href="#" target="_blank" onclick="{{ 'javascript:if (!viewListItem(this, \'/'..page.path..'?uid='..kind..'.'..type..'&actions=displayItemForm'..'\', \''..class..'\')) return false;' }}">View</a>&nbsp;|
    <a href="#" onclick="{{ 'javascript:markForDeletionListItem(\''..kind..'\', \''..type..'\', \''..class..'\', '..(params.show_marked_for_deletion ? 'true' : 'false')..'); return false;' }}">Mark for deletion</a>
    <eval:if test="params.show_marked_for_deletion">
      |&nbsp;<a href="#" onclick="{{ 'javascript:unmarkForDeletionListItem(\''..kind..'\', \''..type..'\', \''..class..'\'); return false;' }}">Unmark for deletion</a>
    </eval:if>
    <eval:if test="#basis_for &gt; 0">
      |&nbsp;<a href="#" target="_blank" onclick="{{ 'javascript:if (!newOnBasis(this, \''..kind..'\', \''..type..'\', '..Json.Emit(basis_for)..')) return false;' }}">New On Basis</a>
    </eval:if>
  </div>
</eval:else>
