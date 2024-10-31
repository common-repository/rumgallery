<?php
set_time_limit(0);

if ($user_level < 6)
{
	die('No rights for this');
}

// handle post
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if (is_array($_POST['files']))
	{
		foreach ($_POST['files'] as $file)
		{
			gallery::ImportPath($file);
		}
	}
	?>
		<div class="updated">
			<p>Files imported...</p>
		</div>
	<?
}
$title = __('Gallery - Edit dir');
$parent_file = 'edit.php';

?>
<div class="wrap">
	<h2><?php _e('Gallery import'); ?></h2>
	<form method="post">
	<table width="100%" cellpadding="4">
	<?
		$path = gallery::GetSetting('ftp_dir');
		$i = 0;
		if (gallery::ParseCurrentDir(&$dirs, &$files, $path))
		{
			foreach($dirs as $dir)
			{
				printf('<tr class="%s">', ($i % 2 == 0) ? 'alternate' : '');
				printf('<td width="70"><input type="checkbox" name="files[]" value="%s"></td>', $dir->name);
				printf('<td><img src="../../wp-images/dir.gif" style="vertical-align: middle;"> %s</td>', $dir->name);
				print('</tr>');
				$i++;
			}
			foreach($files as $file)
			{
				printf('<tr class="%s">', ($i % 2 == 0) ? 'alternate' : '');
				printf('<td width="70"><input type="checkbox" name="files[]" value="%s"></td>', $file->name);
				printf('<td>%s</td>', $file->name);
				print('</tr>');
				$i++;
			}
		}

	?>
	</table>
	<p class="submit"><input type="submit" name="sub" value="<?php _e('Import selected directories and files...'); ?>"></p>
	</form>
</div>
