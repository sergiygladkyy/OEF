<?php
   if (empty($edit))
   {
      $v_disp = 'block';
      $e_disp = 'none';
   }
   else
   {
      $v_disp = 'none';
      $e_disp = 'block';
   }
?>
<div id="catalogs_Employees_tabulars_Locations_<?php echo $i ?>_item" class="tabular_item<?php if (empty($row['_id'])) echo ' new' ?>">
  <div class="userRow tabular_col">
    <div class="errors_msg">
      <ul class="catalogs_Employees_tabulars_Locations_<?php echo $i ?>_Location_errors ae_editform_field_errors" style="display: none;"><li>&nbsp;</li></ul>
    </div>
    <div class="userRowLeft">Location:</div>
    <div class="userRowRight">
      <div class="values userValue" style="display: <?php echo $v_disp ?>;">
        <?php echo isset($links[$row['Location']]['text']) ? $links[$row['Location']]['text'] : '&nbsp;' ?>
      </div>
      <div class="fields" style="display: <?php echo $e_disp ?>;">
        <select name="<?php echo $n_prefix.'['.$i.'][Location]' ?>" class="input onBlur" onblur="blurF(this);" onfocus="focusF(this);">
          <option value="0">&nbsp;</option>
          <?php echo $options ?>
        </select>
      </div>
    </div>
  </div>
  <div class="userRow tabular_col">
    <div class="errors_msg">
      <ul class="catalogs_Employees_tabulars_Locations_<?php echo $i ?>_Comment_errors ae_editform_field_errors" style="display: none;"><li>&nbsp;</li></ul>
    </div>
    <div class="userRowLeft">Comment:</div>
    <div class="userRowRight">
      <div class="values userValue" style="display: <?php echo $v_disp ?>;">
        <?php echo $row['Comment'] ?>
      </div>
      <div class="fields" style="display: <?php echo $e_disp ?>;">
        <input type="text" class="input onBlur" name="<?php echo $n_prefix.'['.$i.'][Comment]' ?>" value="<?php echo $row['Comment'] ?>" onblur="blurF(this);" onfocus="focusF(this);"/>
      </div>
    </div>
  </div>
  <div class="locFooter">&nbsp;</div>
  <div class="userRowClear action" style="padding-left: 13px; padding-bottom: 10px; display: <?php echo $e_disp ?>;">
    <a href="#" class="green_link" onclick="deleteLocation(this); return false;">
      delete
    </a>
  </div>
  <?php if (!empty($row['_id'])): ?>
    <input type="hidden" class="pkey" name="<?php echo $n_prefix.'['.$i.'][_id]' ?>" value="<?php echo $row['_id'] ?>" />
    <script type="text/javascript">
      jQuery('select[name="<?php echo $n_prefix.'['.$i.'][Location]' ?>"] option[value="<?php echo $row['Location']?>"]').attr('selected', true);
    </script>
  <?php endif;?>
</div>