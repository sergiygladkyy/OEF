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
   var references = {};
   var reg_type   = '';
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
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      let reg_type   = entities.getInternalConfiguration(kind..'.register_type', type);
      
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
      <th>Period</th>
      <th>Recorder</th>
      <th>Line</th>
      <eval:foreach var="field" in="fields">
        <eval:if test="field != 'Period'">
          <th>{{ string.ToUpperFirst(field); }}</th>
        </eval:if>
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
          <td onclick="{{ 'javascript:selectColumn(this, \''..class..'\');' }}">
            <div class="oef_accum_reg_period">
              <eval:if test="reg_type == 'Balances'">
                <eval:if test="item._operation == 1">
                  <div class="oef_accum_reg_plus">&nbsp;</div>
                </eval:if>
                <eval:else>
                  <div class="oef_accum_reg_minus">&nbsp;</div>
                </eval:else>
              </eval:if>
              <eval:else>
                <div class="oef_accum_reg_dot">&nbsp;</div>
              </eval:else>
              <div class="oef_accum_reg_period_value"><nobr>{{ item.Period }}</nobr></div>
            </div>
          </td>
          <td onclick="{{ 'javascript:selectColumn(this, \''..class..'\');' }}">
            <span><nobr>{{ links['_rec_id'][item._rec_type][item._rec_id]['text'] }}</nobr></span>
          </td>
          <td onclick="{{ 'javascript:selectColumn(this, \''..class..'\');' }}">
            <span>{{ item._line }}</span>
          </td>
          <eval:foreach var="field" in="fields">
            <eval:if test="field != 'Period'">
              <td onclick="{{ 'javascript:selectColumn(this, \''..class..'\');' }}">
                <nobr>
                <pre class="script">
                  var value  = '';
                  if (!(references[field] is nil)) {
                     let value = links[field][item[field]];
                  }
                  else {
                     let value = item[field];
                  }
                  
                  var tpl_params = {reference: references[field], precision: field_prec[field]};
                  
                  var template = root..'/ListFormFields';
                  var content  = wiki.template(template, [field_type[field], value, tpl_params, type, template, prefix]);
                  
                  if (string.contains(content, 'href="'..template..'"')) {
                     let content = 'Template not found';
                  }
                  
                  content;
                </pre>
                </nobr>
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
  <!--
  <a href="#" target="_blank" onclick="{{ 'javascript: newListItem(this, \''..kind..'\', \''..type..'\');  return false;' }}">New</a>&nbsp;|
  <a href="#" target="_blank" onclick="{{ 'javascript: editListItem(this, \''..kind..'\', \''..type..'\'); return false;' }}">Edit</a>&nbsp;|
  <a href="#" target="_blank" onclick="{{ 'javascript: viewListItem(this, \''..kind..'\', \''..type..'\'); return false;' }}">View</a>&nbsp;|
  <a href="#" onclick="{{ 'javascript:deleteListItem(\''..kind..'\', \''..type..'\', \''..class..'\'); return false;' }}">Delete</a>
  -->
</eval:else>
