{{
   var kind    = args[0];
   var type    = args[1];
   var data    = args[2];
   var root    = args[3] ?? {};
   var current = args[4] ?? 'Template:Entities';
   var prefix  = args[5] ?? 'default';
   
   var item       = data.item;
   var links      = data.links;
   var class      = data.class;
   var fields     = data.fields;
   var field_type = data.field_type;
   var field_prec = data.field_prec;
   var field_use  = data.field_use[item._folder ? 2 : 1];
   var hierarchy  = data.hierarchy;
   var owners     = data.owners;
   var references = data.references;
   var params     = data.params ?? {};
   var htype      = hierarchy.type is num ? hierarchy.type : 0;
}}

<tr class="{{ class..'_list_item ae_list_item'..(item._deleted != 0  ? ' ae_deleted_col' : '') }}">
  <eval:if test="item._id &gt; 0">
    <td style="display: none;">
      <span class="{{ class..'_item_id ae_item_id' }}" style="display: none;">{{ item._id }}</span>
    </td>
    <td onclick="{{ 'javascript:selectColumn(this, \''..class..'\');' }}">
      <div class="oef_tree_control">
        <eval:if test="htype == 2">
          <eval:if test="item._folder == 1">
            <div class="oef_tree_active oef_tree_closed">&nbsp;</div>
            <div class="oef_tree_folder oef_tree_closed">&nbsp;</div>
          </eval:if>
          <eval:else>
            <div class="oef_tree_not_active">&nbsp;</div>
            <div class="oef_tree_item">&nbsp;</div>
          </eval:else>
        </eval:if>
        <eval:elseif test="htype == 1">
          <div class="oef_tree_active oef_tree_closed">&nbsp;</div>
          <div class="oef_tree_item oef_tree_closed">&nbsp;</div>
        </eval:elseif>
        <div class="oef_tree_desc"><nobr>{{ item['Description'] }}</nobr></div>
      </div>
    </td>
    <eval:foreach var="field" in="fields">
      <eval:if test="field != 'Description' && field != 'Parent'">
        <td onclick="{{ 'javascript:selectColumn(this, \''..class..'\');' }}">
          <pre class="script">
            var value = '';
            if (!(references[field] is nil)) {
               let value = links[field][item[field]];
            }
            else {
               let value = item[field];
            }
            
            var not_used = (htype == 2 && #list.select(field_use, "$=='"..field.."'") < 1);
            
            let params ..= {not_used: not_used};
            
            var tpl_params = {reference: references[field], precision: field_prec[field], params: params};
            
            var template = root..'/ListFormFields';
            var content  = wiki.template(template, [field_type[field], value, tpl_params, type, template, prefix]);
          
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
