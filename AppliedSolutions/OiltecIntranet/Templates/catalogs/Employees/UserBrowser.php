<head>
    <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/ae_edit_form.js"></script>
    <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/ae_list_form.js"></script>
</head>
<body>
<style type="text/css">
/************************************* UserBrowser ********************************************/
.popup_photo {
	position: absolute;
	background-image: url(/skins/aconawellpro/images/popup_photo_bg.png);
	background-repeat: no-repeat;
	left: 23px;
	top: -12px;
	height: 100px;
	width: 110px;
}
.popup_padd {
	padding-top: 5px;
	padding-left: 15px;
}
.popup_photo img {
	border: 1px solid #FFF;
}
#blanket {
   background-color: transparent;
   opacity: 0.65;
   position:absolute;
   z-index: 9001; /*ooveeerrrr nine thoussaaaannnd*/
   top:0px;
   left:0px;
   width:100%;
   min-width: 938px;
}
#popUpDiv {
	position: absolute;
	background-color: #eeeeee;
	width: 600px;
	padding-bottom: 10px;
	z-index: 9002; /*ooveeerrrr nine thoussaaaannnd*/
}
.userTabs {
    width: 96% !important;
}
#popUpDiv .locFooter {
    background-color: #eeeeee;
}
#popUpDiv .userTabs td {
    border-color: #b0b0b0;
}
#popUpDiv .userTabs a {
    border-color: #b0b0b0;
}
#popUpDiv .userTabs td.active {
    border-bottom: 1px solid #eeeeee;
}

#catalogs_UserBrowser_container {
	overflow: auto;
    padding: 7px 0 4px;
    width: 721px;
}

#catalogs_UserBrowser_list_block .photo {
	text-align: center;
}

.show_btn {
	margin-top: 6px;
}
</style>

<h3>User Browser</h3>

<div class="catalogs_UserBrowser_message systemmsg" style="display: none;">
  <div class="inner">
    <ul class="flashMsg">
      <li>&nbsp;</li>
    </ul>
  </div>
</div>

<?php $prefix = $form_prefix.'[attributes]' ?>
<div id="catalogs_UserBrowser_container">

<form method="post" action="#" class="oe_custom_edit_form" id="catalogs_Employees_item" enctype="multipart/form-data">
  <input type="hidden" name="<?php echo $form_prefix."[name]"; ?>" value="<?php echo $name ?>" />
  <table>
    <thead>
    <tr>
        <th style="width: 190px;">Unit</th>
        <th style="width: 38px;">Photo</th>
        <th>Name</th>
        <th>Surname</th>
        <th>Phone</th>
        <th>Position</th>
    </tr>
    </thead>
    <tbody id="catalogs_UserBrowser_list_block" class="ae_list_block">
    <?php
       foreach ($data as $unit => $values)
       {
          $params = array(
             'level'     => 0,
             'is_folder' => true,
             'item'      => $values['unit']
          );
          
          echo self::include_template('user_browser_item', $params);
          
          foreach ($values['list'] as $pid => $item)
          {
             $params = array(
                'level'      => 1,
                'parent'     => $unit,
                'is_folder'  => false,
                'item'       => $item,
                'upload_dir' => $uploadDir
             );
             
             echo self::include_template('user_browser_item', $params);
          }
       }
    ?>
    </tbody>
</table>

<input type="hidden" id="selectedUser" value=""/>
</form>

</div>

<div style="display: none;" id="divShowABPhotoDialog" class="popup_photo">
    <div class="popup_padd">
        <div style="width: 82px; height:82px; overflow: hidden; text-align: center; vertical-align: middle;">
            <img id="divShowABPhotoDialogImg" style="max-height: 80px; max-width: 80px;" src="./" alt="Photo" />
        </div>
    </div>
</div>

<div id="blanket" style="display:none;" onclick="popup('popUpDiv')"></div>
	<div id="popUpDiv" style="display:none;">
        <img src="/skins/aconawellpro/images/close.png"  onclick="popup('popUpDiv')" style="float:right;cursor: pointer;margin-top: -15px;margin-right: -15px;"/>
        <div id="userProfilePopUp" style="padding: 20px 20px 0 20px;"></div>
	</div>

