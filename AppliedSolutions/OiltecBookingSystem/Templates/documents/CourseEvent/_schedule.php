<script type="text/javascript">
    var options = {};
</script>

<h3>Schedule</h3>
<?php $i = 0; ?>
<?php foreach ($schedule as $item): ?>
<div class="schedule_item_container">
  <?php echo self::generateScheduleItem($item, $i++) ?>
</div>
<?php endforeach; ?>

<script type="text/javascript">
	var t_wins  = {};
	var current = null;

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
		jQuery('body *').disableSelection();
		
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
		
		jQuery('body *').enableSelection();

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
		
		jQuery(item).find('.datetime_from').attr('value', formattedDate(date_from));
		jQuery(item).find('.datetime_to').attr('value', formattedDate(date_to));
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
</script>