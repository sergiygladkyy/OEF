{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var field_prec = {};
   var references = {}; 
   var required   = {};
   var dynamic    = {};
   var kind   = '';
   var type   = puid.type;
   var _list  = data.list;
   var links  = data.links;
   var select = data.select;
   var i    = 0;
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
      var name_prefix = args[5] ?? 'aeform['..kind..']';
      let name_prefix = name_prefix..'['..type..']';
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      let required   = entities.getInternalConfiguration(kind..'.required', type);
      let dynamic    = entities.getInternalConfiguration(kind..'.dynamic', type);
            
      var class = string.replace(kind, '.', '_')..'_'..type;
  }}
  <h3>{{ string.ToUpperFirst(type) }}</h3>
  <div class="{{ class..'_message systemmsg' }}" style="display: none;">
    <div class="inner">
      <ul class="flashMsg">
        <li>&nbsp;</li>
      </ul>
    </div>
  </div>
  <div class="infomsg">
    End date is NOT a part of period
  </div>
<div class="ae_tabular_section">
  <table class="{{ class }}">
  <thead>
    <tr>
      <th style="width: 10px;"><input type="checkbox" onclick="{{ 'javascript:checkAll(\''..class..'\', this);' }}" /></th>
      <eval:foreach var="field" in="fields">
        <eval:if test="field != 'Owner'">
          <th class="{{ class..'_'..field..'_header' }}">{{ string.ToUpperFirst(field); }}</th>
        </eval:if>
      </eval:foreach>
    </tr>
  </thead>
  <tbody id="{{ class..'_edit_block' }}">
    <eval:foreach var="item" in="_list">
      {{
         let i = i + 1;
         var params = {
           item: item,
           select: select,
           links: links,
           class: class,
           fields: fields,
           field_type: field_type,
           field_prec: field_prec,
           references: references,
           required: required,
           dynamic:  dynamic,
           name_prefix: name_prefix,
           numb: i
         };
         
         var template = root..'/Tabulars/TabularItem';
         var content  = wiki.template(template, [puid, kind, type, params, root, template, prefix]);
      
         if (string.contains(content, 'href="'..template..'"')) {
            let content = 'Template not found';
         }
          
         content;
      }}
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
  {{
     var flag = false;
     let params = {
       item: {},
       select: select,
       links: links,
       class: class,
       fields: fields,
       field_type: field_type,
       field_prec: field_prec,
       references: references,
       required: required,
       dynamic:  dynamic,
       name_prefix: name_prefix,
       numb: '%%i%%'
     };
         
     let template = root..'/Tabulars/TabularItem';
     let content  = wiki.template(template, [puid, kind, type, params, root, template, prefix]);
      
     if (!string.contains(content, 'href="'..template..'"')) {
        var flag = true;
     }
     
     var js_uid = string.replace(kind, '.', '_')..'_'..type;
  }}
  <eval:if test="flag == True">
    <a class="tabulars_actions" href="#" onclick="{{ 'if (jQuery(this).attr(\'disabled\') != \'true\') { addTabularSectionItem(\''..js_uid..'\', \''..class..'\'); } return false;' }}">Add</a>
    {{ &lt;script type="text/javascript"&gt;" ae_index['"..js_uid.."'] = "..i.."; ae_name_prefix['"..js_uid.."'] = '"..name_prefix.."'; ae_template['"..js_uid.."'] = '"..string.replace(string.escape(content), '/script', '/%%script%%').."';"&lt;/script&gt; }}
  </eval:if>
  <a class="tabulars_actions" href="#" onclick="{{ 'if (!jQuery(this).attr(\'disabled\') != \'true\') { deleteTabularSectionItems(\''..js_uid..'\', \''..class..'\'); } return false;' }}">Delete</a>
</eval:else>
