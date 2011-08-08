<?php
   $class   = $kind.'_'.$type;
   $tprefix = 'aeform['.$kind.']['.$type.'][attributes][tabulars][Schedule]['.$index.']';
   $n = 0;
?>
<table id="schedule_item_<?php echo $index ?>" class="schedule_item">
<tbody>
  <tr>
    <td class="no_border label">Duration:</td>
    <td class="no_border no_border_value">
      <?php
         $d_value = ($ts_to && $ts_from) ? ($ts_to - $ts_from)/3600 : 0;
      ?>
      <input class="schedule_duration" onChange="onChangeScheduleDuration(this, '<?php echo $index ?>');" type="text" name="<?php echo $tprefix.'[Duration]' ?>" value="<?php echo $d_value ?>" />
      <?php if ($ts_to && $ts_from): ?>
        <input class="datetime_from" type="hidden" name="<?php echo $tprefix.'[DateTimeFrom]' ?>" value="<?php echo date('Y-m-d H:i:s', $ts_from) ?>">
        <input class="datetime_to" type="hidden" name="<?php echo $tprefix.'[DateTimeTo]' ?>" value="<?php echo date('Y-m-d H:i:s', $ts_to) ?>">
      <?php endif;?>
    </td>
    <?php
       $start = $end = date('Y-m-d');
       $start = strtotime($start.' '.$options['time_from']);
       $end   = strtotime($end.' '.$options['time_to']);
       $step  = $options['step']*60; 
       
       while ($start <= $end)
       {
          echo '<td rowspan="2" class="schedule_time_header">'.date("H:i", $start).'</td>';
          
          $n++;
          
          $start += $step;
       }
    ?>
  </tr>
  <tr>
    <td class="no_border label" style="padding-bottom: 7px;">Date:</td>
    <td class="no_border no_border_value">
      <nobr>
        <?php
          $d_name  = $tprefix.'[Date]';
          $d_value = $ts_from ? date('Y-m-d', $ts_from) : '';
          $d_id    = str_replace(array('[', ']'), array('_', ''), $d_name);
        ?>
        <input class="schedule_date" maxlength="10" onChange="onChangeScheduleDate(this, '<?php echo $index ?>');" id="<?php echo $d_id ?>" type="text" name="<?php echo $d_name ?>" value="<?php echo $d_value ?>" />
        <img class="oef_datetime_picker" onclick="if (!document.getElementById('<?php echo $d_id ?>').disabled) NewCssCal('<?php echo $d_id ?>','yyyymmdd','arrow',false, 24, false);" alt="Pick a date" src="/ext/OEF/Framework/MindTouch/Js/datetimepicker/images/cal.gif" style="vertical-align: top; padding-top: 1px;" />
      </nobr>
    </td>
  </tr>
  <?php
     if ($ts_from || $ts_to)
     {
        $p_beg = $p_end = date('Y-m-d', ($ts_from ? $ts_from : $ts_to));
        $p_beg = strtotime($p_beg.' '.$options['time_from']);
        $p_end = strtotime($p_end.' '.$options['time_to']);
     }
     
     $win_beg = $win_end = -1;
     
     if ($ts_from && $ts_to)
     {
        $t_win_beg = ($ts_from < $p_beg) ? $p_beg : $ts_from;
        $t_win_end = ($ts_to   < $p_beg) ? $p_beg : $ts_to;
                
        $win_beg = floor(($t_win_beg - $p_beg)/$step);
        $win_end = ceil(($t_win_end - $p_beg)/$step) - 1;
        
        $t_win_class = 'current_time_window';
     }
  ?>
  <tr class="first_row">
    <td class="no_border small_padding_label label">Room:</td>
    <td class="small_padding">
      <?php
         $tpl_params = array(
            'attributes' => array(
               'name'     => $tprefix.'[Room]',
               'class'    => 'schedule_room',
               'onChange' => "onChangeScheduleRoom(this, '".$index."');"
            ),
            'options' => $links['Room'],
            'current' => $item['Room']
         );
         
         echo self::include_template('reference', $tpl_params);
      ?>
    </td>
    <?php
       $busy = array();
       
       if (!empty($room_recs))
       {
          foreach ($room_recs as $rec)
          {
             $b_beg = strtotime($rec['DateTimeFrom']);
             $b_end = strtotime($rec['DateTimeTo']);
             
             if ($b_beg < $p_beg) $b_beg = $p_beg;
             if ($b_end > $p_end) $b_end = $p_end;
             
             $n_b = floor(($b_beg - $p_beg)/$step);
             $n_e = ceil(($b_end - $p_beg)/$step) - 1;
             
             for ($i = $n_b; $i <= $n_e; $i++)
             {
                $busy[$i] = true;
             }
          }
       }
       
       for ($i = 0; $i < $n; $i++)
       {
          $html = '<td cell="'.$i.'" class="small_padding'.(empty($busy[$i]) ? '' : ' busy');
          
          if ($i == $win_beg)
          {
             $html .= ' time_window_begin_top '.$t_win_class.'"';
          }
          else if ($win_beg < $i && $i < $win_end)
          {
             $html .= ' time_window_top '.$t_win_class.'"';
          }
          else if ($i == $win_end)
          {
             $html .= ' time_window_end_top '.$t_win_class.'"';
          }
          else
          {
             $html .= '"';
          }
          
          $html .= '>&nbsp;</td>';
                    
          echo $html;
       }
    ?>
  </tr>
  <tr class="last_row">
    <td class="no_border small_padding_label label">Instructor:</td>
    <td class="small_padding">
      <?php
         $tpl_params = array(
            'attributes' => array(
               'name'     => $tprefix.'[Instructor]',
               'class'    => 'schedule_instructor',
               'onChange' => "onChangeScheduleInstructor(this, '".$index."');"
            ),
            'options' => $links['Instructor'],
            'current' => $item['Instructor']
         );
         
         echo self::include_template('reference', $tpl_params);
      ?>
    </td>
    <?php
       $busy = array();
       
       if (!empty($inst_recs))
       {
          foreach ($inst_recs as $rec)
          {
             $b_beg = strtotime($rec['DateTimeFrom']);
             $b_end = strtotime($rec['DateTimeTo']);
             
             if ($b_beg < $p_beg) $b_beg = $p_beg;
             if ($b_end > $p_end) $b_end = $p_end;
             
             $n_b = floor(($b_beg - $p_beg)/$step);
             $n_e = ceil(($b_end - $p_beg)/$step) - 1;
             
             for ($i = $n_b; $i <= $n_e; $i++)
             {
                $busy[$i] = true;
             }
          }
       }
       
       for ($i = 0; $i < $n; $i++)
       {
          $html = '<td cell="'.$i.'" class="small_padding'.(empty($busy[$i]) ? '' : ' busy');
          
          if ($i == $win_beg)
          {
             $html .= ' time_window_begin_bottom '.$t_win_class.'"';
          }
          else if ($win_beg < $i && $i < $win_end)
          {
             $html .= ' time_window_bottom '.$t_win_class.'"';
          }
          else if ($i == $win_end)
          {
             $html .= ' time_window_end_bottom '.$t_win_class.'"';
          }
          else
          {
             $html .= '"';
          }
          
          $html .= '>&nbsp;</td>';
                    
          echo $html;
       }
    ?>
  </tr>
</tbody>
</table>
<div id="time_window_<?php echo $index ?>" class="time_window">&nbsp;</div>
<script type="text/javascript">
	jQuery('#schedule_item_<?php echo $index ?> .current_time_window').mousedown(function(e){
		onActivateTimeWindow(e, '<?php echo $index ?>');
	});

	options[<?php echo $index?>] = {};
	options[<?php echo $index?>]['time_from'] = <?php $arr = explode(':', $options['time_from']); echo $arr[0]*3600 + $arr[1]*60; ?>;
	options[<?php echo $index?>]['time_to'] = <?php $arr = explode(':', $options['time_to']); echo $arr[0]*3600 + $arr[1]*60; ?>;
	options[<?php echo $index?>]['step'] = <?php echo $step ?>;
	options[<?php echo $index?>]['cells'] = <?php echo $n - 1 ?>;
</script>
<pre><?php //print_r($options); ?></pre>
<pre><?php //print_r($room_recs); ?></pre>
<pre><?php //print_r($inst_recs); ?></pre>