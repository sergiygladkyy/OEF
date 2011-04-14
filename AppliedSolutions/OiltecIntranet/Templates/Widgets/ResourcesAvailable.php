var zone = $0?? 'zone_0' ;
<html>
  <head>
    <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"></script>
    <script type="text/javascript">
      "jQuery(document).ready(function() { jQuery('#"..zone.."').html('<div class=\"oef_content\"><h3 style=\"clear:both\">ResourcesAvailable</h3><div id=\"oef_resources_available\">&nbsp;</div></div>'); });"
    </script>
  </head>
  <body></body>
  <tail>
    <script type="text/javascript">
	  "Widgets.showWidget({
        'load': {
	       'solution': 'OiltecIntranet',
	       'service':  'Pm',
	       'method':   'ResourcesAvailable',
	       'authMethod': 'MTAuth',
	       'authtoken' : '"..user.authtoken.."',
	       'attributes': {}
        },
        'view': {
	       'widget':  'Grid',
	       'tag_id':  'oef_resources_available',
	       'options': {}
        }
      });"
    </script>
  </tail>
</html>
