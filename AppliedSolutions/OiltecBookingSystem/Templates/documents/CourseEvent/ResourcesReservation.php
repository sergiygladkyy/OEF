<?php
   $class   = $kind.'_'.$type;
   $prefix  = 'aeform['.$kind.']['.$type.']';
   $aprefix = $prefix.'[attributes][attributes]';
?>

<form method="post" action="#" class="oe_custom_edit_form" id="<?php echo $class.'_item' ?>">
  <input type="hidden" name="<?php echo $prefix.'[name]' ?>" value="<?php echo $name ?>" />
  <?php if (!empty($attrs['_id'])): ?>
  <input type="hidden" name="<?php echo $aprefix.'[_id]' ?>" value="<?php echo $attrs['_id'] ?>" />
  <?php endif; ?>
  <table class="documents_CourseEvent_form">
  <tbody>
    <tr id="<?php echo $class.'_post_flag' ?>" style="<?php echo $attrs['_id'] > 0 ? '' : 'display: none;' ?>">
      <td class="<?php echo $class.'_name ae_editform_field_name label' ?>">Status:</td>
      <td class="<?php echo $class.'_value ae_editform_field_value' ?>">
        <div class="<?php echo $attrs['_post'] > 0 ? 'ae_field_posted' : 'ae_field_not_posted' ?>">
          <span class="ae_field_posted_text" style="<?php echo $attrs['_post'] > 0 ? 'display: block;' : 'display: none;' ?>">This document is posted.</span>
          <span class="ae_field_not_posted_text" style="<?php echo $attrs['_post'] > 0 ? 'display: none;' : 'display: block;' ?>">This document is not posted.</span>
        </div>
      </td>
    </tr>
    <tr>
      <td class="<?php echo $class.'_name ae_editform_field_name label' ?>">Application form:</td>
      <td class="<?php echo $class.'_value ae_editform_field_value' ?>">
        <ul class="<?php echo $class.'_ApplicationForm_errors ae_editform_field_errors' ?>" style="display: none;"><li>&nbsp;</li></ul>
        <select name="<?php echo $aprefix.'[ApplicationForm]' ?>" onChange="onChangeApplicationForm(this);">
          <option value="0" selected>&nbsp;</option>
          <?php
            foreach ($a_select as $row)
            {
               $option = '<option value="'.$row['value'].'"';
               
               if ($attrs['ApplicationForm'] == $row['value']) $option .= ' selected';
               
               $option .= '>'.$row['text'].'</option>';
               
               echo $option;
            }
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <td class="<?php echo $class.'_name ae_editform_field_name label' ?>">Course:</td>
      <td class="<?php echo $class.'_value ae_editform_field_value' ?>">
        <ul class="<?php echo $class.'_Course_errors ae_editform_field_errors' ?>" style="display: none;"><li>&nbsp;</li></ul>
        <select name="<?php echo $aprefix.'[Course]' ?>" onChange="onChangeCourse(this);">
          <option value="0" selected>&nbsp;</option>
          <?php
            $_current = $attrs['Course'].'/'.$attrs['CourseNumber'];
            
            foreach ($c_select as $row)
            {
               $option = '<option value="'.$row['value'].'"';
               
               if ($_current == $row['value']) $option .= ' selected';
               
               $option .= '>'.$row['text'].'</option>';
               
               echo $option;
            }
          ?>
        </select>
      </td>
    </tr>
    <tr>
      <td colspan="2" id="schedule_container">
        <?php echo self::include_template('schedule', array('kind' => $kind, 'type' => $type, 'owner' => (isset($attrs['_id']) ? $attrs['_id'] : 0), 'schedule' => $schedule)) ?>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="ae_submit">
        <input type="button" command="save_and_close" class="ae_command" value="Save and Close">
        <input type="button" command="save" class="ae_command" value="Save">
        <input type="button" command="cancel" class="ae_command" value="Close">
      </td>
    </tr>
  </tbody>
  </table>
