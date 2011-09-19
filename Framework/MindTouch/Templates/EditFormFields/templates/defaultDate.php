{{
  var name   = args[0];
  var value  = args[1];
  var params = args[2];
  var attrs  = params.attrs ?? {};
  var attributes = '';

  foreach (var name in  map.Keys(attrs)) {
    let attributes = attributes..'" '..name..'="'..attrs[name];
  }
  
  if (#attributes != 0) {
    let name = name..attributes;
  }
  
  if (attrs.id is nil) {
    var id = string.replace(name, '[', '_');
    let id = string.replace(id, ']', '');
    let name = name..'" id="'..id;
  }
  else {
    var id = attrs.id;
  }
  
  if (value == '0000-00-00') {
    let value = '';
  }
}}
<nobr>
  {{ web.html('&lt;input type="text" name="'..name..'" value="'..value..'" /&gt;'); }}
  <img class="oef_datetime_picker" onclick="{{ 'if (!document.getElementById(\''..id..'\').disabled) NewCssCal(\''..id..'\',\'yyyymmdd\',\'arrow\',false, 24, false)' }}" alt="Pick a date" src="/ext/OEF/Framework/MindTouch/Js/datetimepicker/images/cal.gif" style="vertical-align: top; padding-top: 1px;" />
</nobr>
