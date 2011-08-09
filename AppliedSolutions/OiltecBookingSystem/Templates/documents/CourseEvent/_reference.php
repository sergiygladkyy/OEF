<?php
   $attrs = '';
   
   foreach($attributes as $name => $value)
   {
      $attrs .= ' '.$name.'="'.$value.'"';
   }
   
   $hasCurrent = false;
?>
<select<?php echo $attrs ?>">
  <option value="0">&nbsp;</option>
<?php
   foreach ($options as $row)
   {
      $option = '<option value="'.$row['value'].'"';
      
      if ($row['value'] == $current)
      {
         $hasCurrent = true;
         
         $option .= ' current="true" selected';
      }
      
      $option .= '>'.$row['text'].($row['deleted'] ? '&nbsp;(marked for deletion)' : '').'</option>';
      
      echo $option;
   }
   
   if (!$hasCurrent && !empty($current))
   {
      echo '<option value="'.$row['value'].'" current="true" selected>Element &lt;'.$current.'&gt; not found</option>';
   }
?>
</select>