<h1 class="show_btn"><a href="#" onclick="popup('popUpDiv'); return false;">Show selected user profile</a></h1>


<script type="text/javascript">
function FindElementPos(element)
{
    var x = y = 0;

	do
	{
		x += element.offsetLeft;
		y += element.offsetTop;
	}
	while (element = element.offsetParent);
	
	x -= document.getElementById('catalogs_UserBrowser_container').scrollLeft;
	y -= document.getElementById('catalogs_UserBrowser_container').scrollTop;

	return {'x':x,'y':y};
}

function ShowABPhotoDialog(control, url)
{
    var ctlDiv = null;
    var ctlControl = null;

    if((ctlDiv = document.getElementById('divShowABPhotoDialog')) == null)
        return;

    if ((ctlControl = document.getElementById(control)) == null)
        return;

    var pos = FindElementPos(ctlControl);

    ctlDiv.style.position = 'absolute';
    ctlDiv.style.left = '0px';
    ctlDiv.style.top = '0px';
    ctlDiv.style.display = 'block';

    ctlDiv.style.left = (pos.x + 22) + 'px';
    ctlDiv.style.top = (pos.y - 12) + 'px';
    ctlDiv.style.display = 'block';

    var ctlImage = document.getElementById("divShowABPhotoDialogImg");

    if (ctlImage)
    {
        ctlImage.src = url;
    }
}

function HideABPhotoDialog(urlDefNoImage)
{
    var ctlDiv = null;

    if((ctlDiv = document.getElementById('divShowABPhotoDialog')) != null)
    {
        ctlDiv.style.display = 'none';
    }
    var ctlImage = document.getElementById("divShowABPhotoDialogImg");
    if (ctlImage)
    {
        ctlImage.src = urlDefNoImage;
    }
}


/*document.onclick=check;

var Ary=[];

function check(e) {
     var target = (e && e.target) || (event && event.srcElement);
     while (target.parentNode){
         if (target.className.match('pop')||target.className.match('poplink')) return;
            target=target.parentNode;
     }
     var ary=zxcByClassName('pop')
     for (var z0=0;z0<ary.length;z0++){
        ary[z0].style.display='none';
     }
}
function zxcByClassName(nme,el,tag){
     if (typeof(el)=='string')
         el=document.getElementById(el);
     el=el||document;
     for (var tag=tag||'*',reg=new RegExp('\\b'+nme+'\\b'),els=el.getElementsByTagName(tag),ary=[],z0=0; z0<els.length;z0++){
        if(reg.test(els[z0].className))
            ary.push(els[z0]);
    }
    return ary;
}

function toggle(layer_ref) {
     var hza = document.getElementById(layer_ref);
     if (hza && hza.style){
          if (!hza.set)
          {
              hza.set=true;  Ary.push(hza);
          }
          hza.style.display = (hza.style.display == '')? 'none':'';
     }
}*/
function toggle(div_id) {
	var el = document.getElementById(div_id);
	if ( el.style.display == 'none' ) {	el.style.display = 'block';}
	else {el.style.display = 'none'; appActive();}
}
function blanket_size(popUpDivVar) {
	if (typeof window.innerWidth != 'undefined') {
		viewportheight = window.innerHeight;
	} else {
		viewportheight = document.documentElement.clientHeight;
	}
	if ((viewportheight > document.body.parentNode.scrollHeight) && (viewportheight > document.body.parentNode.clientHeight)) {
		blanket_height = viewportheight;
	} else {
		if (document.body.parentNode.clientHeight > document.body.parentNode.scrollHeight) {
			blanket_height = document.body.parentNode.clientHeight;
		} else {
			blanket_height = document.body.parentNode.scrollHeight;
		}
	}
	var blanket = document.getElementById('blanket');
	blanket.style.height = blanket_height+ 'px';
	
	var popUpDiv = document.getElementById(popUpDivVar);
	popUpDiv_height=blanket_height/2-480;//150 is half popup's height
	popUpDiv.style.top = jQuery(window).scrollTop() + Math.floor(0.1*jQuery(window).height()) + 'px';
}
function window_pos(popUpDivVar) {
	if (typeof window.innerWidth != 'undefined') {
		viewportwidth = window.innerHeight;
	} else {
		viewportwidth = document.documentElement.clientHeight;
	}
	if ((viewportwidth > document.body.parentNode.scrollWidth) && (viewportwidth > document.body.parentNode.clientWidth)) {
		window_width = viewportwidth;
	} else {
		if (document.body.parentNode.clientWidth > document.body.parentNode.scrollWidth) {
			window_width = document.body.parentNode.clientWidth;
		} else {
			window_width = document.body.parentNode.scrollWidth;
		}
	}
	var popUpDiv = document.getElementById(popUpDivVar);
	window_width=window_width/2-380;//150 is half popup's width
	popUpDiv.style.left = '50%';
	popUpDiv.style.margin = '0 0 0 -220px';
}
function popup(windowname)
{
	var value = document.getElementById('selectedUser').value;

	if(value == "") return false;

	var params = {
		"person": value,
		"form":   'UserProfile',
		"uid":    'catalogs.Employees',
		"tag_id": 'userProfilePopUp'
	};

	appInactive();
	appAddLoader();
	
	displayCustomForm('catalogs.Employees','UserProfile' , params, 'userProfilePopUp');

	blanket_size(windowname);
	window_pos(windowname);
	toggle('blanket');
	toggle(windowname);

	appDisplayLoader(false); 
}
function selectColumn(element, prefix)
{
	jQuery('#' + prefix + '_list_block .ae_current').removeClass('ae_current');
	jQuery(element).parent().addClass('ae_current');

	return false;
}





