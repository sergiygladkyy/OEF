{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var field_prec = {};
   var field_use  = {};
   var required   = [];
   var dynamic    = {};
   var hierarchy  = {};
   var owners     = {};
   var references = [];
   var kind     = '';
   var type     = puid.type;
   var item     = data.item is map ? data.item : {};
   var select   = data.select;
   var tabulars = data.tabulars;
   
   var tmpList = string.Split(uid,'.');
   var header = string.Remove(string.ToUpperFirst(tmpList[0]),string.Length(tmpList[0])-1,1)..' '..tmpList[1];
}}
<h3>{{header;}}</h3>
<eval:if test="puid is nil">
  <ul class="ae_errors">
    <li class="ae_error">Unknow entity</li>
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
      var enctype = '';
      var name_prefix = 'aeform['..kind..']['..type..']';
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let field_use  = entities.getInternalConfiguration(kind..'.field_use', type);
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let required   = entities.getInternalConfiguration(kind..'.required', type);
      let dynamic    = entities.getInternalConfiguration(kind..'.dynamic', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      let hierarchy  = entities.getInternalConfiguration(kind..'.hierarchy', type);
      let owners     = entities.getInternalConfiguration(kind..'.owners', type);
      
      var htype  = hierarchy.type is num ? hierarchy.type : 0;
      
      if (item._id > 0) {
         var hidden = '&lt;input type="hidden" name="'..name_prefix..'[attributes][_id]" value="'..item._id..'" /&gt;';
      }
      else {
         var hidden = '';
         
         if (htype == 2) {
            let item ..= {_folder: (__request.args.type == 'group' ? 1 : 0)};
         }
      }
      
      var use_tabulars = true;
      
      if (htype == 2) {
         if (item._folder == 1) {
            let fields = field_use[2];
            let use_tabulars = false;
         }
         else {
            let fields = field_use[1];
         }
         
         let hidden ..= '&lt;input type="hidden" name="'..name_prefix..'[attributes][_folder]" value="'..item._folder..'" /&gt;';
      }
      
      if (#map.select(field_type, "$.value=='file'") > 0)
      {
         let enctype = 'multipart/form-data';
      }
      
      var class  = string.replace(kind, '.', '_')..'_'..type;
      var js_uid = class;
  }}
  
<style type="text/css">
  #catalogs_Counteragents_item .ae_editform_field_name {
    color: #4F6B72;
    font-family: Verdana,Arial,Sans-Serif;
    font-size: 12px;
  }
  #catalogs_Counteragents_item .ae_tabular_section {
    width: 100% !important;
  }
  
  .oe_hide_field_errors {
     min-height: 17px;
  }
  .oe_fields {
    background-color: #FCFCFC;
    padding: 2px 8px 8px 8px;
    border: 1px solid #AAAAAA;
    min-width: 500px;
  }
  .oe_fields li {
    font-family: Verdana,Arial,Sans-Serif;
    font-size: 12px;
  }
  .oe_field {
    clear: both;
    padding-top: 3px;
  }
  .oe_field .ae_editform_field_name {
    float: left;
    margin: 3px 7px 0 0;
    /*width: 36px;*/
    overflow: hidden;
  }
  
  .oe_field_code {
    width: 170px;
    float: left;
    clear: none !important;
  }
  
  .oe_field_parent {
    float: left;
    width: 250px;
    clear: none !important;
  }
  .oe_field_parent .ae_editform_field_name {
     width: 45px !important;
  }
  
  .oe_checkboxes {
    margin: 4px 0 45px 42px;
  }
  .oe_field_1 {
    float: left;
    margin-top: 0;
    width: 153px;
  }
  .oe_checkbox_errors .oe_hide_field_errors {
    width: 153px;
    min-height: 1px;
    float: left;
  }
  .oe_checkbox_errors ul {
    width: 140px;
    padding-left: 13px !important;
  }
  .oe_field_1 .ae_editform_field_name {
    float: left;
    margin: 3px 7px 0 0;
    overflow: hidden;
  }
  
  .oe_field_2 {
    clear: both;
    margin-top: 2px;
  }
  .oe_field_2 .ae_editform_field_name {
    height: 14px;
    margin: 17px 0px 5px 0;
  }
  .oe_field_2 .ae_editform_field_value {
    clear: both;
    margin-left: 42px;
  }
  
  .oe_field_2 textarea {
    width: 453px;
    height: 84px;
  }
  
  /*#catalogs_Counteragents_item .ae_tabular_section {
    width: 536px;
  }*/
  .ae_tabular_section th {
    width: 32%;
  }
  
  .oe_tabulars {
    margin: 27px 0 25px;
  }
  .oe_cf_controls {
    margin: 0px 3px 0;
  }
