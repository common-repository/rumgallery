<?php
set_time_limit(0);

$picture = new gallery_picture('', gallery::CurrentPath());

// handle post
$title = __('Gallery - Edit dir');

if ($user_level < 1)
{
	die('No rights for this');
}
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$picture->desc = stripslashes($_POST['desc']);
	$picture->save();
	gallery::Redirect("admin.php?page=RumGallery/gallery.php&path=" . substr($picture->getCurrentVirtualPath(),0,-1));
	return;
}
if ($_GET['delete'] == '1')
{
	$picture->delete();
	// header("location: gallery.php?path=" . substr($picture->getCurrentVirtualPath(),0,-1));
	gallery::Redirect(gallery::Url('gallery.php', substr($picture->getCurrentVirtualPath(), 0, -1)));
	return;
}
?>
<script type="text/javascript">
	function doDel()
	{
		if (confirm('<?php _e('Are you complete sure you want to delete this image?'); ?>'))
		{
			self.location.href='<? print gallery::Url('gallery-picture.php', gallery::CurrentVirtualPath(), array('delete=1')); ?>';
		}
	}
</script>
<div class="wrap">
	<h2><?php _e('Edit'); ?>: <? print implode(' &gt; ', gallery::Crumbs()); ?></h2>
	<form method="post">
	<table width="100%" cellpadding="3" cellspacing="3" class="editform">
	<tr>
		<th width="33%" scope="row"><?php _e('Thumbnail'); ?>:</th>
		<td width="67%">
			<img src="../wp-content/<? print GALLERY_DIRECTORY; ?>/<? print $picture->getCurrentVirtualPath(); ?>/.thumbs/<? print $picture->name; ?>" alt="Thumb" style="border-style: solid; border-width: 1px;"/>
		</td>
	</tr>
	<tr>
		<th width="33%" scope="row"><?php _e('Description'); ?>:</th>
		<td width="67%">
			<input type="text" value="<? print $picture->desc; ?>" name="desc" style="width: 95%;">
		</td>
	</tr>
	</table>
	<p class="submit">
		<input type="button" name="delete" value="<?php _e('Delete picture'); ?>" style="color: red;" onclick="doDel()">
		<input type="submit" name="sub" value="<?php _e('Save information'); ?>">
	</p>
	</form>
</div>
