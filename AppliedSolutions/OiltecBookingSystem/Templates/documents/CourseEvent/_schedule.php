<script type="text/javascript">
    var options = {};
    var win_map = {};

    /**
	 * Remove all records for specified item
	 *
	 * @param string index - item index
	 * @return void 
	 */
	function clearWinMap(index)
	{
		for (var date in win_map)
		{
			for (var type in win_map[date])
			{
				for (var id in win_map[date][type])
				{
					if (win_map[date][type][id][index])
						delete win_map[date][type][id][index];
				}
			}
		}
	}
</script>

<h3>Schedule</h3>
<div id="documents_CourseEvent_tabulars_Schedule_edit_block">
<?php $i = 0 ?>
<?php foreach ($schedule as $item): ?>
  <div id="documents_CourseEvent_tabulars_Schedule_<?php echo $i ?>_item" class="tabular_item schedule_item_container">
    <?php echo self::generateScheduleItem($item, $i++, $owner) ?>
  </div>
<?php endforeach; ?>
</div>
<div style="margin: 25px 10px 15px 10px;">
  <a href="#" id="AddScheduleItem" class="green_link" onclick="addScheduleItem(); return false;">
     add new schedule item
  </a>
</div>

<?php    
   $template  = '<div id="documents_CourseEvent_tabulars_Schedule_%%i%%_item" class="tabular_item schedule_item_container">';
   $template .= self::generateScheduleItem(array(), '%%i%%', $owner).'</div>';
   
   $template = str_replace(array(chr(0), chr(9), chr(10), chr(11), chr(13)), ' ', $template);
   $template = str_replace(array("/script", "'") , array("/%%script%%", "\'"), $template);
