<?php
set_time_limit(0);

if ($user_level < 6)
{
	die('No rights for this');
}

// handle post
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	gallery::SetSetting('thumb_width', (int) $_POST['thumb']);
	gallery::SetSetting('image_width', (int) $_POST['image']);
	gallery::SetSetting('jpeg_quality', (int) $_POST['qual']);
	gallery::SetSetting('save_original', (int) $_POST['org']);
	gallery::SetSetting('front_split', (int) $_POST['front']);
	gallery::SetSetting('backend_split', (int) $_POST['back']);
	gallery::SetSetting('ftp_dir', $_POST['ftp']);
	gallery::SetSetting('pic_page', (int) $_POST['page']);
	gallery::SetSetting('order', (int) $_POST['order']);
	gallery::saveSettings();
	?>
		<div class="updated">
			<p>Settings updated</p>
		</div>
	<?
}
$title = __('Gallery - Edit dir');
$parent_file = 'edit.php';

?>
<div class="wrap">
	<h2><?php _e('Gallery settings'); ?></h2>
	<form method="post">
	<table width="100%" cellpadding="3" cellspacing="3" class="editform">
	<tr>
		<th width="33%" scope="row"><?php _e('Thumbnail width'); ?>:</th>
		<td width="67%">
			<input type="text" value="<? print gallery::GetSetting('thumb_width'); ?>" name="thumb" size="4">
		</td>
	</tr>
	<tr>
		<th width="33%" scope="row"><?php _e('Image width'); ?>:</th>
		<td width="67%">
			<input type="text" value="<? print gallery::GetSetting('image_width'); ?>" name="image" size="4">
		</td>
	</tr>
	<tr>
		<th width="33%" scope="row"><?php _e('JPEG Quality'); ?>:</th>
		<td width="67%">
			<input type="text" value="<? print gallery::GetSetting('jpeg_quality'); ?>" name="qual" size="4">
		</td>
	</tr>
	<tr>
		<th width="33%" scope="row"><?php _e('Save original'); ?>:</th>
		<td width="67%">
			<input type="checkbox" name="org" value="1" <?php print (gallery::GetSetting('save_original')) ? 'checked' : ''; ?>> (Not implemented yet)
		</td>
	</tr>
	<tr>
		<th width="33%" scope="row"><?php _e('Pictures per page at frontend'); ?>:</th>
		<td width="67%">
			<input type="text" value="<? print gallery::GetSetting('pic_page'); ?>" name="page" size="4">
		</td>
	</tr>
	<tr>
		<th width="33%" scope="row"><?php _e('Pictures per row at frontend'); ?>:</th>
		<td width="67%">
			<input type="text" value="<? print gallery::GetSetting('front_split'); ?>" name="front" size="4">
		</td>
	</tr>
	<tr>
		<th width="33%" scope="row"><?php _e('Pictures per row at backend'); ?>:</th>
		<td width="67%">
			<input type="text" value="<? print gallery::GetSetting('backend_split'); ?>" name="back" size="4">
		</td>
	</tr>
	<tr>
		<th width="33%" scope="row"><?php _e('Ftp batch directory'); ?>:</th>
		<td width="67%">
			<input type="text" value="<? print gallery::GetSetting('ftp_dir'); ?>" name="ftp" size="50">
		</td>
	</tr>
	<tr>
		<th width="33%" scope="row"><?php _e('Album order'); ?>:</th>
		<td width="67%">
			<select name="order">
				<option value="0" <? if (gallery::GetSetting('order') == 0) print 'SELECTED'; ?>><?php _e('Alphabetical'); ?></option>
				<option value="1" <? if (gallery::GetSetting('order') == 1) print 'SELECTED'; ?>><?php _e('Newest first'); ?></option>
			</select>
		</td>
	</tr>
	</table>
	<p class="submit"><input type="submit" name="sub" value="<?php _e('Save settings'); ?>"></p>
	</form>
</div>
