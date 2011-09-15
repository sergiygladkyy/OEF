
<tr class="catalogs_UserBrowser_list_item ae_list_item<?php echo empty($item['_deleted']) ? '' : ' ae_deleted_col' ?> "<?php if ($parent) echo ' parent="unit_'.$parent.'"' ?>>
   <td style="display: none;">
      <span class="catalogs_UserBrowser_item_id ae_item_id" style="display: none;"><?php echo $is_folder? 'unit_'.$item['_id'] : $item['NaturalPerson'] ?></span>
   </td>
   <?php if (!empty($is_folder)): ?>
      <td>
         <div class="oef_tree_control" style="padding-left: <?php echo 15*$level ?>px;">
            <div class="oef_tree_active">&nbsp;</div>
            <div class="oef_tree_folder">&nbsp;</div>
   <?php else: ?>
      <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser'); document.getElementById('selectedUser').value='<?php echo $item['NaturalPerson'] ?>';">
         <div class="oef_tree_control" style="padding-left: <?php echo 15*$level ?>px;">
            <div class="oef_tree_not_active">&nbsp;</div>
            <div class="oef_tree_item">&nbsp;</div>
   <?php endif; ?>
            <div class="oef_tree_desc"><nobr><?php echo $item['Description'] ?></nobr></div>
         </div>
      </td>
   <?php if ($is_folder): ?>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
   <?php else :?>
      <?php $prewiev = empty($item['Photo']) ? '/skins/common/icons/mrab_no_profile_image.png' : $upload_dir.'preview'.$item['Photo'] ?>
      <td class="photo" onclick="javascript:selectColumn(this, 'catalogs_UserBrowser'); document.getElementById('selectedUser').value='<?php echo $item['NaturalPerson'] ?>';">
         <div onmouseover="ShowABPhotoDialog('photo_img_ctl_<?php echo $item['NaturalPerson'] ?>', '<?php echo $prewiev ?>'); return true;"
              onmouseout="HideABPhotoDialog('<?php echo $prewiev ?>'); return true;">
              <img id="photo_img_ctl_<?php echo $item['NaturalPerson'] ?>" width="23" height="16" src="/skins/aconawellpro/images/photo_icn.gif">
         </div>
      </td>
      <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser'); document.getElementById('selectedUser').value='<?php echo $item['NaturalPerson'] ?>';">
         <?php echo $item['Name'] ?>
      </td>
      <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser'); document.getElementById('selectedUser').value='<?php echo $item['NaturalPerson'] ?>';">
         <?php echo $item['Surname'] ?>
      </td>
      <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser'); document.getElementById('selectedUser').value='<?php echo $item['NaturalPerson'] ?>';">
         <?php echo $item['Phone'] ?>
      </td>
      <td onclick="javascript:selectColumn(this, 'catalogs_UserBrowser');document.getElementById('selectedUser').value='<?php echo $item['NaturalPerson'] ?>';">
         <?php echo isset($item['OrganizationalPosition']['text']) ? $item['OrganizationalPosition']['text'] : '&nbsp;' ?>
      </td>
  <?php endif; ?>
</tr>
