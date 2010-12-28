
function oeCalendar(tag, options)
{
	/* Attributes */
	
	this.tag  = tag;
	this.data = options['data'] ? options['data'] : [];
	this.attr_prefix = options['attr_prefix'] ? options['attr_prefix'] : 'calendar';
	
	/* Options */
	
	this.mondayFirst  = true;
	this.monthName    = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
	this.weekDayNames = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
	this.workingDays  = [0, 1, 1, 1, 1, 1, 0, 0]; // "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"
	
	/* Methods */
	
	this.displayForYear = function(year)
	{
		var content = this.generateForYear(year);
		
		document.getElementById(tag).innerHTML = content;
		
		var callback = this.onClick;
		
		jQuery('#' + tag + ' .oe_date').click(function(event){
			callback(event);
		});
	};
	
	/**
	 * Generate year calendar
	 * 
	 * @param int year
	 * @return string - html code
	 */
	this.generateForYear = function(year)
	{
		var ret = '';
		
		for (month = 0; month < 12; month++)
		{
			ret += '<div class="oe_month">' + this.generateForMonth(year, month) + '</div>'; 
		}
		
		ret += '<div style="clear: both;"></div>';
		
		return ret;
	};
	
	/**
	 * Generate month calendar
	 * 
	 * @param int year
	 * @param int month
	 * @return string - html code
	 */
	this.generateForMonth = function(year, month)
	{
		var ret  = '';
		var date = new Date(year, month);
		var day  = date.getDay();
		var cnt  = 0;
		var firstDay = this.mondayFirst ? 1 : 0;
		
		ret += '\n<table>\n<thead>\n\t<tr>\n\t\t<td class="oe_month_name" colspan="7">';
		ret += this.monthName[date.getMonth()] + '</td>\n\t</tr>\n\t<tr>';
		
		while (cnt < 7)
		{
			ret += '\n\t\t<td class="oe_day_name">' + this.weekDayNames[(firstDay + cnt)] + '</td>';
			cnt++;
		}
		
		ret += '\n\t</tr>\n</thead>\n<tbody>';
		
		if (this.mondayFirst)
		{
		   if (day == 0) day = 7;
		   
		   day--;
		}
		else day;
		
		var dateNumb = 1 - day;
		
		cnt  = 0;
		ret += '\n\t<tr>';
		
		while (true)
		{
			if (cnt % 7 == 0 && cnt != 0)
			{
				if (month != date.getMonth())
				{
					ret += '\n\t</tr>';
					break;
				}
				
				cnt  = 0;
				ret += '\n\t</tr>\n\t<tr>';
			}
			
			if (dateNumb > 0 && month == date.getMonth())
			{
				var _month = '' + (date.getMonth() + 1);
				if (_month.length == 1) _month = '0' + _month;
				
				var _day = '' + date.getDate();
				if (_day.length == 1) _day = '0' + _day;
				
				var currentDate = date.getFullYear() + '-' + _month + '-' + _day;
				
				if (this.data[currentDate])
				{
					var work = this.data[currentDate]['Working'];
				}
				else var work = this.workingDays[firstDay + cnt] == 0 ? 0 : 1;
				
				ret += '\n\t\t<td class="oe_date' + (work ? '' : ' oe_day_off') + '">' + dateNumb;
				ret += '<input type="hidden" name="' + this.attr_prefix + '[' + currentDate + ']" value="' + work + '">';
				ret += '</td>';
				
				date.setDate(dateNumb + 1);
			}
			else
			{
				ret += '\n\t\t<td>&nbsp;</td>';
			}
			
			cnt++;
			dateNumb++;
		}

		ret += '\n</tbody>\n</table>\n';
		
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
		
		var element  = jQuery(event.target || event.srcElement).get(0);
		var hidden   = jQuery(element).find('input[type=hidden]').get(0);
		
		if (hidden.value == '1')
		{
			hidden.value = '0';
			jQuery(element).addClass('oe_day_off');
		}
		else
		{
			hidden.value = '1';
			jQuery(element).removeClass('oe_day_off');
		}
	};
}
