{{
    var name    = args[0];
    var value   = args[1];
    var params  = args[2];
    var uid     = params.uid;
    var id      = params.id &gt; 0 ? params.id : 0;
    var select  = params.select ?? {};
    var attrs   = params.attrs ?? {};
    var owners  = params.owners ?? {};
    var reference  = params.reference ?? {};
    var precision  = params.precision ?? {};
    var dynamic    = params.dynamic ?? false;
    
    var otype = value.OwnerType ?? '';
    var oid   = value.OwnerId ?? 0;
    var text  = oid > 0 ? select[oid]['text'] : '&nbsp;';
}}
<div class="oef_edit_box_container">
  <div class="oef_edit_box_input_container">
    <input type="hidden" name="{{ name..'[OwnerType]' }}" value="{{ otype }}" class="oef_edit_box_input oef_edit_box_otype">
    <input type="hidden" name="{{ name..'[OwnerId]' }}" value="{{ oid }}" class="oef_edit_box_input oef_edit_box_oid">
    <div class="oef_edit_box_text">{{ text }}</div>
  </div>
  <div class="oef_edit_box_btns_container">
    {{
      &lt;div class="oef_edit_box_button" command="select" title="select"&gt;
        &lt;img src="/skins/common/icons/sel.png" class="oef_edit_box_button_pict" /&gt;
      &lt;/div&gt;
      &lt;div class="oef_edit_box_button" command="clear" title="clear"&gt;
        &lt;img src="/skins/common/icons/clear.png" class="oef_edit_box_button_pict" /&gt;
      &lt;/div&gt;
    }}
  </div>
  <div style="height: 0px; clear: both;">&nbsp;</div>
  <table class="oef_edit_box_item_container oef_owners_container" style="display: none;">
    <tr>
      <td class="oef_edit_box_item">&nbsp;</td>
    </tr>
    <eval:foreach var="owner" in="owners">
      <tr>
        {{ 
           &lt;td class="oef_edit_box_item" uid=(uid) id=(id) otype=(owner)&gt;
             owner;
           &lt;/&gt;;
        }}
      </tr>
    </eval:foreach>
  </table>
</div>
