
function oeSchedule(tag, options)
{
	/* Attributes */
	
	this.tag         = tag;
	this.calendar    = options.calendar ? options.calendar : {};
	this.data        = options.data ? options.data : {};
	this.settings    = options.settings ? options.settings : null;
	this.attr_prefix = options['attr_prefix'] ? options['attr_prefix'] : 'schedule';
	this.monthName   = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	
	
	/* Methods */
	
	/**
	 * Display form
	 * 
	 * @param int year
	 * @return void
	 */
	this.displayForYear = function(year)
	{
		if (this.settings)
		{
			this.prepareSchedule(year, this.settings);
		}
		
		var content = this.generateForYear(year);
		
		document.getElementById(tag).innerHTML = content;
		
		var clickCallback = this.onClick;
		var blurCallback  = this.onBlur;
		
		jQuery('#' + tag + ' .oe_hours').click(function(event){
			clickCallback(event);
		}).find('.oe_value').blur(function(event){
			blurCallback(event);
		});
	};
	
	/**
	 * Generate year schedule
	 * 
	 * @param int year
	 * @return string - html code
	 */
	this.generateForYear = function(year)
	{
		var ret = '';
		
		ret += '\n<table>\n<thead>\n\t<tr>\n\t\t<th>&nbsp;</th>';
		
		for (d = 1; d < 32; d++)
		{
			ret += '\n\t\t<th class="oe_day">' + (d < 10 ? '0' + d : d) + '</th>';
		}
		
		ret += '\n\t</tr>\n</thead>\n<tbody>';
		
		for (month = 0; month < 12; month++)
		{
			ret += this.generateForMonth(year, month); 
		}
		
		ret += '\n</tbody>\n</table>';
		
		return ret;
	};
	
	/**
	 * Generate month schedule
	 * 
	 * @param int year
	 * @param int month
	 * @return string - html code
	 */
	this.generateForMonth = function(year, month)
	{
		var ret  = '';
		var date = new Date(year, month);
		var cnt  = 0;
		
		var _year  = date.getFullYear();
		var _month = '' + (date.getMonth() + 1);
		
		if (_month.length == 1) _month = '0' + _month;
		
		ret += '\n\t<tr>\n\t\t<th class="oe_month">' + this.monthName[month] + '</th>';
		
		while (month == date.getMonth())
		{
			cnt++;
			
			var _day = '' + date.getDate();
			if (_day.length == 1) _day = '0' + _day;
			
			var currentDate = _year + '-' + _month + '-' + _day;
			
			if (this.calendar[currentDate])
			{
				var classes = this.calendar[currentDate]['Working'] ? '' : ' oe_day_off';
			}
			else var classes = ' oe_day_undefined';
			
			if (this.data[currentDate] && this.data[currentDate]['Hours'] != 0)
			{
				var hours = this.data[currentDate]['Hours'];
			}
			else var hours = '';
			
			ret += '\n\t\t<td class="oe_hours' + classes + '">';
			ret += '<div class="oe_item">';
			ret += '<span class="oe_text">' + hours + '</span>';
			ret += '<input class="oe_value" type="text" name="' + this.attr_prefix + '[' + currentDate + ']" value="' + hours + '" style="display: none;">';
			ret += '</div>';
			ret += '</td>';
				
			date.setDate(cnt + 1);
		}

		for (i = cnt; i < 31; i++)
		{
			ret += '\n\t\t<td>&nbsp;</td>';
		}
		
		ret += '\n\t</tr>';
		
		return ret;
	};
	
	/**
	 * OnClick
	 * 
	 * @param Event event
	 * @return void
	 */
	this.onClick = function(event)
	{
		if (event.stopPropagation)
		{
			event.stopPropagation();
		}
		else event.cancelBubble = true;
		
		var element = jQuery(event.target || event.srcElement).get(0);
		
		if (element.nodeName != 'TD') element = element.parentNode; 
		
		jQuery(element).find('.oe_text').css('display', 'none');
		jQuery(element).find('.oe_value').css('display', 'block').focus();
	};
	
	/**
	 * OnBlur
	 * 
	 * @param Event event
	 * @return void
	 */
	this.onBlur = function(event)
	{
		if (event.stopPropagation)
		{
			event.stopPropagation();
		}
		else event.cancelBubble = true;
		
		var element = jQuery(event.target || event.srcElement).parent().get(0);
		var input   = jQuery(element).find('.oe_value').css('display', 'none').get(0);
		
		// Validation
		var value = parseInt(input.value, 10);
		
		if (isNaN(value) || value < 0 || value > 24)
		{
			jQuery(element).addClass('sched_invalid_value');
		}
		else
		{
			jQuery(element).removeClass('sched_invalid_value');
			
			input.value = value;
		}
		
		jQuery(element).find('.oe_text').text(value).css('display', 'block');
	};
	
	/**
	 * Prepare this object
	 * 
	 * @param int year
	 * @param object settings
	 * @return void
	 */
	this.prepareSchedule = function(year, settings)
	{
		var hours;
		
		if (settings.hours_in_week)
		{
			hours = parseInt(settings.hours_in_week, 10)/5;
		}
		else hours = 40/5;
		
		var date = new Date(year, 0, 1);
		
		while (year == date.getFullYear())
		{
			var _month = '' + (date.getMonth() + 1);
			if (_month.length == 1) _month = '0' + _month;
			
			var _day = '' + date.getDate();
			if (_day.length == 1) _day = '0' + _day;
			
			var currentDate = year + '-' + _month + '-' + _day;
			this.data[currentDate] = {};
			
			if (settings.with_holidays && this.calendar[currentDate])
			{
				this.data[currentDate]['Hours'] = (this.calendar[currentDate]['Working'] ? hours : '');
			}
			else
			{
				var dayNumb = date.getDay();
				this.data[currentDate]['Hours'] = ((dayNumb == 0 || dayNumb == 6) ? '' : hours);
			}
			
			date.setDate(date.getDate() + 1);
		}
	};
	
	/**
	 * Check values in current schedule
	 * 
	 * @return void
	 */
	this.check = function()
	{
		return jQuery('#' + this.tag).find('.sched_invalid_value').size() == 0;
	};
}
