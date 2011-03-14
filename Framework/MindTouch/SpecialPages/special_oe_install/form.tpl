<h3><?php echo $value ?></h3>
<?php if ($isInstalled): ?>
   <input type="button" name="update" value="Update" onclick="location='<?php echo $base_url ?>?actions=update&appliedSolutionName=<?php echo $value ?>';" />&nbsp;
   <input type="button" name="remove" value="Remove" onclick="javascript: if (confirm('Remove this configuration?')) {location='<?php echo $base_url ?>?actions=remove&appliedSolutionName=<?php echo $value ?>'; return true;} else {return false;}" />&nbsp;
   <input type="button" name="update_modules" value="Update Modules" onclick="location='<?php echo $base_url ?>?actions=update_modules&appliedSolutionName=<?php echo $value ?>';" />&nbsp;
   <input type="button" name="update_templates" value="Update Templates" onclick="location='<?php echo $base_url ?>?actions=update_templates&appliedSolutionName=<?php echo $value ?>';" />
<?php else: ?>
   <input type="button" name="install" value="install" onclick="location='<?php echo $base_url ?>?actions=install&appliedSolutionName=<?php echo $value ?>';" />
<?php endif; ?>
</br>