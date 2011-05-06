  <h3>Schedule</h3>
  <input type="hidden" name="<?php echo $attr_prefix."[name]"; ?>" value="<?php echo $name ?>" />
  <div style="padding: 0 0 7px 0;">
    Year:&nbsp;<select name="<?php echo $attr_prefix."[year]" ?>" onChange="onChange(this);" class="oe_year">
  <?php for ($i = $year_start; $i <= $year_end; $i++): ?>
    <?php if ($i == $year_cur): ?>
      <option value="<?php echo $i ?>" selected><?php echo $i ?></option>
    <?php else: ?>
      <option value="<?php echo $i ?>"><?php echo $i ?></option>
    <?php endif;?>
  <?php endfor; ?>
    </select>
  </div>
  <div id="schedule" class="oe_schedule"></div>
<?php if (!empty($isNew)): ?>
  <div class="oe_schedule_options" style="display: none;">
    <h3>Schedule settings</h3>
    <table>
      <tr>
        <td>Hours per week:</td>
        <td><input type="text" name="week_hours" value="" /></td>
      </tr>
      <tr>
        <td>Consider holidays non-working:</td>
        <td><input type="checkbox" name="day_off" value="1" /></td>
      </tr>
      <tr>
        <td colspan="2"><input type="button" name="generate" value="Generate" onclick="generateSchedule(this)" /></td>
      </tr>
    </table>
  </div>
<?php endif; ?>

<?php $formID = $kind.'_'.$type.'_item' ?>

  <script type="text/javascript">
	var isNewSchedule = <?php echo $isNew ? 'true' : 'false' ?>;
	
	Context.addListener('<?php echo $formID ?>_before_submit', onBeforeSubmit<?php echo $formID ?>);
	Context.addListener('<?php echo $kind.'_'.$type ?>_end_process', onEndProcess);
	
	function onBeforeSubmit<?php echo $formID ?>(params)
	{
		appDisplayLoader(false);
		
		var status = isNewSchedule || confirm('You are about to change the Schedule. This change will affect all the future documents. If the changes are also made for the past dates, it is required that you repost the already existing project assignments and time cards.\nAttention! This change might result in changes for the already reported information.');

		appDisplayLoader(true); 

		Context.setLastStatus(status);
	}
	
	function onEndProcess(params)
	{
		if (!isNewSchedule || !params.status) return;

		isNewSchedule = false;
	}
	
    var schedule_options = {
      attr_prefix: '<?php echo $attr_prefix."[attributes][Schedule]" ?>',
      calendar:     <?php echo $calendar ?>,
      data:         <?php echo $data ?>
    };
    
    function displaySchedule(options)
    {
    	var schedule = new oeSchedule('schedule', schedule_options);

    	schedule.displayForYear(<?php echo $year_cur ?>);
    }

    function displayScheduleSettings(form_id)
    {
    	appInactive();
    	
    	jQuery('#' + form_id + ' .oe_schedule_options').css('display', 'block');
    }

    function onChange(element)
    {
    	appInactive();
    	appAddLoader();
    	
	    var sched = jQuery('#<?php echo $formID ?>').find('input[name="<?php echo $attr_prefix."[attributes][_id]" ?>"]').attr('value');
  	    
  	    displayCustomForm('<?php echo $params['uid'] ?>', '<?php echo $name ?>', {year: element.value, schedule: sched}, '<?php echo $params['tag_id'] ?>');

  	    appActive();
    }
    
  <?php if (!empty($isNew)): ?>
    jQuery(document).ready(function() {
        displayMessage('<?php echo $kind.'_'.$type ?>', 'Schedule for this year does not exist. In order to generate a schedule, click <a href="#" onclick="displayScheduleSettings(\'<?php echo $formID ?>\'); return false;">generate</a>', 2);

        if (jQuery('#<?php echo $formID ?> input[name="dgenerate"]').size() == 0)
        {
            jQuery('#<?php echo $formID ?> .ae_submit').prepend('<input type="button" name="dgenerate" value="Generate" onclick="displayScheduleSettings(\'<?php echo $formID ?>\')" />');
        }
    });
    
    function generateSchedule(element)
    {
        appAddLoader();

        var form     = jQuery(element).parents('.oe_schedule_options').get(0);
        var settings = {};

        jQuery(form).css('display', 'none');
        
        settings.with_holidays = jQuery(form).find('input[name=day_off]').attr('checked');
        settings.hours_in_week = jQuery(form).find('input[name=week_hours]').attr('value');

        schedule_options.settings = settings;

        displaySchedule(schedule_options);

        appActive();
    }
  <?php else: ?>
    hideMessages();
    jQuery('#<?php echo $formID ?> input[name="dgenerate"]').remove();
    displaySchedule(schedule_options);
  <?php endif;?>
  </script>
