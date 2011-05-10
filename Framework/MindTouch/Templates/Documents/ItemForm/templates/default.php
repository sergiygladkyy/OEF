{{
   var uid    = args[0];
   var puid   = args[1];
   var data   = args[2];
   var root   = args[3] ?? 'Template:Entities';
   var prefix = args[4] ?? 'default';
   var fields = {};
   var field_type = {};
   var references = {}; 
   var layout     = [];
   var kind     = '';
   var type     = puid.type;
   var item     = data.item;
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
<eval:elseif test="item._id &lt; 1">
  <ul class="ae_errors">
    <li class="ae_error">Invalid entity id</li>
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
      let fields     = entities.getInternalConfiguration(kind..'.fields', type);
      let references = entities.getInternalConfiguration(kind..'.references', type);
      let layout     = entities.getInternalConfiguration(kind..'.layout', type);
      
      var tab_s = entities.getInternalConfiguration(kind..'.'..type..'.tabulars.tabulars');
      
      var class = string.replace(kind, '.', '_')..'_'..type;
  }}
  <div class="{{ class..'_message systemmsg' }}" style="display: none;">
    <div class="inner">
      <ul class="flashMsg">
        <li>&nbsp;</li>
      </ul>
    </div>
  </div>
  <eval:if test="item._deleted != 0">
  <div  style="float: right; margin: 30px 20px 0px 10px; font-size:12px; font-weight: bold; color: rgb(218, 6, 32);">Mark for deletion</div>
  </eval:if>
  <div style="clear: both; height: 0;">&nbsp;</div>
  <table>
    <tbody>
      <tr>
        <td class="ae_itemform_field_name">Status:</td>
        <td class="ae_itemform_field_value">
          <div class="{{ item._post &gt; 0 ? 'ae_field_posted' : 'ae_field_not_posted' }}">
            <span class="ae_field_posted_text" style="{{ item._post &gt; 0 ? 'display: block;' : 'display: none;' }}">This document is posted.</span>
            <span class="ae_field_not_posted_text" style="{{ item._post &gt; 0 ? 'display: none;' : 'display: block;' }}">This document is not posted.</span>
          </div>
        </td>
      </tr>
    <eval:foreach var="field" in="fields">
      <tr>
        <td class="ae_itemform_field_name">{{ string.ToUpperFirst(field); }}:</td>
        <td class="ae_itemform_field_value">
          <pre class="script">
            var params = {reference: references[field]};
            
            var template = root..'/ItemFormFields';
            var content  = wiki.template(template, [field_type[field], item[field], params, type, template, prefix]);
      
            if (string.contains(content, 'href="'..template..'"')) {
              let content = 'Template not found';
            }
          
            content;
          </pre>
        </td>
      </tr>
    </eval:foreach>
    <eval:foreach var="tabular" in="tab_s">
      <tr>
        <td colspan="2">
          {{
             var template = root..'/Tabulars/ItemForm';
             var tpl_params = [
               uid,
               {main_kind: puid.kind, main_type: puid.type, kind: 'tabulars', type: tabular},
               tabulars[tabular],
               root,
               template,
               [],
               prefix
             ];
             var content = wiki.template(template, tpl_params);
      
             if (string.contains(content, 'href="'..template..'"'))
             {
                let content = 'Template not found';
             }
          
             content;
          }}
        </td>
      </tr>
    </eval:foreach>
      <tr>
        <td class="ae_edit" colspan="2">
          <a href="#" target="_self" onclick="{{ 'javascript: editItem(this, \''..kind..'\', \''..type..'\', '..item._id..'); return false;' }}">Edit</a>&nbsp;|
          <eval:if test="#layout &gt; 0">
            <a href="#" onclick="{{ 'if (!onPrint(this, \''..kind..'\', \''..type..'\', '..item._id..', '..Json.Emit(layout)..')) return false;' }}">Print</a>&nbsp;|
          </eval:if>
          <a href="#" onclick="{{ 'window.self.close(); if (window.opener && window.opener.length) { window.opener.focus(); } return false;' }}">Close</a>
        </td>
      </tr>
    </tbody>
  </table>
</eval:else>

