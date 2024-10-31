<?php

function rum_gallery_is_root()
{
	$path = Gallery::CurrentPath();
	if (!is_dir($path)) $path = dirname($path);
	return ($path == GALLERY_PATH);
}

function rum_gallery_parent_album_list($before='<li>', $after='</li>')
{
	$path = Gallery::CurrentPath();
	if (!is_dir($path)) { 
		$path = dirname($path);
	}
	$path = dirname($path);
	$str = sprintf('%s<a href="gallery.php?path=%s">%s</a>%s',
		$before,
		Gallery::GetVirtualPath($path),
		_('Parent ..'),
		$after);
	if (Gallery::ParseCurrentDir($dirs, $files, $path))
	{
		foreach ($dirs as $dir)
		{
			$str .= sprintf('%s<a href="gallery.php?path=%s">%s</a>%s',
				$before,
				Gallery::GetVirtualPath($path.'/'.$dir->name),
				$dir->name,
				$after);
		}
	}
	print $str;
}

function rum_gallery_random_picture($amount=1)
{

}

function rum_gallery_latest($amount=5)
{

}

function the_rum_gallery($path='')
{
	$GLOBALS['_rum_gallery'] = true;
	$page_title = __('Gallery');
	$crumbs = gallery::Crumbs();
	print('<h2>');
	printf('<a href="gallery.php">%s</a>',
		$page_title);
	$path = '';
	foreach ($crumbs as $crumb)
	{
		$path .= '/'.$crumb;
		print(' &#187; ');
		printf('<a href="gallery.php?path=%s">%s</a>',
			$path,
			$crumb);
	}
	print('</h2>');

	if (gallery::ParseCurrentDir($albums, $files))
	{
		print('<table width="100%" cellpadding="2">');
		foreach($albums as $album)
		{
			if ($album->pictures > 0)
			{
				print('<tr>');
				print('<td>');
				if ($album->hasThumb())
				{
					printf('<a href="gallery.php?path=%s" class="album"><img src="wp-content/%s" alt="%s"></a>',
						gallery::CurrentVirtualPath() . '/' . $album->name,
						GALLERY_DIRECTORY . $album->getThumbUrl(),
						$album->desc);
				}
				else
				{
					printf('<a href="gallery.php?path=%s" class="album"><span style="width: %dpx; height: %dpx; border: 1px solid black;"></span></a>',
						gallery::CurrentVirtualPath() . '/' . $album->name,
						GALLERY_THUMB_WIDTH,
						GALLERY_THUMB_WIDTH * 0.75);
				}
				print('</td>');
				printf('<td valign="top">%s: %d<br><br>%s</td>',
					__('Pictures in album'),
					$album->pictures,
					$album->desc);
				print('</tr>');
			}
		}
		print('</table>');
		print('<br>');
		$i = 0;
		$split = (int) gallery::getSetting('front_split');
		$pics_per_page = (int) gallery::getSetting('pic_page');
		$offset = (int) $_GET['page'] * $pics_per_page;
		$page = (int) $_GET['page'] + 1;
		$pages = 0;
		if ($pics_per_page > 0)
		{
			$pages = ceil(count($files) / $pics_per_page);
		}
		if ($pages > 1)
		{
			print('<table width="100%" cellpadding="2" id="GalleryPageTitle">');
			print('<tr>');
			print('<td width="25%">');
			if ($page > 1)
			{
				printf('<a href="gallery.php?path=%s&page=%d">&#171; %s</a>',
					gallery::CurrentVirtualPath(),
					$page - 2,
					__('Previous'));
			}
			printf('</td><td align="center" width="50%%">%s %d %s %d</td>',
				__('Page'),
				$page,
				__('of'),
				$pages);
			print('<td align="right" width="25%">');
			if ($page < $pages)
			{
				printf('<a href="gallery.php?path=%s&page=%d">%s &#187;</a>',
					gallery::CurrentVirtualPath(),
					$page,
					__('Next'));
			}
			print('</td></tr>');
			print('</table>');
		}
		print('<table width="100%" cellpadding="2">');
		foreach($files as $file)
		{
			$draw = false;
			if ($pics_per_page > 0)
			{
				// print("$i - $offset - $pics_per_page<br>");
				if ($i >= $offset && $i < $offset+$pics_per_page)
				{
					$draw = true;
				}
			}
			else
			{
				$draw = true;
			}
			$i++;
			if ($draw)
			{
				printf('<td align="center"><a href="gallery.php?path=%s" class="picture"><img src="wp-content/%s/%s" alt="%s"></a></td>',
					gallery::CurrentVirtualPath() . '/' . $file->name,
					GALLERY_DIRECTORY,
					gallery::CurrentVirtualPath() . '/.thumbs/' . $file->name,
					$file->desc);
				if ($i % $split == 0 && $i > 0) print('</tr><tr>');
			}
		}
		print('</table>');
		if ($pages > 1)
		{
			printf('<div id="GalleryPages">%s:', __('Pages'));
			for ($i=0; $i<$pages; $i++)
			{
				if ($i == (int) $_GET['page'])
				{
					printf(' <a href="gallery.php?path=%s&page=%d" class="active">%d</a>',
						gallery::CurrentVirtualPath(),
						$i,
						$i+1);
				}
				else
				{
					printf(' <a href="gallery.php?path=%s&page=%d">%d</a>',
						gallery::CurrentVirtualPath(),
						$i,
						$i+1);
				}
			}
			print('</div>');

		}
	}
	else
	{
		$file = new gallery_picture('', gallery::CurrentPath());
		if ($file->isFound())
		{
			$files = array();
			$albums = array();
			if (gallery::ParseCurrentDir($albums, $files, $file->getCurrentDirPath()))
			{
				print('<table width="100%" celpadding="4">');
				print('<tr>');
				$prev = null;
				$next = null;
				for($i=0; $i<count($files); $i++)
				{
					if ($files[$i]->name == $file->name)
					{
						if ($i > 0)
						{
							$prev = $files[$i-1];
						}
						if ($i+1 < count($files))
						{
							$next = $files[$i+1];
						}
						break;
					}
				}
				if ($prev)
				{
					printf('<td><a href="gallery.php?path=%s">%s</td>',
						$file->getcurrentvirtualpath() . $prev->name,
						'&#171; ' . __('Previous'));
				}
				if ($next)
				{
					printf('<td align="right"><a href="gallery.php?path=%s">%s</td>',
						$file->getCurrentVirtualPath() . $next->name,
						__('Next') . ' &#187;');
				}
				print('</tr>');
				print('</table>');
			}
			print('<br>');
			printf('<div align="center"><img src="wp-content/%s/%s" alt="%s" border="1"/></div>',
				GALLERY_DIRECTORY,
				gallery::CurrentVirtualPath(),
				$file->name);
			printf('<div align="center">%s</div><br>', $file->desc);
			print('<table width="100%" cellpadding"4">');
			printf('<tr><td width="50%%" align="right">%s:</td><td width="50%%">%d x %d</td></tr>',
				__('Dimensions'),
				$file->width,
				$file->height);
			printf('<tr><td width="50%%" align="right">%s:</td><td width="50%%">%.1f kb</td></tr>',
				__('Filesize'),
				$file->size/1000);
			printf('<tr><td width="50%%" align="right">%s:</td><td width="50%%">%s</td></tr>',
				__('Filename'),
				$file->name);
			if ($file->hasOriginal())
			{
				printf('<tr><td width="50%%" align="right">%s:</td><td width="50%%">%d x %d</td></tr>',
					__('Original dimensions'),
					$file->orgwidth,
					$file->orgheight);
				printf('<tr><td width="50%%" align="right">%s:</td><td width="50%%">%.1f kb</td></tr>',
					__('Original filesize'),
					$file->orgsize/1000);
				printf('<tr><td align="center" colspan="2"><a href="javascript:popImg(\'%s\');">%s</a></td></tr>',
					$file->getCurrentVirtualPath().'.originals/'.$file->name,
					__('View original'));
			}
			print('</table>');
		}
		else
		{
			print('Couldnt open directory or file');
		}
	}
}

?>