</form>
<script type="text/javascript">
	var active = {};
	var t_wins = {};
	
    Context.addListener('onChangeCourseResponse', processChangeCourse);
    Context.addListener('onScheduleItemUpdateResponse', processScheduleItemUpdate);

    /**
     * Process onChangeApplicationForm event 
     *
     * @param DOMElements node
     * @return void
     */
	function onChangeApplicationForm(node)
	{
		var app_f_id = node.options[node.selectedIndex].value;

		appInactive();
		appAddLoader();

		displayCustomForm('<?php echo $kind.'.'.$type ?>', '<?php echo $name ?>', {document: <?php echo (int) $attrs['_id'] ?>, ApplicationForm: app_f_id}, 'oef_custom_ResourcesReservation_form');

		jQuery('#oef_custom_ResourcesReservation_form .ae_command').each(function(index) {
	    	jQuery(this).click(function() { 
	    		processFormCommand(this);
	    	});
	    });
	    
		appActive();
	}

	/**
     * Process onChangeCourse event 
     *
     * @param DOMElements node
     * @return void
     */
	function onChangeCourse(node)
	{
		var app_f_node = jQuery('#<?php echo $class.'_item' ?> select[name="<?php echo $aprefix.'[ApplicationForm]' ?>"]').get(0);
		var app_f_id   = app_f_node.options[app_f_node.selectedIndex].value;
		
		var c_id = node.options[node.selectedIndex].value;

		notifyFormEvent('<?php echo $kind.'.'.$type ?>', '<?php echo $name ?>', 'onChangeCourse', {});
	}

	/**
     * Process onChangeScheduleDate event 
     *
     * @param DOMElements node
     * @param string index     - item number in tabular section Schedule
     * @return void
     */
	function onChangeScheduleDate(node, index)
	{
		var date_str = node.value;

		if (!date_str.match(/^\d{4}-\d{2}-\d{2}$/gi))
		{
			alert('Invalid date format');

			jQuery(node).focus();

			return;
		}

		var f_node = jQuery(node).parents('.schedule_item').find('.datetime_from').get(0);
		var t_node = jQuery(node).parents('.schedule_item').find('.datetime_to').get(0);

		f_node.value = (f_node.value.length ? f_node.value.replace(/^\d{4}-\d{2}-\d{2}\s/gi, date_str + ' ') : '');
		t_node.value = (t_node.value.length ? t_node.value.replace(/^\d{4}-\d{2}-\d{2}\s/gi, date_str + ' ') : '');
		
		onScheduleItemUpdate({index: index});
	}

	/**
     * Process onChangeScheduleDuration event 
     *
     * @param DOMElements node
     * @param string index     - item number in tabular section Schedule
     * @return void
     */
	function onChangeScheduleDuration(node, index)
	{
		var item = jQuery('#schedule_item_' + index).get(0);

		var beg = end = 0;

		if (jQuery(item).find('.time_window_begin_top').size() != 0)
		{
			beg = parseInt(jQuery(item).find('.time_window_begin_top').attr('cell'), 10);
		}

		if (jQuery(item).find('.time_window_end_top').size() != 0)
		{
			end = parseInt(jQuery(item).find('.time_window_end_top').attr('cell'), 10);
		}

		var new_numb = Math.ceil(parseFloat(node.value, 10) * 3600 / options[index]['step']); 

		if (new_numb - 1 > options[index]['cells'])
		{
			alert('Invalid duration');

			return;
		}
		
		var new_beg = beg;
		var new_end = beg + new_numb - 1;
		
		if (new_end > options[index]['cells'])
		{
			new_beg -= new_end - options[index]['cells'];
			new_end = options[index]['cells'];
		}
		
		if (new_beg == beg && new_end == end)
		{
			return;
		}

		repaintTimeWindow(index, new_beg, new_end);

		setDateTime(index);
		
		updateWinMap(index, new_beg, new_end);
	}

	/**
     * Process onChangeScheduleRoom event 
     *
     * @param DOMElements node
     * @param string index     - item number in tabular section Schedule
     * @return void
     */
	function onChangeScheduleRoom(node, index)
	{
		onScheduleItemUpdate({index: index});
	}

	/**
     * Process onChangeScheduleInstructor event 
     *
     * @param DOMElements node
     * @param string index     - item number in tabular section Schedule
     * @return void
     */
	function onChangeScheduleInstructor(node, index)
	{
		onScheduleItemUpdate({index: index});
	}

	/**
	 * Generate onScheduleItemUpdate form event
	 *
	 * @param object params
	 * @return void
	 */
	function onScheduleItemUpdate(params)
	{
		notifyFormEvent('<?php echo $kind.'.'.$type ?>', '<?php echo $name ?>', 'onScheduleItemUpdate', params);
	}


	/**
     * Process onChangeCourse event response
     *
     * @param object params
     * @return void
     */
	function processChangeCourse(params)
	{
		if (params.response.type != 'html')
		{
			alert('Invalid response type');

			return;
		}
		
        jQuery('#schedule_container').html(params.response.data);
	}

	/**
     * Process onScheduleItemUpdate event response
     *
     * @param object params
     * @return void
     */
	function processScheduleItemUpdate(params)
	{
		if (params.response.type != 'array')
		{
			alert('Invalid response type');

			return;
		}

		jQuery('#schedule_item_' + params.response.data.index).parent().html(params.response.data.html);

		onWinMapUpdatedItemUpdated(params.response.data.index);
	}



	/**
	 * Add new schedule item
	 * 
	 * @return void
	 */
	function addScheduleItem()
	{
		addTabularSectionItem('documents_CourseEvent_tabulars_Schedule', 'schedule');
	}

	/**
	 * Delete schedule item
	 *
	 * @return void
	 */
	function deleteScheduleItem(elem, index)
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

		clearWinMap(index);
	}
