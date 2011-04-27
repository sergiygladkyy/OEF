<?php
   $class = $kind.'_'.$type;
   $aprefix = $attr_prefix.'[attributes][attributes]';
   $tprefix = $attr_prefix.'[attributes][tabulars][TimeRecords]';
?>

<form method="post" action="#" class="oe_custom_edit_form" id="<?php echo $class.'_item' ?>">
  <input type="hidden" name="<?php echo $attr_prefix.'[name]' ?>" value="<?php echo $name ?>" />
  <?php if (!empty($attrs['_id'])): ?>
  <input type="hidden" name="<?php echo $aprefix.'[_id]' ?>" value="<?php echo $attrs['_id'] ?>" />
  <?php endif; ?>
  <table>
  <tbody>
    <tr>
      <td colspan="4"><h3>Time Card</h3></td>
    </tr>
    <tr id="<?php echo $class.'_post_flag' ?>" style="<?php echo $attrs['_id'] > 0 ? '' : 'display: none;' ?>">
      <td class="<?php echo $class.'_name ae_editform_field_name oe_period' ?>">Posted:</td>
      <td class="<?php echo $class.'_value ae_editform_field_value' ?>" colspan="3">
        <div class="<?php echo $attrs['_post'] > 0 ? 'ae_field_posted' : 'ae_field_not_posted' ?>">
          <span class="ae_field_posted_text" style="<?php echo $attrs['_post'] > 0 ? 'display: block;' : 'display: none;' ?>">This document is posted.</span>
          <span class="ae_field_not_posted_text" style="<?php echo $attrs['_post'] > 0 ? 'display: none;' : 'display: block;' ?>">This document is not posted.</span>
        </div>
      </td>
    </tr>
    <tr>
      <td class="oe_period oe_attribute">Period:</td>
      <td class="oe_week oe_attribute">
        <ul class="<?php echo $class.'_Period_errors ae_editform_field_errors' ?>" style="display: none;"><li>&nbsp;</li></ul>
        <select name="<?php echo $attr_prefix.'[attributes][week]' ?>" onChange="onChange(this);">
          <option value="0" selected>&nbsp;</option>
          <?php
            foreach ($periods as $opt)
            {
               $option = '<option value="'.$opt['value'].'"';
               if ($period == $opt['value']) $option .= ' selected';
               if ($opt['disabled'])         $option .= ' disabled';
               $option .= '>'.$opt['text'].'</option>';
               
               echo $option;
            }
          ?>
        </select>
      </td>
      <td class="oe_user oe_attribute">User:</td>
      <td class="oe_employee oe_attribute">
        <ul class="<?php echo $class.'_Employee_errors ae_editform_field_errors' ?>" style="display: none;"><li>&nbsp;</li></ul>
        <select name="<?php echo $aprefix.'[Employee]' ?>" onChange="onChange(this);">
          <option value="0" selected>&nbsp;</option>
          <?php foreach ($employees as $opt): ?>
            <?php if ($employee == $opt['value']): ?>
          <option value="<?php echo $opt['value'] ?>" selected><?php echo $opt['text'] ?></option>
            <?php else: ?>
          <option value="<?php echo $opt['value'] ?>"><?php echo $opt['text'] ?></option>
            <?php endif;?>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td colspan="4">
        <div class="infomsg">
          Input sample: ‘7.5’, what means 7 hrs 30 min
        </div>
        <div class="oe_time_card" style="width: 702px;">
          <table>
          <tr>
            <th>Project</th>
            <th>SubProject</th>
            <th>Mon</th>
            <th>Tue</th>
            <th>Wed</th>
            <th>Thr</th>
            <th>Fri</th>
            <th>Sat</th>
            <th>Sun</th>
          </tr>
          <?php $i = 0 ?>
          <?php foreach ($card as $project => $_card): ?>
            <?php foreach ($_card as $subproject => $vals): ?>
          <tr>
            <td class="oe_project">
              <ul class="<?php echo $class.'_tabulars_TimeRecords_'.$i.'_Project_errors ae_editform_field_errors' ?>" style="display: none;"><li>&nbsp;</li></ul>
              <?php echo $links['Project'][$project]['text'] ?>
            </td>
            <td class="oe_subproject">
              <ul class="<?php echo $class.'_tabulars_TimeRecords_'.$i.'_SubProject_errors ae_editform_field_errors' ?>" style="display: none;"><li>&nbsp;</li></ul>
              <?php echo $links['SubProject'][$subproject]['text'] ?>
            </td>
              <?php for ($j = 0; $j < 7; $j++): ?>
                <?php $cprefix = $tprefix.'['.(7*$i + $j).']' ?>
            <td class="oe_hours" id="<?php echo $class.'_tabulars_TimeRecords_'.(7*$i + $j).'_item' ?>">
              <ul class="<?php echo $class.'_tabulars_TimeRecords_'.(7*$i + $j).'_Hours_errors ae_editform_field_errors' ?>" style="display: none;"><li>&nbsp;</li></ul>
                <?php if ($vals[$j]['Planed']): ?>
              <div class="oe_planed" title="your planned time">
                  <?php printf("%01.1f", $vals[$j]['Planed']) ?>
              </div>
                <?php endif; ?>
              <div class="oe_attribute" title="your real time">
                <span class="oe_text"><?php printf("%01.1f", $vals[$j]['Hours']) ?></span>
                <input class="oe_value" type="text" name="<?php echo $cprefix.'[Hours]' ?>" value="<?php printf("%01.1f", $vals[$j]['Hours']) ?>" style="display: none;">
                  <?php if (isset($vals[$j]['_id'])): ?>
                <input type="hidden" name="<?php echo $cprefix.'[_id]' ?>" value="<?php echo $vals[$j]['_id'] ?>" />
                  <?php endif; ?>
                <input type="hidden" name="<?php echo $cprefix.'[Project]' ?>" value="<?php echo $project ?>" />
                <input type="hidden" name="<?php echo $cprefix.'[SubProject]' ?>" value="<?php echo $subproject ?>" />
              </div>
            </td>
              <?php endfor; ?>
          </tr>
              <?php $i++ ?>
            <?php endforeach; ?>
          <?php endforeach; ?>
          </table>
        </div>
      </td>
    </tr>
    <tr>
      <td class="oe_submit" colspan="4">
        <input type="button" value="Close" class="ae_command" command="cancel" />&nbsp;
        <input type="button" value="Save" class="ae_command" command="save" />
      </td>
    </tr>
  </tbody>
  </table>
