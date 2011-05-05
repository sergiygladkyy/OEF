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
    min-height: 37px;
}
.userValue { margin: 2px 6px 5px 0px; }
.userValue a { line-height: 17px !important; }

.userRow input[type="text"], .userRow input[type="file"] {
    color: #666666 !important;
    border: 1px solid #cccccc !important;
    height: 15px;
    line-height: 15px;
    padding: 1px 10px;
    -moz-border-radius: 3px;
    font-size: 12px !important;
    margin: 3px 6px 8px 0px !important;
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
    margin: 3px 6px 8px 0px !important;
    width: 140px;
}

.errors_msg {
    padding: 5px 0 0 138px;
}


.userRow input[type="checkbox"] {
    height: 15px;
    color: #666666 !important;
    border: none !important;
    padding: 0 !important;
    -moz-border-radius: 3px;
    font-size: 12px !important;
    margin: 8px 6px 8px 0px !important;
}

.userRow .onFocus {
    background-color: #FFFBC7 !important;
}

.userRow .onBlur {
    background-color: #FDFDFD !important;
}

.checkBoxText { color:#666666; }

.userRowLeft { padding: 2px 0 5px 21px; float: left; width: 117px; font-weight: bold; }
.userRowRight { float: left; width: 163px; }

.userRowRight b { padding-top:0px; display:block; }

.userRowClear { 
    background-clear:none;
    min-width: 1%;/*width:100%;*/
    float:left;
    height:29px;
}

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

.locFooter {
    background-color: #FFFFFF;
    border-top: 1px solid #D9D9D9;
    float: left;
    height: 5px;
    width: 100%;
}

.oef_content a.green_link {
    color: #93B52D !important;
    text-decoration: none;
    font-size: 15px !important;
    font-variant: small-caps;
    font-weight: bold;
    font-family: Arial !important;
    line-height: 1.2em !important;
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

  <div style="height: 450px; border: 1px solid #DDDDDD; background-color: #F8F8F8; margin-bottom: 10px;">
      <script>
        var msg = "System could not find an association between the currently logged on user ";
        msg += '<?php echo $user->getUsername() ?> and a Natural Person Catalog item. Thus, we cannot identify you as a Person/Employee. ';
        msg += 'Please, open Catalog "<a href="?uid=catalogs.NaturalPersons&actions=displayListForm">Natural Persons</a>" and set up the association';

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
    <input type="button" class="userProfileEdit" id="userEdit" onclick="openForm(1)" />
    <input type="button" class="userProfileSubmit" id="userSubmit" onclick="_submit(this)" style="display:none;" value="" />
    <input type="button" class="userProfileCancel" id="userCancel" onclick="openForm(0)" style="display:none;" />
  </div>
  <div class="userActions" id="userLink" style="display:none;">
    <a  href="">Click to change selected user profile</a>
  </div>
</div>

<div class="userRight">
  <form method="post" action="#" class="oe_custom_edit_form" id="tab1_form" enctype="multipart/form-data">
    <input type="hidden" name="<?php echo $form_prefix."[name]"; ?>" value="<?php echo $name ?>" />
    <input type="hidden" name="<?php echo $prefix."[_id]"; ?>" value="<?php echo $attrs["Person"]["_id"]?>" />
    <input type="hidden" name="tab" value="tab1" />
  
    <div id="save_tab1" style="display: none;" command="save"></div>
    
    <div id="tab1">
      <div class="userRows userFields">

        <div class="userRow">
          <div class="errors_msg">
            <ul class="catalogs_Employees_Name_errors ae_editform_field_errors" style="display: none;"><li>&nbsp;</li></ul>
          </div>
          <div class="userRowLeft">First Name:</div>
          <div class="userRowRight">
            <div class="values userValue"><?php echo $attrs["Person"]["Name"]?></div>
            <div class="fields" style="display:none;">
              <input type="text" class="input onBlur" name="<?php echo $prefix."[Name]"; ?>" value="<?php echo $attrs["Person"]["Name"]?>" onfocus="focusF(this);" onblur="blurF(this);"/>
            </div>
          </div>
        </div>

        <div class="userRow">
          <div class="errors_msg">
            <ul class="catalogs_Employees_Surname_errors ae_editform_field_errors" style="display: none;"><li>&nbsp;</li></ul>
          </div>
          <div class="userRowLeft">Surname:</div>
          <div class="userRowRight" >
            <div class="values userValue"><?php echo $attrs["Person"]["Surname"]?></div>
            <div class="fields" style="display:none;">
              <input type="text" class="input onBlur" name="<?php echo $prefix."[Surname]"; ?>" value="<?php echo $attrs["Person"]["Surname"]?>" onfocus="focusF(this);" onblur="blurF(this);"/>
            </div>
          </div>
        </div>

        <div class="userRow">
          <div class="errors_msg">
            <ul class="catalogs_Employees_Gender_errors ae_editform_field_errors" style="display: none;"><li>&nbsp;</li></ul>
          </div>
          <div class="userRowLeft">Gender:</div>
          <div class="userRowRight" >
            <div class="values userValue"><?php echo $attrs["Person"]["Gender"]?></div>
            <div class="fields" style="display:none;">
              <select name="<?php echo $prefix."[Gender]"; ?>" class="input onBlur" onblur="blurF(this);" onfocus="focusF(this);">
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
          <div class="errors_msg">
            <ul class="catalogs_Employees_Email_errors ae_editform_field_errors" style="display: none;"><li>&nbsp;</li></ul>
          </div>
          <div class="userRowLeft">E-mail:</div>
          <div class="userRowRight">
            <div class="values userValue"><?php echo $attrs["Person"]["Email"]?></div>
            <div class="fields" style="display:none;">
              <input type="text" class="input onBlur" name="<?php echo $prefix."[Email]"; ?>" value="<?php echo $attrs["Person"]["Email"]?>" onblur="blurF(this);" onfocus="focusF(this);"/>
            </div>
          </div>
        </div>
        
        <div class="userRow">
          <div class="errors_msg">
            <ul class="catalogs_Employees_Phone_errors ae_editform_field_errors" style="display: none;"><li>&nbsp;</li></ul>
          </div>
          <div class="userRowLeft">Phone:</div>
          <div class="userRowRight" >
            <div class="values userValue"><?php echo $attrs["Person"]["Phone"]?></div>
            <div class="fields" style="display:none;">
              <input type="text" class="input onBlur" name="<?php echo $prefix."[Phone]"; ?>" value="<?php echo $attrs["Person"]["Phone"]?>" onblur="blurF(this);" onfocus="focusF(this);"/>
            </div>
          </div>
        </div>

        <div class="userRow" id="photo_id" style="display:none;">
          <div class="errors_msg">
            <ul class="catalogs_Employees_Photo_errors ae_editform_field_errors" style="display: none;"><li>&nbsp;</li></ul>
          </div>
          <div class="userRowLeft">Photo:</div>
          <div class="userRowRight">
            <div class="fields"> 
              <input type="file" class="onBlur" name="aeform[catalogs][NaturalPersons][attributes][Photo]" size="10" onblur="blurF(this);" onfocus="focusF(this);"/>
            </div>
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
  </form>
  
  
    <div id="tab2" style="display:none;">
      <div class="userRows userFields">

        <div class="userRow">
          <div class="errors_msg"> </div>
          <div class="userRowLeft">Organization:</div>
          <div class="userRowRight" >
            <div class="userValue"><?php echo $attrs['Employee'] ? Constants::get('OrganizationName') : '&nbsp;' ?></div>
          </div>
        </div>

        <div class="userRow">
          <div class="errors_msg"> </div>
          <div class="userRowLeft">Unit:</div>
          <div class="userRowRight" >
            <div class="userValue"><?php echo $attrs["StaffRecord"] ? $attrs["StaffRecord"]["OrganizationalUnit"]["text"] : '&nbsp;' ?></div>
          </div>
        </div>

        <div class="userRow">
          <div class="errors_msg"> </div>
          <div class="userRowLeft">Position:</div>
          <div class="userRowRight" >
            <div class="userValue"><?php echo $attrs["StaffRecord"] ? $attrs["StaffRecord"]["OrganizationalPosition"]["text"] : '&nbsp;' ?></div>
          </div>
        </div>

        <div class="userRow">
          <div class="errors_msg"> </div>
          <div class="userRowLeft">Schedule:</div>
          <div class="userRowRight" >
            <div class="userValue"><?php echo $attrs["StaffRecord"] ? $attrs["StaffRecord"]["Schedule"]["text"] : '&nbsp;' ?></div>
          </div>
        </div>

        <div class="userRow">
          <div class="errors_msg"> </div>
          <div class="userRowLeft">Vacation Days:</div>
          <div class="userRowRight" >
            <div class="userValue"><?php echo $attrs["StaffRecord"] ? $attrs["StaffRecord"]["YearlyVacationDays"] : '&nbsp;' ?></div>
          </div>
        </div>
        
        <div class="userRowClear" style="padding-left: 13px;">
          <a href="#" id="AddLocation" class="green_link" onclick="addLocation(); return false;" style="margin-top: 6px; display: none;">
            add new location
          </a>
        </div>
        
    <?php if (!empty($attrs['Employee'])): ?>
      <form method="post" action="#" class="oe_custom_edit_form" id="tab2_form">
        <input type="hidden" name="<?php echo $form_prefix."[name]"; ?>" value="<?php echo $name ?>" />
        <input type="hidden" name="<?php echo $prefix."[_id]"; ?>" value="<?php echo $attrs["Employee"]["_id"]?>" />
        <input type="hidden" name="tab" value="tab2" />
        
        <div id="save_tab2" style="display: none;" command="save"></div>
        
        <div id="catalogs_Employees_tabulars_Locations_edit_block">
        <?php
          $i = 0;  
          $options = '';  
          
          foreach ($select['Location'] as $row)
          {
             $options .= '<option value="'.$row['value'].'">'.$row['text'].'</option>'; 
          }
          
          $params = array(
             'options'  => $options,
             'n_prefix' => $prefix.'[tabulars][Locations]',
             'links'    => $attrs['Locations']['links']['Location']
          );
          
          foreach ($attrs['Locations']['list'] as $row)
          {
             $params['i']   = $i++;
             $params['row'] = $row;
             
	         echo self::include_template('location', $params);
          }
          
          $params['i']    = '%%i%%';
          $params['row']  = array('Location' => 0, 'Comment' => '&nbsp;');
          $params['edit'] = true;
          
	      $template = self::include_template('location', $params);
	      $template = str_replace(array(chr(0), chr(9), chr(10), chr(11), chr(13)), ' ', $template);
	      $template = str_replace("'", "\'", $template);
        ?>
          <script type="text/javascript">
            ae_index['catalogs_Employees_tabulars_Locations'] = <?php echo $i - 1 ?>;
            ae_template['catalogs_Employees_tabulars_Locations'] = '<?php echo $template ?>';
          </script>
        </div>
      </form>
    <?php else: ?>
        <div class="userRow"></div>
        <div class="userRow"></div>
        <div class="userRow"></div>
        <div class="userRow"></div>
        <div class="userRow"></div>
    <?php endif; ?>
      </div>
    </div>
</div>


<div class="info_message systemmsg" style="display: none; clear: both; margin-bottom: 20px !important; width: 528px;">
  <div style="padding: 0 6px;">
    <ul class="flashMsg">
      <li>&nbsp;</li>
    </ul>
  </div>
</div>

<script type="text/javascript">
var _ACTIVE_TAB = '<?php echo $tab ?>';

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
	if (_ACTIVE_TAB != 'tab1')
		show(_ACTIVE_TAB);
});

