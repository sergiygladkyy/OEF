<!--<ul class="{{ class..'_'..field..'_errors ae_editform_field_errors' }}" style="display: none;"><li>&nbsp;</li></ul>-->
<div class="catalogs_Employees_message systemmsg" style="display: none;">
  <div class="inner">
    <ul class="flashMsg">
      <li>&nbsp;</li>
    </ul>
  </div>
</div>
<?php $prefix = $form_prefix.'[attributes]' ?>
<h5 id="labelWarning" style="display:none;"> Login Records </h5>


<div class="userTabs">
<div id="htab1">
    <a href="#"  class="active"  onclick="show('tab1')" ><span class="icon info"></span><b>Personal info</b></a>
    <a href="#" onclick="show('tab2')"><span class="icon blog"></span><b>Organization</b></a>

</div>
<div id="htab2" style="display:none;">
    <a href="#" onclick="show('tab1')" ><span class="icon info"></span><b>Personal info</b></a>
    <a href="#" class="active" onclick="show('tab2')" ><span class="icon proj"></span><b>Organization</b></a>
</div>
</div>

<form method="post" action="#" class="oe_custom_edit_form" id="{{ class..'_item' }}" enctype="multipart/form-data">
<input type="hidden" name="<?php echo $form_prefix."[name]"; ?>" value="<?php echo $name ?>" />

<div class="userLeft">
    <div class="userPhoto">
        <?php if (isset($attrs["Person"]["Photo"]) ): ?>
           <a href="<?php echo $uploadDir.$attrs["Person"]["Photo"] ?>"><img src="<?php echo $uploadDir.'preview'.$attrs["Person"]["Photo"] ?>" /></a>
        <?php else: ?>
           <center>No image</center>
        <?php endif;?>
    </div>
    <div class="userPic"></div>
  
</div>

<div class="userRight">
<div id="tab1">
    <div class="userRows userFields">
    

   <div class="userRow">
        <div class="userRowLeft">First Name:</div>
        <div class="userRowRight" >
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
               <?php if ($attrs["Person"]["Gender"]=='Male'): ?>

                    <option value="male" selected="1">Male</option>
                    <option value="female" >Female</option>

                <?php else: ?>
                    <option value="male" >Male</option>
                    <option value="female" selected="1">Female</option>
                <?php endif;?>
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
        <div class="userRowLeft">Path:</div>
        <div class="userRowRight" >
          <input name="<?php echo $prefix."[Photo]"; ?>" type="file" onfocus="focusF(this);" onblur="blurF(this);" />
        </div>
    </div>

    </div>
    <input type="button" id="userEdit1" class="userProfileEdit" onclick="openForm(1)" />
    <input type="button" id="userSubmit1" onclick="_submit(this)" class="userProfileSubmit" style="display:none;" value="" command="save"/>
    <input type="button" id="userCancel1" onclick="openForm(0)" style="display:none;" class="userProfileCancel" />

</div>

    
<div id="tab2" style="display:none;">
    <div class="userRows userFields">
    <div class="userRow">
        <div class="userRowLeft">Organization:</div>
        <div class="userRowRight" >
          <div id="left6" class="userValue"><?php echo $attrs["StaffRecord"]["Employee"]["text"]?></div>
        </div>
    </div>
    

    
    <div class="userRow">
        <div class="userRowLeft">Unit:</div>
        <div class="userRowRight" >
          <div id="left7" class="userValue"><?php echo $attrs["StaffRecord"]["OrganizationalUnit"]["text"]?></div>
        </div>
    </div>



    <div class="userRow">
        <div class="userRowLeft">Position:</div>
        <div class="userRowRight" >
          <div id="left8" class="userValue"><?php echo $attrs["StaffRecord"]["OrganizationalPosition"]["text"]?></div>
        </div>
    </div>


    <div class="userRow">
        <div class="userRowLeft">Schedule:</div>
        <div class="userRowRight" >
          <div id="left9" class="userValue"><?php echo $attrs["StaffRecord"]["Schedule"]["text"]?></div>
        </div>
    </div>


    <div class="userRow">
        <div class="userRowLeft">YearlyVacationDays:</div>
        <div class="userRowRight" >
          <div id="left10" class="userValue"><?php echo $attrs["StaffRecord"]["YearlyVacationDays"]?></div>
        </div>
    </div>
    </div>

    <input type="button" id="userEdit2" class="userProfileEdit" onclick="openForm(1)" />
    <input type="button" id="userSubmit2" onclick="_submit(this)" class="userProfileSubmit" style="display:none;" value="" command="save"/>
    <input type="button" id="userCancel2" onclick="openForm(0)" style="display:none;" class="userProfileCancel" />

</div>
    
</div>

<!--<div class="ae_submit" >
      <input type="button" value="Save and Close" class="ae_command" command="save_and_close" >
      <input type="button" value="Save" class="ae_command" command="save" />
      <input type="button" value="Close" class="ae_command" command="cancel" />
</div>-->
<input type="hidden" value="<?php echo $attrs["Person"]["_id"]?>" name="<?php echo $prefix."[_id]"; ?>" />
</form>
<script type="text/javascript">
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
    for(i=1;i<3;i++)
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
        document.getElementById('labelWarning').style.display='block';
        document.getElementById('photo_id').style.display='block';
    }
    else
    {
        document.getElementById('labelWarning').style.display='none';
        document.getElementById('photo_id').style.display='none';
    }
}

function show(num){
    if(num=='tab1')
        document.getElementById('labelWarning').innerHTML ="Changind catalog Natural Person"
    else
        document.getElementById('labelWarning').innerHTML ="To change data you need post document RecruitingOrder"
    document.getElementById(num).style.display='block';

    document.getElementById("h"+num).style.display='block';
    num=num=='tab1'?'tab2':'tab1';
    document.getElementById(num).style.display='none';

    document.getElementById("h"+num).style.display='none';
}
function _submit(elem)
{
    //alert(elem.nodeName);
    processFormCommand(elem);
    //document.userform.submit();
    openForm(0);
}
</script>
<!--<?php echo'<pre>'.print_r($attrs, true).'</pre>'?>-->