</form>

<script type="text/javascript">
	jQuery('<?php echo '#'.$class.'_item .oe_hours' ?>').click(function(event)
	{
		event.stopPropagation ? event.stopPropagation() : event.cancelBubble = true;
		
		var element = event.target || event.srcElement;
		
		if (element.nodeName != 'TD') element = element.parentNode; 

		if (jQuery(element).find('.oe_value').attr('disabled')) return false;
		
		jQuery(element).find('.oe_text').css('display', 'none');
		jQuery(element).find('.oe_value').css('display', 'block').focus();
	}
	).find('.oe_value').blur(function(event)
	{
		event.stopPropagation ? event.stopPropagation() : event.cancelBubble = true;
		
		var element = jQuery(event.target || event.srcElement).parent().get(0);
		var input   = jQuery(element).find('.oe_value').css('display', 'none').get(0);
		
		jQuery(element).find('.oe_text').text(input.value).css('display', 'block');
	});

	function onChange(element)
	{
		appInactive();
		
		var params = {};
		
     <?php if (!empty($attrs['_id'])): ?>
        if (!confirm('TimeCard will be cleared. Continue?'))
		{
			jQuery(element).find('option[current=true]').attr('selected', 'selected');
			appActive();
			return;
		}
        params.cleared  = 1;
		params.document = <?php echo $attrs['_id'] ?>;
     <?php endif; ?>

		appDisplayLoader(true);
     
		if (element.getAttribute('name') == 'aeform[documents][TimeCard][attributes][week]')
		{
			params.Period = element.options[element.selectedIndex].value;
			params.Employee = jQuery('<?php echo '#'.$class.'_item .oe_employee' ?>').
				find('select option:selected').attr('value');
		}
		else
		{
			params.Employee = element.options[element.selectedIndex].value;
			params.Period   = jQuery('<?php echo '#'.$class.'_item .oe_week' ?>').
				find('select option:selected').attr('value');
		}
		
		displayCustomForm('documents.TimeCard', 'TimeCard', params, 'oef_custom_time_card_form');

		jQuery('<?php echo '#'.$class.'_item .ae_command' ?>').each(function(index) {
	    	jQuery(this).click(function() { 
	    		processFormCommand(this);
	    	});
	    });
	    
		appActive();
	}
</script>

<style type="text/css">
   .oe_time_card {
      min-width: 430px;
      overflow: auto;
      margin: 0 0 10px 0;
   }
   
   .oe_time_card td
   {
      vertical-align: bottom;
      border-bottom: 1px solid #AAAAAA;
   }
   
   .oe_time_card td.oe_hours, .oe_time_card th {
      text-align: center;
   }
   
   .oe_time_card tr:hover {
      background-color: #F8F8F8;
   }
   
   .oe_time_card .oe_hours {
      cursor: pointer;
      padding: 6px 4px !important;
   }
   
   .oe_hours {
      width: 46px !important;
      height: 27px !important;
   }
   
   .oe_hours input {
      width: 40px !important;
      height: 13px !important;
   }
   
   .oe_project {
      width: 150px !important;
      vertical-align: middle !important;
   }
   
   .oe_subproject {
      vertical-align: middle !important;
   }
   
   td.oe_attribute
   {
      padding-bottom: 15px !important;
      vertical-align: bottom;
   }
   
   td.oe_period
   {
      border-right:  0 none !important;
      padding-right: 0 !important;
      width: 50px !important;
   }
   
   td.oe_week 
   {
      padding-right: 95px !important;
      border-right: 0 none !important;
      width: 223px;
   }
   
   .oe_week select
   {
      width: 223px;
   }
   
   td.oe_user 
   {
      border-right:  0 none !important;
      padding-right: 0 !important;
      width: 34px !important;
   }
   
   .oe_employee select
   {
      width: 165px !important;
   }
   
   td.oe_submit
   {
      text-align: right;
      padding: 15px 21px 23px 0; 
   }
   
   #<?php echo $class.'_item' ?> ul.ae_editform_field_errors
   {
      margin-bottom: 3px !important;
   }
   
   #<?php echo $class.'_post_flag' ?> td
   {
      padding-bottom: 9px;
   }
   
   .oe_attribute {
      height: 19px !important;
      overflow: hidden;
   }
   
   .oe_planed {
      vertical-align: top;
      line-height: 10px;
      height: 14px !important;
      font-size: 10px;
      font-weight: normal;
      margin-top: 4px;
      /*color: #208020;*/
   }
   
   .oe_text {
      color: #208020;
   }
</style>