?>
<script type="text/javascript">
	ae_index['documents_CourseEvent_tabulars_Schedule'] = <?php echo $i - 1 ?>;
	ae_template['documents_CourseEvent_tabulars_Schedule'] = '<?php echo $template ?>';

	var t_wins  = {};
	var current = null;
	var deact_selector = '#rightSide'; // 'body *'
	
	jQuery(document).mouseup(function(e){
		if (current === null) return;

		onDeactivateTimeWindow(e);
	});
	
	jQuery(document).mousemove(function(e){
    	if (current === null) return;

    	onItemMouseMove(e, current);
    });

	jQuery.fn.extend({
	    disableSelection : function() {
	            this.each(function() {
	                    this.onselectstart = function() { return false; };
	                    this.unselectable = "on";
	                    jQuery(this).css('-moz-user-select', 'none');
	            });
	    },
	    enableSelection : function() {
	            this.each(function() {
	                    this.onselectstart = function() {};
	                    this.unselectable = "off";
	                    jQuery(this).css('-moz-user-select', 'auto');
	            });
	    }
	});
    
	/**
	 * Activate time window
	 *
	 * @param DOMEvents e
	 * @return void
	 */
	function onActivateTimeWindow(e, index)
	{
		jQuery(deact_selector).disableSelection();
		
		current = index;

		jQuery('#schedule_item_' + index + ' .current_time_window').each(function(index){
			if (!jQuery(this).hasClass('active_time_window'))
				jQuery(this).addClass('active_time_window');
		});
		
		initializeTimeWindowParams(e, index);

		var params = getTimeWindowParams(index);

		jQuery('#time_window_' + index).css('left', params['left'])
			.css('top', params['top'])
			.css('width', params['width'] - 3)
			.css('height', params['height'])
			.css('display', 'block');
	}

	/**
	 * Deactivate time window
	 *
	 * @param DOMEvents e
	 * @return void
	 */
	function onDeactivateTimeWindow(e)
	{
		jQuery('#schedule_item_' + current + ' .current_time_window').each(function(index){
			if (jQuery(this).hasClass('active_time_window'))
				jQuery(this).removeClass('active_time_window');
		});
		
		jQuery('#time_window_' + current).css('display', 'none');

		setDateTime(current);

		delete t_wins[current];
		
		jQuery(deact_selector).enableSelection();

		current = null;
	}

	/**
	 * Process item mousemove event
	 *
	 * @param DOMEvents e
	 * @param string index - item index
	 * @return void
	 */
	function onItemMouseMove(e, index)
	{
		var params = getTimeWindowParams(index);
		var opts   = options[index];
		
		var left = params['left'] + e.pageX - params['pageX'];

		if (left < params['begin'])
		{
			left = params['begin'];
		}
		else if (left > params['end'] - params['width'])
		{
			left = params['end'] - params['width'];
		}

		var first_cell = Math.round((left - params['begin'])/params['cell_width']);

		if (params['first_cell'] != first_cell)
		{
			changeTimeInterval(first_cell, index);
		}
		
		jQuery('#time_window_' + index).css('left', left);
	}


	
	/**
	 * Calculate time window parameters
	 *
	 * @param DOMEvents e
	 * @param string index - current item index
	 * @return void
	 */
	function initializeTimeWindowParams(e, index)
	{
		if (t_wins[index]) return t_wins[index];

		var item     = jQuery('#schedule_item_' + index).get(0);
		var beg_node = jQuery(item).find('.time_window_begin_top').get(0);
		var end_node = jQuery(item).find('.time_window_end_bottom').get(0);
		
		var beg_top_offset    = jQuery(beg_node).offset();
		var end_bottom_offset = jQuery(end_node).offset();

		var params = {};

		params['top']    = beg_top_offset.top;
		params['left']   = beg_top_offset.left;
		params['cell_width'] = parseInt(jQuery(beg_node).width(), 10) + 
								parseInt(jQuery(beg_node).css('border-left-width'), 10) + 
								parseInt(jQuery(beg_node).css('border-right-width'), 10) + 
								parseInt(jQuery(beg_node).css('padding-left'), 10) + 
								parseInt(jQuery(beg_node).css('padding-right'), 10);
		params['first_cell'] = jQuery(beg_node).attr('cell');
		params['width']  = end_bottom_offset.left + params['cell_width'] - beg_top_offset.left;
		params['height'] = end_bottom_offset.top + jQuery(end_node).height() - beg_top_offset.top;
		params['begin']  = jQuery(item).find('.schedule_time_header:first').offset().left;
		params['end']    = jQuery(item).find('.schedule_time_header:last').offset().left + params['cell_width'];

		t_wins[index] = params;
		t_wins[index]['pageX'] = e.pageX;
	}

	/**
	 * Return time window parameters
	 *
	 * @param string index - current item index
	 * @return void
	 */
	function getTimeWindowParams(index)
	{
		return t_wins[index];
	}

	/**
	 * Change current time interval
	 *
	 * @param int first_cell - number first cell in time window
	 * @param string index   - item index
	 * @return void
	 */
	function changeTimeInterval(first_cell, index)
	{
		var item = jQuery('#schedule_item_' + index).get(0);

		var bt = jQuery(item).find('.time_window_begin_top').get(0);
		var bb = jQuery(item).find('.time_window_begin_bottom').get(0);
		var et = jQuery(item).find('.time_window_end_top').get(0);
		var eb = jQuery(item).find('.time_window_end_bottom').get(0);

		var beg_numb = parseInt(jQuery(bt).attr('cell'), 10);
		var end_numb = parseInt(jQuery(et).attr('cell'), 10);

		var last_cell = first_cell + end_numb - beg_numb;
		
		if (beg_numb < first_cell && (first_cell - 1 == beg_numb))
		{
			jQuery(et).removeClass('time_window_end_top').addClass('time_window_top').next().addClass('time_window_end_top current_time_window active_time_window')
				.bind('mousedown', function(e) { onActivateTimeWindow(e, index); });
			jQuery(eb).removeClass('time_window_end_bottom').addClass('time_window_bottom').next().addClass('time_window_end_bottom current_time_window active_time_window')
				.bind('mousedown', function(e) { onActivateTimeWindow(e, index); });

			jQuery(bt).removeClass('time_window_begin_top current_time_window active_time_window').unbind('mousedown').next().removeClass('time_window_top').addClass('time_window_begin_top');
			jQuery(bb).removeClass('time_window_begin_bottom current_time_window active_time_window').unbind('mousedown').next().removeClass('time_window_bottom').addClass('time_window_begin_bottom');
		}
		else if (beg_numb > first_cell && (first_cell + 1 == beg_numb))
		{
			jQuery(bt).removeClass('time_window_begin_top').addClass('time_window_top').prev().addClass('time_window_begin_top current_time_window active_time_window')
				.bind('mousedown', function(e) { onActivateTimeWindow(e, index); });
			jQuery(bb).removeClass('time_window_begin_bottom').addClass('time_window_bottom').prev().addClass('time_window_begin_bottom current_time_window active_time_window')
				.bind('mousedown', function(e) { onActivateTimeWindow(e, index); });

			jQuery(et).removeClass('time_window_end_top current_time_window active_time_window').unbind('mousedown').prev().removeClass('time_window_top').addClass('time_window_end_top');
			jQuery(eb).removeClass('time_window_end_bottom current_time_window active_time_window').unbind('mousedown').prev().removeClass('time_window_bottom').addClass('time_window_end_bottom');
		}
		else
		{
			repaintTimeWindow(index, first_cell, last_cell, true);
		}

		t_wins[index]['first_cell'] = first_cell;

		updateWinMap(index, first_cell, last_cell);
	}

	/**
	 * Update item datetime
	 *
	 * @param string index - current item index
	 * @return void
	 */
	function setDateTime(index)
	{
		var item = jQuery('#schedule_item_' + index).get(0);

		if (jQuery(item).find('.time_window_begin_top').size() == 0)
		{
			jQuery(item).find('.datetime_from').attr('value', '');
			jQuery(item).find('.datetime_to').attr('value', '');

			return;
		}
		
		var beg = parseInt(jQuery(item).find('.time_window_begin_top').attr('cell'), 10);
		var end = parseInt(jQuery(item).find('.time_window_end_top').attr('cell'), 10);

		var from = options[index]['time_from'] + beg * options[index]['step'];
		var to   = from + (end - beg + 1) * options[index]['step'];

		var date_str = jQuery(item).find('.schedule_date').attr('value');

		if (date_str.length == 0) date_str = '0000-00-00';

		p_date = date_str.split('-');

		date_from = new Date(p_date[0], p_date[1], p_date[2]);
		date_from.setSeconds(from);

		date_to = new Date(p_date[0], p_date[1], p_date[2]);
		date_to.setSeconds(to);

		var df_str = formattedDate(date_from);
		var dt_str = formattedDate(date_to);

		if (date_str == '0000-00-00')
		{
			df_str = df_str.replace(/^\d{4}-\d{2}-\d{2}\s/gi, '0000-00-00 ');
			dt_str = dt_str.replace(/^\d{4}-\d{2}-\d{2}\s/gi, '0000-00-00 ');
		}
		
		jQuery(item).find('.datetime_from').attr('value', df_str);
		jQuery(item).find('.datetime_to').attr('value', dt_str);
	}

	function formattedDate(date)
	{
		var str = date.getFullYear() + '-' + _to_string(date.getMonth()) + '-' + _to_string(date.getDate());

		return str + ' ' + _to_string(date.getHours()) + ':' + _to_string(date.getMinutes()) + ':' + _to_string(date.getSeconds());
	}
	
	function _to_string(str)
	{
		str = String(str);
		
		if (str.length < 2) str = '0' + str;

		return str;
	}



	/**
	 * Repaint time window basis on specified params
	 *
	 * @param string index   - current item index
	 * @param int first_cell - number of first time window cell
	 * @param int last_cell  - number of last time window cell
	 * @return void
	 */
	function repaintTimeWindow(index, first_cell, last_cell, active)
	{
		var item = jQuery('#schedule_item_' + index).get(0);

		var classes = 'current_time_window active_time_window ';
		classes += 'time_window_begin_top time_window_end_top ';
		classes += 'time_window_top time_window_bottom ';
		classes += 'time_window_begin_bottom time_window_end_bottom';

		var st_classes = 'current_time_window';

		if (active) st_classes += ' active_time_window';
		
		jQuery(item).find('.current_time_window').removeClass(classes).unbind('mousedown');

		if (first_cell > last_cell) return;
		
		jQuery(item).find('*[cell="' + first_cell + '"]').each(function(index){
			if (jQuery(this).parent().hasClass('first_row'))
			{
				jQuery(this).addClass('time_window_begin_top ' + st_classes);
			}
			else
			{
				jQuery(this).addClass('time_window_begin_bottom ' + st_classes);
			}
		});

		jQuery(item).find('*[cell="' + last_cell + '"]').each(function(index){
			if (jQuery(this).parent().hasClass('first_row'))
			{
				jQuery(this).addClass('time_window_end_top ' + st_classes);
			}
			else
			{
				jQuery(this).addClass('time_window_end_bottom ' + st_classes);
			}
		});

		for (i = (first_cell + 1); i < last_cell; i++)
		{
			jQuery(item).find('*[cell="' + i + '"]').each(function(index){
				if (jQuery(this).parent().hasClass('first_row'))
				{
					jQuery(this).addClass('time_window_top ' + st_classes);
				}
				else
				{
					jQuery(this).addClass('time_window_bottom ' + st_classes);
				}
			});
		}

		jQuery(item).find('.current_time_window').bind('mousedown', function(e) { onActivateTimeWindow(e, index); });	
	}


	/**
	 * Update marked as selected in items related with specified item if specified been moved
	 *
	 * @param int index_changed_item
	 * @param bool mode
	 * @return void
	 */
	function onWinMapUpdatedItemMoved(index_changed_item, mode)
	{
		var res = jQuery('#documents_CourseEvent_tabulars_Schedule_edit_block .schedule_item');

		if (res.size() < 2) return;

		var item   = jQuery('#schedule_item_' + index_changed_item).get(0);
		var c_date = jQuery(item).find('.schedule_date').attr('value');
		var c_room = jQuery(item).find('.schedule_room option:selected').attr('value');
		var c_inst = jQuery(item).find('.schedule_instructor option:selected').attr('value');
		
		res.each(function(i)
		{
			var index = parseInt(jQuery(this).attr('index'), 10);
			
			if (index > index_changed_item)
			{
				var date = jQuery(this).find('.schedule_date').attr('value');

				if (mode || date == c_date)
				{
					jQuery(this).find('.grid').removeClass('selected_in_other_items');
					
					var room = jQuery(this).find('.schedule_room option:selected').attr('value');
					var inst = jQuery(this).find('.schedule_instructor option:selected').attr('value');

					var marked = {};
						
					for (var ind in win_map[date]['room'][room])
					{
						if (index <= ind) continue;

						var params = win_map[date]['room'][room][ind];

						if (params['beg'] === undefined || params['end']=== undefined)
						{
							continue;
						}

						for (var n = params['beg']; n <= params['end']; n++)
						{
							if (marked[n]) continue;
							
							jQuery(this).find('*[grid="room"] *[cell="' + n + '"]').addClass('selected_in_other_items');

							marked[n] = true;
						}
					}

					marked = {};
					
					for (var ind in win_map[date]['instructor'][inst])
					{
						if (index <= ind) continue;

						var params = win_map[date]['instructor'][inst][ind];
						
						if (params['beg'] === undefined || params['end']=== undefined)
						{
							continue;
						}
						
						for (var n = params['beg']; n <= params['end']; n++)
						{
							if (marked[n]) continue;
							
							jQuery(this).find('*[grid="instructor"] *[cell="' + n + '"]').addClass('selected_in_other_items');

							marked[n] = true;
						}
					}
				}
			}
		});
	}

	/**
	 * Update marked as selected in items related with specified item if specified been updated
	 *
	 * @param int index_changed_item
	 * @return void
	 */
	function onWinMapUpdatedItemUpdated(index_changed_item)
	{
		var res = jQuery('#documents_CourseEvent_tabulars_Schedule_edit_block .schedule_item');

		if (res.size() < 2) return;

		res.each(function(i) {
			
			var index = parseInt(jQuery(this).attr('index'), 10);

			if (index >= index_changed_item)
			{
				onWinMapUpdatedItemMoved(index, true);
			}
		});
	}
	
	/**
	 * Mark selected in related items
	 *
	 * @return void
	 */
	function onWinMapUpdatedAll()
	{
		var res = jQuery('#documents_CourseEvent_tabulars_Schedule_edit_block .schedule_item');

		if (res.size() < 2) return;

		var first_cleared = false;		

		res.each(function(i) {
			if (!first_cleared)
			{
				jQuery(this).find('.grid').removeClass('selected_in_other_items');
				
				first_cleared = true;
			}
			
			var index = parseInt(jQuery(this).attr('index'), 10);

			onWinMapUpdatedItemMoved(index, true);
		});
	}


	/**
	 * Update win_map
	 *
	 * @param string index   - current item index
	 * @param int first_cell
	 * @param int last_cell
	 * @return void
	 */
	function updateWinMap(index, first_cell, last_cell)
	{
		var item = jQuery('#schedule_item_' + index).get(0);
		var date = jQuery(item).find('.schedule_date').attr('value');

		if (!date) return;
		
		var room  = jQuery(item).find('.schedule_room option:selected').attr('value');
		var inst  = jQuery(item).find('.schedule_instructor option:selected').attr('value');

		if (!win_map[date]) win_map[date] = {};
		if (!win_map[date]['room'])
		{
			win_map[date]['room'] = {};
			win_map[date]['room'][room] = {};
		}
		else if (!win_map[date]['room'][room])
		{
			win_map[date]['room'][room] = {};
		}

		win_map[date]['room'][room][index] = {beg: first_cell, end: last_cell};

		if (!win_map[date]) win_map[date] = {};
		if (!win_map[date]['instructor'])
		{
			win_map[date]['instructor'] = {};
			win_map[date]['instructor'][inst] = {};
		}
		else if (!win_map[date]['instructor'][inst])
		{
			win_map[date]['instructor'][inst] = {};
		}

		win_map[date]['instructor'][inst][index] = {beg: first_cell, end: last_cell};


		onWinMapUpdatedItemMoved(index);
	}
	
	onWinMapUpdatedAll();
</script>