</script>

<style type="text/css">
    .documents_CourseEvent_form td.label {
        border-right: 0 none;
        font-weight: bold;
        width: 115px;
    }
    .documents_CourseEvent_form select {
        width: 245px;
    }
    
    .schedule_item_container {
        padding: 10px 10px 10px 5px;
        border: 1px solid #AAAAAA;
        margin: 20px 7px;
    }
    
    .schedule_date {
        width: 76px !important;
    }
    .schedule_duration {
        width: 76px !important;
    }
    .schedule_room, .schedule_instructor {
        width: 122px !important;
    }
    
    table.schedule_item {
        border-top:    0px none;
        border-right:  1px solid #AAAAAA !important;
        border-bottom: 0px none;
        border-left:   0px none;
    }
    .schedule_item tr {
        border: 0 none !important;
    }
    .schedule_item td {
        border-top:    1px solid #AAAAAA;
        border-right:  0px none;
        border-bottom: 1px solid #AAAAAA;
        border-left:   1px solid #AAAAAA;
        padding: 3px 5px;
    }
    .schedule_item td.no_border {
        border: 0 none !important;
    }
    .schedule_item td.small_padding {
        padding: 1px !important;
    }
    .schedule_item td.small_padding_label {
        padding: 1px 5px !important;
    }
    .schedule_item td.label {
        font-weight: bold;
    }
    .schedule_item td.no_border_value {
        padding-left: 0px !important;
        padding-right: 8px !important;
    }
    
    .schedule_item td.schedule_time_header {
        width: 50px !important;
        padding: 6px 8px 8px 4px;
        font-size: 10px;
        vertical-align: bottom;
        border-bottom: 0 none;
    }
    
    .busy {
        background-color: #ffccff !important;
    }
    .schedule_item .selected_in_other_items {
        background-color: #ffff77;
    }
    
    .time_window {
        position: absolute;
        border: 1px solid #000000;
        background-color: #cccccc;
        opacity: 0.4;
        display: none;
        z-index: 1000;
    }
    
    
    .time_window_begin_top {
        border-left: 1px solid #000000 !important;
        border-top:  1px solid #000000 !important;
    }
    .time_window_top {
        border-top: 1px solid #000000 !important;
    }
    .time_window_end_top {
        border-right: 1px solid #000000 !important;
        border-top:  1px solid #000000 !important;
    }
    .time_window_begin_bottom {
        border-left: 1px solid #000000 !important;
        border-bottom:  1px solid #000000 !important;
    }
    .time_window_bottom {
        border-bottom: 1px solid #000000 !important;
    }
    .time_window_end_bottom {
        border-right: 1px solid #000000 !important;
        border-bottom:  1px solid #000000 !important;
    }
    
    .schedule_item .active_time_window {
        border: 1px solid #AAAAAA !important;
    }
    
    .time_window_begin_top, .time_window_top, .time_window_end_top , 
    .time_window_begin_bottom, .time_window_bottom, .time_window_end_bottom {
        background-color: #d0ffd0;
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