</style>

  <form method="post" action="#" class="ae_object_edit_form" id="{{ class..'_item' }}" enctype="{{ enctype }}">
    {{ web.html(hidden) }}
    <div class="{{ class..'_message systemmsg' }}" style="display: none;">
      <div class="inner">
        <ul class="flashMsg">
          <li>&nbsp;</li>
        </ul>
      </div>
    </div>
    <div class="oe_fields">
    
  <eval:if test="use_tabulars">
  
<style>
  .catalogs_Counteragents_Code_value input[type=text] {
    width: 80px;
  }
  .catalogs_Counteragents_Description_value input[type=text] {
    width: 450px;
  }
  .oe_field .ae_editform_field_name {
    width: 36px;
  }
  .oe_field .ae_editform_field_errors {
    padding-left: 54px !important;
  }
</style>
  
      {{
         var field  = 'Code';
         let fields = list.select(fields, "$ != '"..field.."'");
         var name   = name_prefix..'[attributes]['..field..']';
         var value  = item[field];
         var f_name = string.ToUpperFirst(field);
         var f_type = field_type[field];
         var params = {
            required:  list.contains(required, field),
            precision: field_prec[field]
         };
      }}
      <div class="oe_field oe_field_code">
        <div class="oe_hide_field_errors">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none"><li>&nbsp;</li></ul>
        </div>
        <div class="{{ class..'_'..field..'_name ae_editform_field_name' }}">{{ f_name }}:</div>
        <div class="{{ class..'_'..field..'_value ae_editform_field_value' }}">
          <pre class="script">
             var template = root..'/EditFormFields';
             var content  = wiki.template(template, [f_type, name, value, params, type, template, prefix]);
             
             if (string.contains(content, 'href="'..template..'"')) {
                let content = 'Template not found';
             }
             
             content;
          </pre>
        </div>
      </div>
      {{
         var field  = 'Parent';
         let fields = list.select(fields, "$ != '"..field.."'");
         var name   = name_prefix..'[attributes]['..field..']';
         var value  = item[field];
         var f_name = string.ToUpperFirst(field);
         var f_type = field_type[field];
         var params = {
            select:    select[field],
            required:  list.contains(required, field),
            dynamic:   list.contains(dynamic, field),
            precision: field_prec[field]
         };
      }}
      <div class="oe_field oe_field_parent">
        <div class="oe_hide_field_errors">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none"><li>&nbsp;</li></ul>
        </div>
        <div class="{{ class..'_'..field..'_name ae_editform_field_name' }}">{{ f_name }}:</div>
        <div class="{{ class..'_'..field..'_value ae_editform_field_value' }}">
          <pre class="script">
             var template = root..'/EditFormFields';
             var content  = wiki.template(template, [f_type, name, value, params, type, template, prefix]);
             
             if (string.contains(content, 'href="'..template..'"')) {
                let content = 'Template not found';
             }
             
             content;
          </pre>
        </div>
      </div>
      {{
         var field  = 'Description';
         let fields = list.select(fields, "$ != '"..field.."'");
         var name   = name_prefix..'[attributes]['..field..']';
         var value  = item[field];
         var f_name = 'Desc';
         var f_type = field_type[field];
         var params = {
            required:  list.contains(required, field),
            precision: field_prec[field]
         };
      }}
      <div class="oe_field">
        <div class="oe_hide_field_errors">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
        </div>
        <div class="{{ class..'_'..field..'_name ae_editform_field_name' }}">{{ f_name }}:</div>
        <div class="{{ class..'_'..field..'_value ae_editform_field_value' }}">
        <pre class="script">
             var template = root..'/EditFormFields';
             var content  = wiki.template(template, [f_type, name, value, params, type, template, prefix]);
             
             if (string.contains(content, 'href="'..template..'"')) {
                let content = 'Template not found';
             }
             
             content;
          </pre>
        </div>
      </div>
    <div class="oe_checkboxes">
      <div class="oe_checkbox_errors">
        <div class="oe_hide_field_errors">
          <ul class="{{ class..'_Customer_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
        </div>
        <div class="oe_hide_field_errors">
          <ul class="{{ class..'_Supplier_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
        </div>
        <div style="width: 0; height: 0; clear: both;">&nbsp;</div>
      </div>
      {{
         var field  = 'Customer';
         let fields = list.select(fields, "$ != '"..field.."'");
         var name   = name_prefix..'[attributes]['..field..']';
         var value  = item[field];
         var f_name = string.ToUpperFirst(field);
         var f_type = field_type[field];
         var params = {
            required:  list.contains(required, field),
            precision: field_prec[field]
         };
      }}
      <div class="oe_field_1">
        <div class="{{ class..'_'..field..'_name ae_editform_field_name' }}">{{ f_name }}:</div>
        <div class="{{ class..'_'..field..'_value ae_editform_field_value' }}">
          <pre class="script">
             var template = root..'/EditFormFields';
             var content  = wiki.template(template, [f_type, name, value, params, type, template, prefix]);
             
             if (string.contains(content, 'href="'..template..'"')) {
                let content = 'Template not found';
             }
             
             content;
          </pre>
        </div>
      </div>
      {{
         var field  = 'Supplier';
         let fields = list.select(fields, "$ != '"..field.."'");
         var name   = name_prefix..'[attributes]['..field..']';
         var value  = item[field];
         var f_name = string.ToUpperFirst(field);
         var f_type = field_type[field];
         var params = {
            required:  list.contains(required, field),
            precision: field_prec[field]
         };
      }}
      <div class="oe_field_1">
        <div class="{{ class..'_'..field..'_name ae_editform_field_name' }}">{{ f_name }}:</div>
        <div class="{{ class..'_'..field..'_value ae_editform_field_value' }}">
          <pre class="script">
             var template = root..'/EditFormFields';
             var content  = wiki.template(template, [f_type, name, value, params, type, template, prefix]);
             
             if (string.contains(content, 'href="'..template..'"')) {
                let content = 'Template not found';
             }
             
             content;
          </pre>
        </div>
      </div>
    </div>
      {{
         var field  = 'Information';
         let fields = list.select(fields, "$ != '"..field.."'");
         var name   = name_prefix..'[attributes]['..field..']';
         var value  = item[field];
         var f_name = string.ToUpperFirst(field);
         var f_type = field_type[field];
         var params = {
            required:  list.contains(required, field),
            precision: field_prec[field],
            attrs:     {cols: 60, rows: 5},
            options:   {text: true}
         };
      }}
      <div class="oe_field_2">
        <div class="{{ class..'_'..field..'_name ae_editform_field_name' }}">
          <div style="float: left; margin-right: 20px;">{{ f_name }}:</div>
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
        </div>
        <div class="{{ class..'_'..field..'_value ae_editform_field_value' }}">
          <pre class="script">
             var template = root..'/EditFormFields';
             var content  = wiki.template(template, [f_type, name, value, params, type, template, prefix]);
             
             if (string.contains(content, 'href="'..template..'"')) {
                let content = 'Template not found';
             }
             
             content;
          </pre>
        </div>
      </div>
      {{
         var field  = 'InvoicingInformation';
         let fields = list.select(fields, "$ != '"..field.."'");
         var name   = name_prefix..'[attributes]['..field..']';
         var value  = item[field];
         var f_name = string.ToUpperFirst(field);
         var f_type = field_type[field];
         var params = {
            required:  list.contains(required, field),
            precision: field_prec[field],
            attrs:     {cols: 60, rows: 5},
            options:   {text: true}
         };
      }}
      <div class="oe_field_2">
        <div class="{{ class..'_'..field..'_name ae_editform_field_name' }}">
          <div style="float: left; margin-right: 20px;">{{ f_name }}:</div>
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
        </div>
        <div class="{{ class..'_'..field..'_value ae_editform_field_value' }}">
          <pre class="script">
             var template = root..'/EditFormFields';
             var content  = wiki.template(template, [f_type, name, value, params, type, template, prefix]);
           
             if (string.contains(content, 'href="'..template..'"')) {
                let content = 'Template not found';
             }
             
             content;
          </pre>
        </div>
      </div>
      
  </eval:if>
  <eval:else>
  
