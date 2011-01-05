  <h3>Schedule</h3>
  <input type="hidden" name="<?php echo $attr_prefix."[name]"; ?>" value="<?php echo $name ?>" />
  <div style="padding: 0 0 7px 0;">
    Choise year:&nbsp;<select name="<?php echo $attr_prefix."[year]" ?>" onChange="onChange(this);" class="oe_year">
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
<?php if ($generate): ?>
  <div class="oe_schedule_options">
    <h3>Schedule settings</h3>
    <table>
      <tr>
        <td>Hours in week:</td>
        <td><input type="text" name="week_hours" value="" /></td>
      </tr>
      <tr>
        <td>With the holidays:</td>
        <td><input type="checkbox" name="day_off" value="1" /></td>
      </tr>
      <tr>
        <td colspan="2"><input type="button" name="generate" value="Generate" onclick="generateSchedule(this)" /></td>
      </tr>
    </table>
  </div>
<?php endif;?>

  <script type="text/javascript">
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

    function onChange(element)
    {
  	    appInactive();
        appAddLoader();

  	    var sched = jQuery('<?php echo '#'.$kind.'_'.$type.'_item' ?>').find('input[name="<?php echo $attr_prefix."[attributes][_id]" ?>"]').attr('value');
  	    
  	    displayCustomForm('<?php echo $params['uid'] ?>', '<?php echo $name ?>', {year: element.value, schedule: sched}, '<?php echo $params['tag_id'] ?>');
    }
    
  <?php if ($generate): ?>
    appInactive();

    function generateSchedule(element)
    {
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
    appActive();
    displaySchedule(schedule_options);
  <?php endif;?>
  </script>