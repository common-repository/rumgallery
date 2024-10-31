<?php
set_time_limit(0);

// include_once('admin-header.php');
if ($user_level < 1)
{
	die('No rights for this');
}

if ($_GET['dir'])
{
	include('gallery-dir.php');
	return;
}

if ($_GET['pic'])
{
	include('gallery-picture.php');
	return;
}

if ($_GET['import'])
{
	include('gallery-import.php');
}

// handle post
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	switch($_POST['a'])
	{
		case 'dir':
			if (gallery::IsDir($_POST['dir_name']))
			{
				die('Directory allready exists');
			}
			else
			{
				if (gallery::MakeDir($_POST['dir_name']))
				{
					gallery::Redirect(gallery::Url('gallery.php', gallery::CurrentVirtualPath()));
					return;
				}
				else
				{
					die('Couldnt create directory, check file rights in directory ('. GALLERY_PATH .')');
				}
			}
			break;
				
		case 'upload':
			if (gallery::SavePicture($_FILES['picfile']))
			{
				gallery::Redirect(gallery::Url('gallery.php', gallery::CurrentVirtualPath()));
				return;
			}
			else
			{
				die('Couldnt save file, check file permissions');
			}
			break;
	}

}
$title = __('Gallery Fish');

?>
<script language="javascript">
	function doDel(path)
	{
		if (confirm('<?php _e('Sure you want to delete directory?'); ?>'))
		{
			self.location.href = path;
		}
	}
</script>
<style>
	#pictures td a img {
		padding: 3px;	
		border: 1px solid black;
	}
	#pictures td a {
		text-decoration: none;
		border: none;
	}

	#pictures td a img:hover {
		border-color: blue;
	}
</style>
<? if (gallery::HasBatchWaiting() && !isset($_GET['import'])) : ?>
	<div class="updated">
		<p><?php _e('Gallery have batch pictures waiting for import...'); ?></p>
		<p><a href="<?php print gallery::Url('gallery.php', gallery::CurrentEncodedPath(), array('import=1')); ?>"><?php _e('Click here to select and start recursive import in current directory.'); ?></a></p>
	</div>
<? endif; ?>
<div class="wrap">
	<?
		if (gallery::ParseCurrentDir($dirs, $files))
		{
			$crumbs = gallery::Crumbs();
			if (count($crumbs) > 0)
			{
				printf('<h2>%s (%s)</h2>',
					__('Browse pictures'),
					implode(' &gt; ', $crumbs));
			}
			else
			{
				printf('<h2>%s</h2>', __('Browse pictures'));
			}
			print('<table width="100%" cellpadding="3" cellspacing="3">');
			$i = 0;
			foreach ($dirs as $dir)
			{
				printf('<tr class="%s">',
					($i % 2 == 0) ? 'alternate': '');
				print('<td width="30"><img src="../wp-images/dir.gif" alt="Directory/Album"></td>');	
				printf('<td><a href="%s">%s</a></td>',
					gallery::Url('gallery.php', gallery::CurrentEncodedPath($dir->name)),
					$dir->name);
				printf('<td>%s&nbsp;</td>', $dir->desc);
				printf('<td>%s&nbsp;</td>', $dir->thumb);
				// admin.php?page=RumGallery/gallery-dir.php&path=%s
				printf('<td width="50"><a href="%s" class="edit">%s</a></td>', 
					gallery::Url('gallery.php', gallery::CurrentEncodedPath($dir->name), array('dir=1')),
					__('Edit'));
				printf('<td width="50"><a href="javascript:doDel(\'%s\');" class="delete">%s</a></td>', 
					gallery::Url('gallery.php', gallery::CurrentEncodedPath($dir->name), array('action=delete', 'dir=1')),
					__('Delete'));
				print('</tr>');
				$i++;
			}
			print('</table>');
			print('<table width="100%" cellpadding="3" cellspacing="3" id="pictures">');
			print('<tr>');
			$i = 1;
			$split = (int) gallery::getSetting('backend_split');;
			foreach ($files as $file)
			{
				printf('<td align="center"><a href="%s"><img src="../wp-content/%s/%s/.thumbs/%s" alt="%s"></a><br>%s<br>%.1f kb<br>%d x %d</td>',
					gallery::Url('gallery.php', gallery::CurrentVirtualPath() .'/'. $file->name, array('pic=1')),
					GALLERY_DIRECTORY,
					gallery::CurrentVirtualPath(),
					$file->name,
					$file->name,
					$file->name,
					$file->size / 1000,
					$file->width,
					$file->height);
				if ($i % $split == 0) print('</tr><tr>');
				$i++;
			}
			print('</tr>');
			print('</table>');
		}
		else
		{
			print('Couldnt open directory: (' . gallery::CurrentPath() . ')');
		}
	?>
</div>
<div class="wrap">
	<h2><?php _e('Create directory'); ?></h2>
	<form method="post">
		<input type="hidden" name="a" value="dir">
		<p><?php _e('Name:'); ?><br />
		<input type="text" name="dir_name" value="" /></p>
		<p><input type="submit" name="submit" value="<?php _e('Create directory'); ?>" class="search" /></p>
	</form>
</div>

<div class="wrap">
	<form method="post" enctype="multipart/form-data">
	<h2><?php _e('Upload new picture <i>(in current directory)</i>'); ?></h2>
	<input type="hidden" name="a" value="upload">
	<p><?php _e('File'); ?>:<br />
	<input type="file" name="picfile" value="" /></p>
	<p><input type="submit" name="submit" value="<?php _e('Upload picture'); ?>" class="search" /></p>
	</form>
</div>
 