/**
 * Open tab
 *
 * @param string num - tab prefix
 * @return void
 */
function show(num)
{
	hideMessages();
	hideFieldErrors('ae_editform_field');
	
	openForm(0);
    
    _ACTIVE_TAB = num;
    
    var isHired = <?php echo (empty($attrs["StaffRecord"]) ? 'false' : 'true') ?>;

    if (num == 'tab2')
    {
    	if (isHired)
    	{
        	var msg = "The information you see is available only since it is contributed by the following document: " +
        		'<?php echo '<a target="_blank" href="#" onclick="_dispalyEF(this, \\\'documents\\\', \\\''.$doc['type'].'\\\', '.$doc['id'].'); return false;">'.$doc['desc'].'</a>' ?>' +
        		" If you want to update the information you may need to re-submit the document." +
            	" For making so, click the link above, perform \"Clear Posting\", then update the data and perform \"Post\"." +
            	" Attention, you must have the sufficient access rights for this"
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

    num = num == 'tab1' ? 'tab2' : 'tab1';
    
    document.getElementById(num).style.display='none';

    document.getElementById("h"+num).style.display='none';
}

/**
 * Open current form
 *
 * @param boolean mode
 * @return void
 */
function openForm(mode)
{
	var selector, fid = getFormId();

	if (!fid) return;
	
	selector = '#' + fid;
	
	if (mode)
	{
        var disp1 = 'none';
        var disp2 = 'block';
	}
	else
	{
		processCancel();
		
		var disp1 = 'block';
        var disp2 = 'none';
	}
	
	jQuery(selector + ' .values, ' + selector + ' .action, #AddLocation, #userEdit').css('display', disp1);
	jQuery(selector + ' .fields, ' + selector + ' .action, #AddLocation, #photo_id, #userSubmit, #userCancel').css('display', disp2);
}

/**
 * Process Cancel event
 *
 * @return void
 */
function processCancel()
{
	hideMessages(['catalogs_Employees']);
	hideFieldErrors('ae_editform_field');
	
	var selector, fid = getFormId();

	if (!fid) return;

	selector = '#' + fid;

	jQuery(selector + ' .new').remove();

	updateFormValues(selector, true);
}

/**
 * Copied values from item form to edit form if mode == true, and
 * from edit form to item form if mode == false
 *
 * @param string  selector
 * @param boolean mode
 * @return void
 */
function updateFormValues(selector, mode)
{
	if (mode) // from item to edit
	{
		jQuery(selector + ' .userRowRight').each(function(index)
		{
			var input = jQuery(this).find('.input').get(0);

			if (input)
			{
				switch (input.nodeName)
				{
					case 'INPUT':
						if (input.type == 'text')
						{
							input.value = jQuery(this).find('.values').text().match(/[\S]{1}.*[\S]{1}/i, '');
						}
						break;
						
					case 'SELECT':
						jQuery(input).find('option[current="true"]').attr('selected', true);
						break;
				}
			}
		});
	}
	else // from edit to item
	{
		jQuery(selector + ' .userRowRight').each(function(index)
		{
			var input = jQuery(this).find('.input').get(0);

			if (input)
			{
				switch (input.nodeName)
				{
					case 'INPUT':
						if (input.type == 'text')
						{
							 jQuery(this).find('.values').text(input.value);
						}
						break;
						
					case 'SELECT':
						var value = jQuery(input).find('option:selected').text();
						jQuery(this).find('.values').text(value);
						break;
				}
			}
		});
	}
}

/**
 * Return current form id
 * 
 * @return string or false
 */
function getFormId()
{
	if (!document.getElementById(_ACTIVE_TAB + '_form')) return false;
	
	return _ACTIVE_TAB + '_form';
}

/**
 * Process onFocus event
 *
 * @param DOMElement it
 * @return void
 */
function focusF(it) {
	jQuery(it).removeClass('onBlur').addClass('onFocus');
}

/**
 * Process onBlur event
 *
 * @param DOMElement it
 * @return void
 */
function blurF(it) {
	jQuery(it).removeClass('onFocus').addClass('onBlur');
}

/**
 * Add new location
 * 
 * @return void
 */
function addLocation()
{
	addTabularSectionItem('catalogs_Employees_tabulars_Locations', 'catalogs_Employees_tabulars_Locations');
}

/**
 * Delete location
 *
 * @return void
 */
function deleteLocation(elem)
{
	var item = jQuery(elem).parents('.tabular_item').get(0);

	if (jQuery(item).find('input.pkey').size() == 0)
	{
		jQuery(item).remove();
	}
	else
	{
		jQuery(item).css('display', 'none');
	}
}

/**
 * Submit form
 *
 * @param DOMElement elem
 * @return void
 */
function _submit(elem)
{
	var button = document.getElementById('save_' + _ACTIVE_TAB);

	processFormCommand(button);
}

/**
 * Add listeners
 */
Context.addListener('tab2_form_before_submit', onBeforeSubmitTab2);
Context.addListener('catalogs_Employees_end_process', onEmployeesEndProcess);
Context.addListener('catalogs_Employees_tabulars_Locations_end_process', onEmployeesLocationsEndProcess);

/**
 * Called before submit tab2_form
 */
function onBeforeSubmitTab2(params)
{
	jQuery('#tab2_form').find(".tabular_item:hidden .tabular_col").remove();
	jQuery('#tab2_form').find('.tabular_item:hidden').each(function(index) {
		var hidden = jQuery(this).find('input[type=hidden]').get(0);
		var name = hidden.getAttribute('name');
		name = name.replace(/\[[^\]\[]+\]\[[^\[\]]+\]$/gi, '[deleted][]');
		hidden.setAttribute('name', name);
	});

	//Context.setLastStatus(false);
}

/**
 * Called after standard process catalog_Employees item
 */
function onEmployeesEndProcess(params)
{
	if (Context.getLastStatus())
    {
	    var systemmsg = jQuery('#<?php echo $tag_id?> .catalogs_Employees_message').get(0).cloneNode(true);

    	displayCustomForm('catalogs.Employees', 'UserProfile', {person: '<?php echo $attrs["Person"]["_id"]?>', tab: _ACTIVE_TAB}, '<?php echo $tag_id?>');

    	show(_ACTIVE_TAB);
    	
    	jQuery('#<?php echo $tag_id?> .catalogs_Employees_message').replaceWith(systemmsg);
    }
}

/**
 * Called after standard process catalog_Employees_tabulars_Locations item
 */
function onEmployeesLocationsEndProcess(params)
{
	if (!params.status) return;

	var tid = '#catalogs_Employees_tabulars_Locations_' + params.index + '_item';

	updateFormValues(tid, false);
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
