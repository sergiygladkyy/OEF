<h3>Calendar</h3>
<div class="<?php echo $kind.'_'.$type.'_message systemmsg' ?>" style="display: none;">
  <div class="inner">
    <ul class="flashMsg">
      <li>&nbsp;</li>
    </ul>
  </div>
</div>
<form action="#" method="post" class="oe_custom_edit_form">
  <input type="hidden" name="<?php echo $attr_prefix."[name]"; ?>" value="<?php echo $name ?>" />
  <div style="padding: 0 0 7px 0;">
    Choise year:&nbsp;<select name="<?php echo $attr_prefix."[year]" ?>" onChange="onChange(this);">
  <?php for ($i = $year_start; $i <= $year_end; $i++): ?>
    <?php if ($i == $year_cur): ?>
      <option value="<?php echo $i ?>" selected><?php echo $i ?></option>
    <?php else: ?>
      <option value="<?php echo $i ?>"><?php echo $i ?></option>
    <?php endif;?>
  <?php endfor; ?>
    </select>
  </div>
  <div id="calendar" class="oe_calendar"></div>
  <input type="submit" value="Save" />
</form>
<br />
<script type="text/javascript">
  var calendar_options = {
    attr_prefix: '<?php echo $attr_prefix."[attributes]" ?>',
    data:         <?php echo $data ?>
  };

  var calendar = new oeCalendar('calendar', calendar_options);
  calendar.displayForYear(<?php echo $year_cur ?>);

  jQuery('.oe_custom_edit_form').submit(function() {
    appInactive();
    appAddLoader();
    
    jQuery(this).ajaxSubmit(custom_edit_form_options);

    appActive();

  	return false;
  });

  function onChange(element)
  {
	  appInactive();
      appAddLoader();
	  
	  displayCustomForm('<?php echo $params['uid'] ?>', '<?php echo $name ?>', {year: element.value}, '<?php echo $params['tag_id'] ?>');
	  
	  appActive();
  }
</script>