<style>
  .oe_field .ae_editform_field_name {
    width: 100px;
  }
  .oe_cf_controls {
    margin-top: 25px;
  }
  .oe_field .ae_editform_field_errors {
    padding-left: 118px !important;
  }
</style>

  </eval:else>
  
  <eval:if test="#fields &gt 0 || !use_tabulars">
    <eval:foreach var="field" in="fields">
      <eval:if test="#owners == 0 || field != 'OwnerId'">
        {{
           if (#owners > 0 && field == 'OwnerType') {
              var name   = name_prefix..'[attributes]';
              var value  = {OwnerType: item[field], OwnerId: item['OwnerId']};
              var f_name = 'Owner';
              var f_type = 'OwnerReference';
              var params = {
                 owners:    owners,
                 select:    select[field],
                 required:  list.contains(required, field),
                 dynamic:   list.contains(dynamic, field),
                 precision: field_prec[field]
              };
           }
           else {
              var name   = name_prefix..'[attributes]['..field..']';
              var value  = item[field];
              var f_name = string.ToUpperFirst(field);
              var f_type = field_type[field];
              var params = {
                 select:    select[field],
                 required:  list.contains(required, field),
                 dynamic:   list.contains(dynamic, field),
                 precision: field_prec[field]
              };
              
              if (references[field]) {
                 let params ..= {reference: references[field]};
              }
           }
        }}
        <div class="oe_field">
          <div class="oe_hide_field_errors">
            <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none"><li>&nbsp;</li></ul>
          </div>
          <div class="{{ class..'_'..field..'_name ae_editform_field_name' }}">{{ f_name }}:</div>
          <div class="{{ class..'_'..field..'_value ae_editform_field_value' }}">
            <pre class="script">
               var template = root..'/EditFormFields';
               var content  = wiki.template(template, [f_type, name, value, params, type, template, prefix]);
               
               if (string.contains(content, 'href="'..template..'"')) {
                  let content = 'Template not found';
               }
               
               content;
            </pre>
          </div>
        </div>
      </eval:if>
    </eval:foreach>
  </eval:if>
      
      
    <eval:if test="use_tabulars">
      {{ var tab_s = entities.getInternalConfiguration(kind..'.'..type..'.tabulars.tabulars'); }}
      <eval:foreach var="tabular" in="tab_s">
        <div class="oe_tabulars">
            {{
               var template = root..'/Tabulars/EditForm';
               var tpl_params = [
                 uid,
                 {main_kind: puid.kind, main_type: puid.type, kind: 'tabulars', type: tabular},
                 tabulars[tabular],
                 root,
                 template,
                 {name_prefix: name_prefix..'[tabulars]'},
                 prefix
               ];
               var content  = wiki.template(template, tpl_params);
               
               if (string.contains(content, 'href="'..template..'"'))
               {
                  let content = 'Template not found';
               }
               
               content;
            }}
        </div>
      </eval:foreach>
    </eval:if>
    
      <div class="oe_cf_controls">
        {{ &lt;input type="button" value="Save and Close" class="ae_command" command="save_and_close" /&gt;&nbsp; }}
        {{ &lt;input type="button" value="Save" class="ae_command" command="save" /&gt;&nbsp; }}
        {{ &lt;input type="button" value="Close" class="ae_command" command="cancel" /&gt; }}
      </div>
    </div>
    
  
  
  </form>
  {{ &lt;script type="text/javascript"&gt;" ae_name_prefix[\'"..js_uid.."\'] = \'"..name_prefix.."[attributes]\';"&lt;/script&gt; }}
</eval:else>
