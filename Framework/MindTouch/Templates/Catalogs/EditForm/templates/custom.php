{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var field_prec = {};
   var required = []; 
   var kind     = '';
   var type     = puid.type;
   var item     = data.item;
   var select   = data.select;
   var tabulars = data.tabulars;
}}
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
      var name_prefix = 'aeform['..kind..']['..type..']';
      let field_type = entities.getInternalConfiguration(kind..'.field_type', type);
      let field_prec = entities.getInternalConfiguration(kind..'.field_prec', type);
      let fields   = entities.getInternalConfiguration(kind..'.fields', type);
      let required = entities.getInternalConfiguration(kind..'.required', type);
      
      var tab_s    = entities.getInternalConfiguration(kind..'.'..type..'.tabulars.tabulars');
      if (item._id > 0) {
         var header = 'Edit ';
         var hidden = '&lt;input type="hidden" name="'..name_prefix..'[attributes][_id]" value="'..item._id..'" /&gt;';
         var button = 'Update';
      }
      else {
         var header = 'New ';
         var hidden = '';
         var button = 'Create';
      }
      
      var class  = string.replace(kind, '.', '_')..'_'..type;
      var js_uid = class;
  }}
  <style type="text/css">
    .ae_object_edit_form fieldset {
       border: 1px solid #999999;
       padding: 10px;
       margin: 10px 0px;
       color: #444444;
       background-color: #F5F5F5;
    }
    .ae_custom_header {
       font-size: 20px;
       font-style: italic;
       color: #B58228;
       margin: 10px 0px;
       border-top: 0 none;
    }
    .ae_custom_tabular_form table {
       background-color: #FFFFFF;
    }
    .ae_custom_tabular_form .ae_tabular_section {
       max-height: 203px;
    }
  </style>
  <h3 id="{{ class..'_header' }}" class="ae_custom_header">{{ header..string.ToUpperFirst(string.remove(puid.kind, string.length(puid.kind)-1, 1))..' "'..type..'"' }}</h3>
  <form method="post" action="#" class="ae_object_edit_form" id="{{ class..'_item' }}">
    {{ web.html(hidden) }}
    <div class="{{ class..'_message systemmsg' }}" style="display: none;">
      <div class="inner">
        <ul class="flashMsg">
          <li>&nbsp;</li>
        </ul>
      </div>
    </div>
    <fieldset style="background-image: url('ext/AE/background.jpg'); background-repeat: no-repeat; background-position: right top;">
      <legend>General:</legend>
      <eval:foreach var="field" in="fields">
      {{ var field_id = class..'_field_'..field }}
      <label for="{{ field_id }}" class="{{ class..'_name ae_editform_field_name' }}">{{ string.ToUpperFirst(field); }}:</label>
        <div class="{{ class..'_value ae_editform_field_value' }}">
          <ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>
          <pre class="script">
            var name   = name_prefix..'[attributes]['..field..']';
            var params = {select: select[field], required: list.contains(required, field), precision: field_prec[field], attrs: {id: field_id}};
          
            var template   = root..'/EditFormFields';
            var content    = wiki.template(template, [field_type[field], name, item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </div>
        <div style="height: 10px;">&nbsp;</div>
      </eval:foreach>
    </fieldset>
    <fieldset>
      <legend>Tabular sections:</legend>
      <eval:foreach var="tabular" in="tab_s">
        <div class="ae_custom_tabular_form">
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
    </fieldset>
    <div class="ae_submit">
      <input type="submit" name="submit" value="{{ button }}" />
    </div>
  </form>
  {{ &lt;script type="text/javascript"&gt;" ae_name_prefix[\'"..js_uid.."\'] = \'"..name_prefix.."[attributes]\';"&lt;/script&gt; }}
</eval:else>
{{awpskin.hideAll();}}