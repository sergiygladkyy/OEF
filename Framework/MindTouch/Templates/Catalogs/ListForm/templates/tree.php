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
   var kind   = '';
   var type   = puid.type;
   var list   = data.list;
   var links  = data.links;
   var class  = '';
   
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
          <eval:if test="field != 'Description' && field != 'Parent'">
            <th>{{ string.ToUpperFirst(field); }}</th>
          </eval:if>
        </eval:foreach>
      </tr>
    </thead>
    <tbody id="{{ class..'_list_block' }}" class="ae_list_block">
      <eval:foreach var="item" in="list">
        {{
           var params = {
             item:   item,
             links:  links,
             class:  class,
             fields: fields,
             field_type: field_type,
             field_prec: field_prec,
             hierarchy:  hierarchy,
             owners:     owners,
             references: references
           };
           
           var template = root..'/Catalogs/TreeItem';
           var content  = wiki.template(template, [kind, type, params, root, template, prefix]);
           
           if (string.contains(content, 'href="'..template..'"')) {
              let content = web.html('&lt;tr&gt;&lt;td colspan="'..(#fields)..'"&gt;Template not found&lt;/td&gt;&lt;/tr&gt;');
           }
           
           content;
        }}
      </eval:foreach>
    </tbody>
    </table>
  </div>
  <eval:if test="htype == 2">
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
  {{
     var flag  = false;
     let links = {};
     let item  = {_id: 1, _deleted: 0};
     
     foreach (var field in  fields) {
        let item ..= {(field): '%%'..field..'%%'};
        
        if (!(references[field] is nil)) {
           let links ..= {(field): {(item[field]): {text: item[field], value: 1, deleted: 0}}};
        }
     }
     
     if (htype == 2) {
        let item ..= {_folder: 1};
     }
     
     let params = {
       item:       item,
       links:      links,
       class:      '%prefix%',
       fields:     fields,
       field_type: field_type,
       field_prec: field_prec,
       hierarchy:  hierarchy,
       owners:     owners,
       references: references
     };
         
     let template = root..'/Catalogs/TreeItem';
     let content  = wiki.template(template, [kind, type, params, root, template, prefix]);
      
     if (!string.contains(content, 'href="'..template..'"')) {
        var flag = true;
     }
     
     var js_uid = string.replace(kind, '.', '_')..'_'..type;
  }}
  <eval:if test="flag == True">
    {{ &lt;script type="text/javascript"&gt;" oe_item_template['"..js_uid.."'] = '"..string.replace(string.escape(content), '/script', '/%%script%%').."';"&lt;/script&gt; }}
  </eval:if>
</eval:else>
