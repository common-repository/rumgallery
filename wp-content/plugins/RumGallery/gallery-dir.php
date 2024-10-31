<?php
set_time_limit(0);

if ($user_level < 1)
{
	die('No rights for this');
}

$album = new gallery_album('', gallery::CurrentPath());

if ($_GET['action'] == 'delete')
{
	gallery::DeleteRecursive(gallery::CurrentPath());
	gallery::Redirect(gallery::Url('gallery.php', ''));
	return;
}

// handle post
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$album->thumb = $_POST['thumb'];
	$album->desc = stripslashes($_POST['desc']);
	$album->save();
	gallery::Redirect(gallery::Url('gallery.php', gallery::CurrentVirtualPath()));
	return;
}
$title = __('Gallery - Edit dir');

?>
<div class="wrap">
	<h2><?php _e('Edit'); ?>: <? print implode(' &gt; ', gallery::Crumbs()); ?></h2>
	<form method="post">
	<table width="100%" cellpadding="3" cellspacing="3" class="editform">
	<tr>
		<th width="33%" scope="row"><?php _e('Description'); ?>:</th>
		<td width="67%">
			<input type="text" value="<? print $album->desc; ?>" name="desc" style="width: 95%;">
		</td>
	</tr>
	<tr>
		<th width="33%" scope="row"><?php _e('Thumbnail'); ?>:</th>
		<td>
		<?
			if (gallery::ParseCurrentDir($dirs, $files))
			{
				if (count($files) > 0)
				{
					print('<select name="thumb">');
					foreach ($files as $file)
					{
						printf('<option value="%s" %s>%s</option>',
							$file->name,
							($file->name == $album->thumb) ? 'selected' : '',
							$file->name);
					}
					print('</select>');
				}
			}
			else
			{
				print('Couldnt open directory: (' . gallery::CurrentPath() . ')');
			}
		?>
		</td>
	</tr>
	</table>
	<p class="submit"><input type="submit" name="sub" value="<?php _e('Save information'); ?>"></p>
	</form>
</div>
