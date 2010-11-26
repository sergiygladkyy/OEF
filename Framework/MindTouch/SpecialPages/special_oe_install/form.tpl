<?php if ($isInstalled): ?>
   <input type="button" name="remove" value="Remove" onclick="javascript: if (confirm('Remove this configuration?')) {location='<?php echo $base_url ?>?actions=remove'; return true;} else {return false;}" />&nbsp;
   <input type="button" name="update_modules" value="Update Modules" onclick="location='<?php echo $base_url ?>?actions=update_modules';" />&nbsp;
   <input type="button" name="update_templates" value="Update Templates" onclick="location='<?php echo $base_url ?>?actions=update_templates';" />
<?php else: ?>
   <input type="button" name="install" value="install" onclick="location='<?php echo $base_url ?>?actions=install';" />
<?php endif; ?>
