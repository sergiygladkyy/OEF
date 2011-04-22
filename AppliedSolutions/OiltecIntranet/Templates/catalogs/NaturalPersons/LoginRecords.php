<?php $prefix = $form_prefix.'[attributes][Records]' ?>
<h3>Login Records</h3>
<input type="hidden" name="<?php echo $form_prefix."[name]"; ?>" value="<?php echo $name ?>" />
<div style="padding: 0 0 7px 0;">
  <table>
  <thead>
    <tr>
      <th style="width: 10px;">&nbsp;</th>
      <th width="36%">User</th>
      <th width="11%">AuthType</th>
      <th>NaturalPerson</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($users as $id => $vals): ?>
    <?php if (isset($vals['NaturalPerson']) && $person != $vals['NaturalPerson']): ?>
    <tr class="tabular_item oe_already_assigned">
      <td>&nbsp;</td>
      <td><?php echo $vals['Description'] ?></td>
      <td><?php echo $vals['AuthType'] ?></td>
      <td>
        <a href="#" target="_self" onclick="openPopup(this, 'catalogs', 'NaturalPersons', 'EditForm', {id: <?php echo $vals['NaturalPerson'] ?>}); return false;">
          <?php echo $plinks[$vals['NaturalPerson']]['text'] ?>
        </a>
      </td>
    </tr>
    <?php else: ?>
      <?php $value   = $id.' '.$vals['AuthType'].(isset($vals['LoginRecords']) ? ' '.$vals['LoginRecords'] : '') ?>
      <?php $checked = isset($vals['NaturalPerson']) && $person == $vals['NaturalPerson'] ? 'checked' : '' ?>
    <tr class="tabular_item">
      <td>
        <input type="checkbox" name="<?php echo $prefix.'[]' ?>" value="<?php echo $value ?>" <?php echo $checked ?> />
      </td>
      <td><?php echo $vals['Description'] ?></td>
      <td><?php echo $vals['AuthType'] ?></td>
      <td>
        <?php echo (isset($vals['NaturalPerson']) && $person == $vals['NaturalPerson']) ? '<i>current</i>' : '&nbsp;' ?>
      </td>
    </tr>
    <?php endif;?>
  <?php endforeach; ?>
  </tbody>
  </table>
</div>

<script type="text/javascript">
    
</script>

<style type="text/css">
   .oe_already_assigned {
      background-color: #F2F2F2;
   }
   .oe_already_assigned td {
      color: #808080 !important;
   }
</style>