jQuery(document).ready(function() {
	
	jQuery('.oef_tree_active').click(clickTree);
});

function clickTree(event)
{
	hideMessages();
	
	event = event || window.event;
	
	var node = event.target || event.srcElement;
	
	event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
	
	var item = jQuery(node).parents('.ae_list_item').get(0);
	var tree = new oefTree();
	
	tree.onClick(item);
}

function oefTree()
{
	var uid    = null;
	var prefix = null;
	var item   = null;
	
	/**
	 * Process onClick event
	 * 
	 * @param DOMElement _item
	 * @return void
	 */
	this.onClick = function(_item)
	{
		item = _item;
		
		prefix = jQuery(item).parents('.ae_list_block').attr('id');
		prefix = prefix.replace('_list_block', '');
		uid    = prefix.replace('_', '.');
		
		var nodeId = jQuery(item).find('.ae_item_id').text();
		
		if (jQuery(item).find('.oef_tree_active').hasClass('oef_tree_closed'))
		{
			this.open(nodeId);
		}
		else
		{
			this.close(nodeId);
		}
	};
	
	/**
	 * Open node
	 * 
	 * @return boolean
	 */
	this.open = function(nodeId)
	{
		jQuery(item).find('.oef_tree_active').removeClass('oef_tree_closed').end().
			find('.oef_tree_folder, .oef_tree_item').removeClass('oef_tree_closed');

		var fid = jQuery(item).find('.ae_item_id').text();

		jQuery(item).parents('.ae_list_block').find('.ae_list_item[parent="' + fid + '"]').css('display', 'table-row');
		
		return true;
	};
	
	/**
	 * Close node
	 * 
	 * @return boolean
	 */
	this.close = function(nodeId)
	{
		jQuery(item).find('.oef_tree_active').addClass('oef_tree_closed').end().
			find('.oef_tree_folder, .oef_tree_item').addClass('oef_tree_closed');
		
		var padding = jQuery(item).find('.oef_tree_control').css('padding-left');
		padding = parseInt(padding.replace("px", ""), 10);
		
		var current = jQuery(item).next().get(0);
		
		if (!current) return true;
		
		var padd = jQuery(current).find('.oef_tree_control').css('padding-left');
		padd = parseInt(padd.replace("px", ""), 10);
		
		while (padd > padding)
		{
			var prev = current;
			current  = jQuery(current).next().get(0);
			
			if (padd > padding) {
				jQuery(prev).css('display', 'none');
			}
			
			if (!current) return true;
			
			padd = jQuery(current).find('.oef_tree_control').css('padding-left');
			padd = parseInt(padd.replace("px", ""), 10);
		}
		
		return true;
	};	
}
</script>
</body>