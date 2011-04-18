<style type="text/css">

/************************************* UserProfile ********************************************/
.userTabs { padding:0 5px; height:28px; border-bottom:1px solid #d2d2d2; }
img { border:0; }
.userTabs a, .userTabs a:visited { text-decoration:none; border-top:1px solid #d2d2d2; border-right:1px solid #d2d2d2; border-left:1px solid #d2d2d2; float:left; height:15px; position:relative; margin-right:4px; padding:6px 8px; margin-bottom:-1px; -moz-border-radius-topleft:3px; -moz-border-radius-topright:3px; line-height:1em;}
.userTabs a:hover, .userTabs a.active { border-bottom:1px solid #ffffff; }
.userTabs span.icon { background-image:url(/skins/aconawellpro/images/userTabs.png); float:left; height:16px; width:16px; margin-right:3px; }
.userTabs span.info { background-position:0 0; }
.userTabs span.blog { background-position:-16px 0; }
.userTabs span.proj { background-position:-32px 0; }
.userTabs b { font-size:12px;  text-decoration:none; }
.userTabs b span { color:#646464; padding-left:5px; }

.userLeft { width:220px; margin: 20px 0; float: left; }
.userPhoto {  width:200px; overflow:hidden; /*-moz-border-radius:3px;*/ text-align: center; border: 1px solid #d2d2d2; padding:8px;}
.userPhoto a.new, .userPhoto a:hover.new, .userPhoto a:visited.new { text-decoration: none !important; border: 0 none !important; }
.userPhoto img { max-width:200px; max-height:200px; }

.userPic { width:153px; height:185px; float:left; margin-top:1px; }

.userRight { float:left; margin: 20px 0px 20px 8px;  }

.userDesc { margin: 20px 0 10px 20px; color: #5f5f5f; text-align: left; font-weight: 600;}

.userActions { float: left; width: 190px; overflow:hidden; padding-top: 10px;}

.userRows { width:302px; float:left; }

.userRow {
    float: left;
    background-color: #F6F3FA;
    font-size: 12px;
    line-height: 17px;
    color: #494949;
    font-family: Arial;
    border-top: 1px solid #D2D2D2;
    width: 100%;
    height: 37px;
}
.userValue { margin: 7px 6px 5px 0px; }
.userValue a { line-height: 17px !important; }

.userRow input[type="text"], .userRow input[type="file"] {
    color: #666666 !important;
    border: 1px solid #cccccc !important;
    height: 15px;
    line-height: 15px;
    padding: 1px 10px;
    -moz-border-radius: 3px;
    font-size: 12px !important;
    margin: 8px 6px 8px 0px !important;
    width: 133px;
    background-color: #FDFDFD;
}

.userRow input[type="file"] {
    height: 20px;
    width: 154px;
}

.userRow select {
    color: #666666 !important;
    border: 1px solid #cccccc !important;
    padding: 1px 0px 1px 10px;
    -moz-border-radius: 3px;
    font-size: 12px !important;
    margin: 8px 6px 8px 0px !important;
    width: 140px;
    background-color: #FDFDFD;
}

.userRow input[type="checkbox"] {
    height: 15px;
    color: #666666 !important;
    border: none !important;
    padding: 0 !important;
    -moz-border-radius: 3px;
    font-size: 12px !important;
    margin: 8px 6px 8px 0px !important;
    background-color: #FDFDFD;
}

.checkBoxText { color:#666666; }

.userRowLeft { padding: 7px 0 5px 21px; float: left; width: 117px; font-weight: bold; }
.userRowRight { float: left; width: 163px; }

.userRowRight b { padding-top:0px; display:block; }

.userRowClear { background-clear:none; width:100%; float:left; height:29px;  }

.userRowTop .userRowLeft, .userRowTop .userRowLeft { border-top:1px solid #d2d2d2; }

input.userProfileEdit { cursor:pointer; width:105px; height:21px; float:left; border: none ! important; background:url(/skins/aconawellpro/images/userProfileEdit.png); margin-left:20px; margin-bottom:7px; }
input.userProfileSubmit {cursor:pointer; width:109px; height:21px; float:left; border: none ! important; background:url(/skins/aconawellpro/images/userProfileSubmit.png); margin-left:20px; margin-bottom:7px; }
input.userProfileCancel {cursor:pointer; width:66px; height:21px; float:left; border: none ! important; background:url(/skins/aconawellpro/images/userProfileCancel.png); margin-left:20px; }
input.userBusinessCard {
    position:relative;
    left:50%;
    margin: 0px 0px 19px -75px;
    cursor:pointer;
    width:145px !important;
    height:21px !important;
    border: none ! important;
    background:url(../images/userBusinessCard.png);
}
</style>
<!--<ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>-->

<div class="catalogs_Employees_message systemmsg" style="display: none;">
  <div class="inner">
    <ul class="flashMsg">
      <li>&nbsp;</li>
    </ul>
  </div>
</div>

<?php if (empty($attrs['Employee']) && $isCurrentEmployee): ?>

  <div style="height: 250px; border: 1px solid #DDDDDD; background-color: #F8F8F8; margin-bottom: 10px;">
      <script>
        var msg = "The System couldn't find an association between the currently logged user ";
        msg += '<?php echo $user->getUsername() ?> and a Natural Person catalog item. So, we cannot identify which Person/Employee you are. ';
        msg += 'Please, open the catalog "<a href="?uid=catalogs.NaturalPersons&actions=displayListForm">Natural Persons</a>" and set up this association';

        displayMessage('catalogs_Employees', msg, 2);
      </script>
  </div>

<?php else: ?>
  <?php $prefix = $form_prefix.'[attributes]' ?>
  <?php if (empty($attrs['Employee'])): ?>
    <?php $_msg = 'This person not employed. You can employ him/her by submitting a "<a href="?uid=documents.RecruitingOrder&actions=displayListForm">RecruitingOrder</a>" document.' ?>
    <script>
      displayMessage('info', '<?php echo $_msg ?>', 2);
    </script>
  <?php endif; ?>
<div class="userTabs">
  <div id="htab1">

    <a href="#"  class="active"  onclick="show('tab1'); return false;" style="color: #93B52D !important;" ><b>Personal info</b></a>
    <a href="#" onclick="show('tab2'); return false;" style="color: #93B52D !important;" ><b>Organization</b></a>
  </div>
  <div id="htab2" style="display:none;">
    <a href="#" onclick="show('tab1'); return false;" style="color: #93B52D !important;" ><b>Personal info</b></a>
    <a href="#" class="active" onclick="show('tab2'); return false;" style="color: #93B52D !important;" ><b>Organization</b></a>

  </div>
</div>

<form method="post" action="#" class="oe_custom_edit_form" id="catalogs_Employees_item" enctype="multipart/form-data">
  <input type="hidden" name="<?php echo $form_prefix."[name]"; ?>" value="<?php echo $name ?>" />
  <input type="hidden" value="<?php echo $attrs["Person"]["_id"]?>" name="<?php echo $prefix."[_id]"; ?>" />

  <div class="userLeft">
    <div class="userPhoto">
      <?php if (!empty($attrs["Person"]["Photo"]) ): ?>
        <a href="<?php echo $uploadDir.$attrs["Person"]["Photo"] ?>"><img src="<?php echo $uploadDir.'preview'.$attrs["Person"]["Photo"] ?>" /></a>
      <?php else: ?>
        <img src="/skins/common/icons/mrab_no_profile_image.png" alt="Photo" />
      <?php endif;?>
    </div>
    <div class="userDesc"><?php echo $attrs['Person']['Name'].' '.$attrs['Person']['Surname'] ?></div>
    <div class="userActions" id="userActions" >
        <input type="button" id="userEdit1" class="userProfileEdit" onclick="openForm(1)" />
        <input type="button" id="userSubmit1" onclick="_submit(this)" class="userProfileSubmit" style="display:none;" value="" command="save"/>
        <input type="button" id="userCancel1" onclick="openForm(0)" style="display:none;" class="userProfileCancel" />
     </div>
      <div class="userActions" id="userLink" style="display:none;"  >
        <a  href="">Click to change selected user profile</a>
     </div>
  </div>

  <div class="userRight">
    <div id="tab1">
      <div class="userRows userFields">

        <div class="userRow">
          <div class="userRowLeft">First Name:</div>
          <div class="userRowRight">
            <div id="left1" class="userValue"><?php echo $attrs["Person"]["Name"]?></div>
            <div id="right1" style="display:none;"><input type="text" onfocus="focusF(this);" onblur="blurF(this);" value="<?php echo $attrs["Person"]["Name"]?>" name="<?php echo $prefix."[Name]"; ?>"/></div>
          </div>
        </div>

        <div class="userRow">
          <div class="userRowLeft">Surname:</div>
          <div class="userRowRight" >
            <div id="left2" class="userValue"><?php echo $attrs["Person"]["Surname"]?></div>
            <div id="right2" style="display:none;"><input type="text"  onfocus="focusF(this);" onblur="blurF(this);" value="<?php echo $attrs["Person"]["Surname"]?>" name="<?php echo $prefix."[Surname]"; ?>"/></div>
          </div>
        </div>

        <div class="userRow">
          <div class="userRowLeft">Birthday:</div>
          <div class="userRowRight" >
            <div id="left3" class="userValue"><?php echo $attrs["Person"]["Birthday"]?></div>
            <div id="right3" style="display:none;"><input type="text"  onfocus="focusF(this);" onblur="blurF(this);" value="<?php echo $attrs["Person"]["Birthday"]?>" name="<?php echo $prefix."[Birthday]"; ?>"/></div>
          </div>
        </div>

        <div class="userRow">
          <div class="userRowLeft">Gender:</div>
          <div class="userRowRight" >
            <div id="left4" class="userValue"><?php echo $attrs["Person"]["Gender"]?></div>
            <div id="right4" style="display:none;">
              <select name="<?php echo $prefix."[Gender]"; ?>">
                <?php foreach ($gender as $value => $text): ?>
                  <?php if ($attrs["Person"]["Gender"] == $text): ?>
                    <option value="<?php echo $value ?>" selected><?php echo $text ?></option>
                  <?php else: ?>
                    <option value="<?php echo $value ?>"><?php echo $text ?></option>
                  <?php endif;?>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
        </div>

        <div class="userRow">
          <div class="userRowLeft">Phone:</div>
          <div class="userRowRight" >
            <div id="left5" class="userValue"><?php echo $attrs["Person"]["Phone"]?></div>
            <div id="right5" style="display:none;"><input type="text"  onfocus="focusF(this);"  value="<?php echo $attrs["Person"]["Phone"]?>" name="<?php echo $prefix."[Phone]"; ?>"/></div>
          </div>
        </div>

        <div class="userRow" id="photo_id" style="display:none;">
          <div class="userRowLeft">Photo:</div>
          <div class="userRowRight" >
            <input name="aeform[catalogs][NaturalPersons][attributes][Photo]" type="file" size="10" onfocus="focusF(this);" onblur="blurF(this);" />
          </div>
        </div>
        <div class="userRowClear">   </div>
        <div class="userRow"></div>
        <div class="userRow"></div>
        <div class="userRow"></div>
        <div class="userRow"></div>
        <div class="userRow"></div>
      </div>



    </div>
    <div id="tab2" style="display:none;">
      <div class="userRows userFields">

        <div class="userRow">
          <div class="userRowLeft">Organization:</div>
          <div class="userRowRight" >
            <div id="left6" class="userValue"><?php echo $attrs['Employee'] ? Constants::get('OrganizationName') : '&nbsp;' ?></div>
          </div>
        </div>

        <div class="userRow">
          <div class="userRowLeft">Unit:</div>
          <div class="userRowRight" >
            <div id="left7" class="userValue"><?php echo $attrs["StaffRecord"] ? $attrs["StaffRecord"]["OrganizationalUnit"]["text"] : '&nbsp;' ?></div>
          </div>
        </div>

        <div class="userRow">
          <div class="userRowLeft">Position:</div>
          <div class="userRowRight" >
            <div id="left8" class="userValue"><?php echo $attrs["StaffRecord"] ? $attrs["StaffRecord"]["OrganizationalPosition"]["text"] : '&nbsp;' ?></div>
          </div>
        </div>

        <div class="userRow">
          <div class="userRowLeft">Schedule:</div>
          <div class="userRowRight" >
            <div id="left9" class="userValue"><?php echo $attrs["StaffRecord"] ? $attrs["StaffRecord"]["Schedule"]["text"] : '&nbsp;' ?></div>
          </div>
        </div>

        <div class="userRow">
          <div class="userRowLeft">Vacation Days:</div>
          <div class="userRowRight" >
            <div id="left10" class="userValue"><?php echo $attrs["StaffRecord"] ? $attrs["StaffRecord"]["YearlyVacationDays"] : '&nbsp;' ?></div>
          </div>
        </div>
        <div class="userRowClear">   </div>
        <div class="userRow"></div>
        <div class="userRow"></div>
        <div class="userRow"></div>
        <div class="userRow"></div>
        <div class="userRow"></div>
     
     </div>
   </div>
  </div>

</form>

<div class="info_message systemmsg" style="display: none; clear: both; margin-bottom: 20px !important; width: 528px;">
  <div style="padding: 0 6px;">
    <ul class="flashMsg">
      <li>&nbsp;</li>
    </ul>
  </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
var divElement = document.getElementById("comments-section");
if (divElement != null)
    divElement.style.display = 'none';
divElement = document.getElementById("gallery-section");
if (divElement != null)
    divElement.style.display = 'none';
divElement = document.getElementById("file-section");
if (divElement != null)
    divElement.style.display = 'none';
divElement = document.getElementById("pageInfo");
if (divElement != null)
    divElement.style.display = 'none';
});
function focusF(it) {
	it.style.background='#fffbc7';
}
function blurF(it) {
	it.style.background='#ffffff';
}
function openForm(mode) {
    var count=5;
    for(i=1; i<=count; i++)
    {
        if(mode)
            {
                 document.getElementById('left'+i).style.display='none';
                 document.getElementById('right'+i).style.display='block';

            }
        else
            {
                 document.getElementById('left'+i).style.display='block';
                 document.getElementById('right'+i).style.display='none';
            }
    }
    for(i=1;i<2;i++)
    {
        if (mode) {
            document.getElementById('userEdit'+i).style.display='none';
            document.getElementById('userSubmit'+i).style.display='block';
            document.getElementById('userCancel'+i).style.display='block';

        }
        else
        {
            document.getElementById('userEdit'+i).style.display='block';
            document.getElementById('userSubmit'+i).style.display='none';
            document.getElementById('userCancel'+i).style.display='none';
        }
    }
    if (mode)
    {
        document.getElementById('photo_id').style.display='block';
    }
    else
    {
        document.getElementById('photo_id').style.display='none';
    }
}

function show(num){
    hideMessages();

    var isHired = <?php echo (empty($attrs["StaffRecord"]) ? 'false' : 'true') ?>;

    if (num == 'tab2')
    {
    	if (isHired)
    	{
        	var msg = "The information you see is read only since it is contributed by the following document: " +
        		'<?php echo '<a target="_blank" href="#" onclick="_dispalyEF(this, \\\'documents\\\', \\\''.$doc['type'].'\\\', '.$doc['id'].'); return false;">'.$doc['desc'].'</a>' ?>' +
        		" If you want to update the information you may need to re-submit the document." +
            	" For making so, click the link above, perform \"Clear Posting\", then update the data and perform \"Post\"." +
            	" Attention, that you must have the sufficient access rights for this"
            ;
        }
    	else
    	{
        	var msg = 'The section is emply because this person is not currently employed. ' +
        		'You can employ him/her by submitting a "<a href="?uid=documents.RecruitingOrder&actions=displayListForm">Recruitment Order</a>" document';
    	}

        displayMessage('info', msg, 2);
    }
  <?php if (empty($attrs['Employee'])): ?>
    else if (num == 'tab1')
    {  
      displayMessage('info', '<?php echo $_msg ?>', 2);
    }
  <?php endif; ?>
    

    document.getElementById(num).style.display='block';

    document.getElementById("h"+num).style.display='block';
    num=num=='tab1'?'tab2':'tab1';
    document.getElementById(num).style.display='none';

    document.getElementById("h"+num).style.display='none';
}

function _submit(elem)
{
	hideMessages();

    processFormCommand(elem);
}

Context.addListener('catalogs_Employees_end_process', onEndProcess);

function onEndProcess(params)
{
	if (Context.getLastStatus())
    {
	    var systemmsg = jQuery('#<?php echo $tag_id?> .catalogs_Employees_message').get(0).cloneNode(true);

    	displayCustomForm('catalogs.Employees', 'UserProfile', {employee: '<?php echo $attrs["Person"]["_id"]?>'}, '<?php echo $tag_id?>');

    	jQuery('#<?php echo $tag_id?> .catalogs_Employees_message').replaceWith(systemmsg);
    }
}

/**
 * Action edit item
 * 
 * @param DOMElements element
 * @param string kind - entity kind
 * @param string type - entity type
 * @param int    id   - entity id
 * 
 * @return boolean
 */
function _dispalyEF(element, kind, type, id)
{ 
	if (!id || id < 1) return false;
	
	var popup  = new oefPopup(kind, type);
    var target = element.getAttribute('target');
    
    if (target) popup.setTarget(target);
    
	return popup.displayWindow('displayEditForm', {id: id});
}
</script>
<?php endif;?>
