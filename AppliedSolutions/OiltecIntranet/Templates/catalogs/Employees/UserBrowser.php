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
   background-color:#111;
   opacity: 0.65;
   position:absolute;
   z-index: 9001; /*ooveeerrrr nine thoussaaaannnd*/
   top:0px;
   left:0px;
   width:100%;
}
#popUpDiv {
	position:absolute;
	background-color:#eeeeee;
	width:300px;
	height:300px;
	z-index: 9002; /*ooveeerrrr nine thoussaaaannnd*/
}
</style>
<div class="catalogs_Employees_message systemmsg" style="display: none;">
  <div class="inner">
    <ul class="flashMsg">
      <li>&nbsp;</li>
    </ul>
  </div>
</div>

<!--<div class="info_message systemmsg" style="display: none; clear: both; margin-bottom: 20px !important; width: 528px;">
  <div style="padding: 0 6px;">
    <ul class="flashMsg">
      <li>&nbsp;</li>
    </ul>
  </div>
</div>-->
<?php if (empty($attrs)): ?>
  <div style="height: 250px; border: 1px solid #DDDDDD; background-color: #F8F8F8; margin-bottom: 10px;">

      <script>
        displayMessage('catalogs_Employees', 'Employees not exists. You can create a new employees with a document <a href="?uid=documents.RecruitingOrder&actions=displayListForm">RecruitingOrder</a>.', 2);
      </script>

  </div>
<?php else: ?>
  <?php $prefix = $form_prefix.'[attributes]' ?>


<form method="post" action="#" class="oe_custom_edit_form" id="catalogs_Employees_item" enctype="multipart/form-data">
  <input type="hidden" name="<?php echo $form_prefix."[name]"; ?>" value="<?php echo $name ?>" />
  <input type="hidden" value="<?php echo $attrs["Person"]["_id"]?>" name="<?php echo $prefix."[_id]"; ?>" />
<table>
    <thead>
    <tr>
        <th>Photo</th>
        <th>Name</th>
        <th>Surname</th>
        <th>Phone</th>
        <th>Unit</th>
        <th>Position</th>
    </tr>
    </thead>
    <tbody id="catalogs_UserBrowser_list_block" class="ae_list_block">
<?php foreach ($attrs as $value ): ?>
    <tr class="catalogs_UserBrowser_list_item ae_list_item">
        <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser');document.getElementById('selectedUser').value='<?php echo $value["Person"]["_id"];?>';">
            <a onmouseover="ShowABPhotoDialog('photo_img_ctl_<?php echo $uploadDir.$value["Person"]["_id"]?>', '<?php echo $uploadDir.'preview'.$value["Person"]["Photo"]?>'); return true;"
               onmouseout="HideABPhotoDialog('<?php echo $uploadDir.'preview'.$value["Person"]["Photo"]?>'); return true;">
                <img id="photo_img_ctl_<?php echo $uploadDir.$value["Person"]["_id"]?>" width="23" height="16" src="/skins/aconawellpro/images/photo_icn.gif">
            </a>
        </td>
        <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser');document.getElementById('selectedUser').value='<?php echo $value["Person"]["_id"];?>';">
            <?php echo $value["Person"]["Name"];?>
        </td>
        <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser');document.getElementById('selectedUser').value='<?php echo $value["Person"]["_id"];?>';">
            <?php echo $value["Person"]["Surname"];?>
        </td>
        <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser');document.getElementById('selectedUser').value='<?php echo $value["Person"]["_id"];?>';">
            <?php echo $value["Person"]["Phone"];?>
        </td>
        <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser');document.getElementById('selectedUser').value='<?php echo $value["Person"]["_id"];?>';">
            <?php echo $value["StaffRecord"]["OrganizationalUnit"]["text"];?>
        </td>
        <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser');document.getElementById('selectedUser').value='<?php echo $value["Person"]["_id"];?>';">
            <?php echo $value["StaffRecord"]["OrganizationalPosition"]["text"];?>
        </td>
    </tr>
<?php endforeach; ?>
    </tbody>
</table>

<input type="hidden" id="selectedUser" value=""/>
</form>
<div style="display: none;" id="divShowABPhotoDialog" class="popup_photo">
    <div class="popup_padd">
        <img id="divShowABPhotoDialogImg" height="80" width="80" src="./" alt="Фото" />
    </div>
</div>




<div id="blanket" style="display:none;" onclick="popup('popUpDiv')"></div>
	<div id="popUpDiv" style="display:none;width:800px;height:640px;" >
                <img src="/skins/aconawellpro/images/close.png"  onclick="popup('popUpDiv')" style="float:right;cursor: pointer;padding:10px;"/>
                <div id="userProfilePopUp" style="padding: 20px;"></div>
	</div>

<h1><a href="#" onclick="popup('popUpDiv')">Show selected user profile</a></h1>


<!--
<a href="#"  class="poplink" onclick="toggle('div1');return false;" id="link">Link</a><br />
<a href="#"  class="poplink" onclick="toggle('tst');return false;" id="link">Link2</a>

<div id="div1" class="pop" style="position: relative; margin: 0 auto; width: 800px; display: none; border: dotted;">
	<div style="width: 800px; height: 35px; background-color: #000;"></div><br /><br /><br /><br />
<input id="tst"  class="pop" type="button" name="" value="TEST" />

</div>-->
<script type="text/javascript">
function FindElementPos(element)
{
    var x = y = 0;
//        Works for FF
//        if (element.x && element.y)
//        {
//            x = element.x;
//            y = element.y;
//        }
//        else
//    {
    while ((element = element.offsetParent) != null)
    {
                x += element.offsetLeft;
                y += element.offsetTop;
    }
//   }

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

    var posOffset = FindElementPos(ctlDiv);

    ctlDiv.style.left = (pos.x - posOffset.x + 28) + 'px';
    ctlDiv.style.top = (pos.y - posOffset.y - 5) + 'px';
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
	else {el.style.display = 'none';}
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
	popUpDiv.style.top = popUpDiv_height + 'px';


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
	popUpDiv.style.left = window_width + 'px';
}
function popup(windowname) {
        var value = document.getElementById('selectedUser').value;
        if(value=="")
            return false;

	blanket_size(windowname);
	window_pos(windowname);
	toggle('blanket');
	toggle(windowname);
        var params = {
           "employee": document.getElementById('selectedUser').value,
           "form": "UserProfile",
           "uid": "catalogs.Employees",
           "tag_id": 'userProfilePopUp'
           };

        /*var params = new Array();

        params['employee'] = '1';
        params['form'] = 'UserProfile';
        params['uid'] = 'catalogs.Employees';
        params['tag_id'] = 'popUpDiv';*/

        displayCustomForm('catalogs.Employees','UserProfile' , params, 'userProfilePopUp');
}
function selectColumn(element, prefix)
{
	jQuery('#' + prefix + '_list_block .ae_current').removeClass('ae_current');
	jQuery(element).parent().addClass('ae_current');

	return false;
}
</script>
<?php endif;?>
</body>