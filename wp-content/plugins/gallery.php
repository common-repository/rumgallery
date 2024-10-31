<?php
/*
Plugin Name: RumGallery

Plugin URI: http://www.rummanddan.dk/plugins/
Description: Ultra simple file based gallery.
Version: 0.03
Author: Dan Thrue
Author URI: http://www.rummanddan.dk
*/ 

include_once(dirname(__FILE__) . '/RumGallery/gallery-html.php');

define('GALLERY_DIRECTORY', 'gallery');

define('GALLERY_FTP', 'false');
define('GALLERY_FTP_HOST', '');
define('GALLERY_FTP_USER', '');
define('GALLERY_FTP_PASS', '');
define('GALLERY_FTP_KEY', 'SomeSecretKey');

define('GALLERY_PATH', ABSPATH . 'wp-content/' . GALLERY_DIRECTORY);
define('GALLERY_EXCLUDE', '.;..;.thumbs;.album;.info;.files;.settings;.originals;.files');

function AddGalleryOptionPage()
{
	if (function_exists('add_options_page'))
	{
		add_options_page(__("Rum Gallery options"), __('Gallery'), 8, 'RumGallery/gallery-settings.php');
	}
}

function AddGalleryManagePage()
{
	if (function_exists('add_management_page'))
	{
		add_management_page(__("Rum Gallery"), __('Gallery'), 1, 'RumGallery/gallery.php');
	}
}

$_default_settings = array(
	'thumb_width'	=>	'120',
	'image_width'	=>	'400',
	'jpeg_quality'	=>	'85',
	'save_original'	=>	'0',
	'front_split'	=>	'3',
	'backend_split'	=>	'5',
	'ftp_dir'	=>	ABSPATH.'wp-content/ftp-gallery',
	'order'		=>	'0',
	'pic_page'	=>	'0'
);


// GALLERY HACK
if (substr($wp_version, 0, 3) != '1.2')
{
	add_filter('the_content', 'GalleryPost');
	add_action('admin_menu', 'AddGalleryOptionPage');
	add_action('admin_menu', 'AddGalleryManagePage');
}
else
{
	GalleryInstall();
}

if (basename($_SERVER['SCRIPT_NAME']) == 'plugins.php' && isset($_GET['activate']))
{
	GalleryInstall();
}

class gallery
{
	
	function getSetting($key)
	{
		gallery::loadSettings();
		return $GLOBALS['_gallery_settings'][$key];
	}

	function setSetting($key, $value)
	{
		$GLOBALS['_gallery_settings'][$key] = $value;
	}

	function saveSettings()
	{
		$content = "[GALLERY SETTINGS]\n";
		foreach ($GLOBALS['_gallery_settings'] as $key => $value)
		{
			$content .= "$key=$value\n";
		}
		$file = GALLERY_PATH . '/.settings';
		if ($fp = fopen($file, 'w'))
		{
			fwrite($fp, $content);
			fclose($fp);
			chmod($file, 0777);
		}
		else
		{
			die('Couldnt open ('.$file.') for writing check file permissions');
		}
	}

	function GetCurrentStamp($file)
	{
		$files =& $GLOBALS['_gallery_files'];
		if (!is_array($files))
		{
			$files = gallery_album::getFileList(gallery::CurrentPath().'/.files');
		}
		if (isset($files[$file]))
		{
			return (int) $files[$file];
		}
		return 0;
	}

	function loadSettings()
	{
		if (!isset($GLOBALS['_gallery_settings']))
		{
			$file = GALLERY_PATH . '/.settings';
			if (file_exists($file))
			{
				$GLOBALS['_gallery_settings'] = parse_ini_file($file);
				if (count($GLOBALS['_gallery_settings']) == 0)
				{
					$GLOBALS['_gallery_settings'] = $GLOBALS['_default_settings'];
				}
			}
			else
			{
				$GLOBALS['_gallery_settings'] = $GLOBALS['_default_settings'];
			}
		}
	}

	// STATIC METHODS
	function CurrentPath()
	{
		if (!defined('_GALLERY_CURRENTPATH'))
		{
			$path = rawurldecode($_GET['path']);
			while (strpos($path, '..') !== false)
			{
				$path = str_replace('..', '.', $path);
			}
			if (strlen($path) > 0)
			{
				define('_GALLERY_CURRENTPATH', GALLERY_PATH . $path);
			}
			else
			{
				define('_GALLERY_CURRENTPATH', GALLERY_PATH);
			}
		}
		return _GALLERY_CURRENTPATH;
	}

	function CurrentVirtualPath($dir='')
	{
		$path = gallery::CurrentPath();
		if (strlen($dir) > 0)
		{
			$path .= '/'.$dir;
		}
		$path = str_replace(GALLERY_PATH, '', $path);
		return $path;

	}

	function GetVirtualPath($path)
	{
		$path = str_replace(GALLERY_PATH, '', $path);
		return $path;
	}

	function CurrentEncodedPath($dir='')
	{
		$path = gallery::CurrentVirtualPath($dir);
		return rawurlencode($path);
	}

	function GetValidName($name)
	{
		$name = strtolower($name);
		$name = str_replace(' ', '_', $name);
		return $name;
	}

	function IsDir($dir, $path='')
	{
		if ($path == '')
		{
			$path = gallery::CurrentPath();
		}
		return is_dir($path .'/'. gallery::GetValidName($dir));
	}

	function MakeDir($dir)
	{
		$path = gallery::CurrentPath();
		$path .= '/' . gallery::GetValidName($dir);
		return mkdir($path, 0777);
	}

	function SaveImportedPicture($path, $dest='')
	{
		if (strlen($dest) == 0)
		{
			$dest = gallery::CurrentPath();
		}
		$full_path = $dest . '/' . gallery::GetValidName(basename($path));
		$thumb_path = $dest . '/.thumbs/' . gallery::GetValidName(basename($path));
		if (!is_dir($dest . '/.thumbs'))
		{
			if (!@mkdir($dest . '/.thumbs', 0777));
			{
				die('Couldnt create .thumbs directory...');
			}
		}
		$picture = new picture($path);
		$picture->SaveResized(gallery::getSetting('thumb_width'), $thumb_path);
		$picture->SaveResized(gallery::getSetting('image_width'), $full_path);
		$picture->Close();
		if (gallery::getSetting('save_original'))
		{
			$org_file = $dest . '/.originals';
			if (!is_dir($org_file))
			{
				if (!@mkdir($org_file))
				{
					die('Couldnt create originals directory');
				}
			}
			$org_file = $dest . '/.originals/' . gallery::GetValidName(basename($path));
			rename($path, $org_file);
		}
		else
		{
			unlink($path);
		}
		$album = new gallery_album('', $dest);
		if (!$album->hasThumb())
		{
			$album->thumb = gallery::GetValidName(basename($path));
		}
		if (gallery::ParseCurrentDir($dirs, $files, $dest))
		{
			$album->pictures = count($files);
			$album->refreshFiles($files);
		}
		$album->save();
		return true;
	}

	function SavePicture($file)
	{
		if (is_uploaded_file($file['tmp_name']))
		{
			$full_path = gallery::CurrentPath() . '/' . gallery::GetValidName($file['name']);
			$thumb_path = gallery::CurrentPath() . '/.thumbs/' . gallery::GetValidName($file['name']);
			if (!is_dir(gallery::CurrentPath() . '/.thumbs'))
			{
				mkdir(gallery::CurrentPath() . '/.thumbs', 0777);
			}
			$picture = new picture($file['tmp_name']);
			$picture->SaveResized(gallery::getSetting('thumb_width'), $thumb_path);
			$picture->SaveResized(gallery::getSetting('image_width'), $full_path);
			$picture->Close();
			if (gallery::getSetting('save_original'))
			{
				$org_file = gallery::CurrentPath() . '/.originals';
				if (!is_dir($org_file))
				{
					if (!@mkdir($org_file))
					{
						die('Couldnt create originals directory');
					}
				}
				$org_file = gallery::CurrentPath() . '/.originals/' . $file['name'];
				rename($file['tmp_name'], $org_file);
			}
			// check .album stuff...
			$album = new gallery_album('', gallery::CurrentPath());
			if (!$album->hasThumb())
			{
				$album->thumb = gallery::GetValidName($file['name']);
			}
			if (gallery::ParseCurrentDir($dirs, $files))
			{
				$album->pictures = count($files);
				$album->refreshFiles($files);
			}
			$album->save();
			return true;	
		}
		return false;
	}

	function ParseCurrentDir(&$dirs, &$files, $path='')
	{
		$dirs = array();
		$files = array();
		$exclude = explode(';', GALLERY_EXCLUDE);
		if ($path == '')
		{
			$path = gallery::CurrentPath();
		}
		if (is_dir($path))
		{
			if ($dir = opendir($path))
			{
				while ($file = readdir($dir))
				{
					if (!in_array($file, $exclude))
					{
						if (gallery::IsDir($file, $path))
						{
							$dirs[] = new gallery_album($file);
						}
						else
						{
							$files[] = new gallery_picture($file);
						}
					}
				}
				closedir($dir);
				if (count($files) > 0)
				{
					if (gallery::GetSetting('order') > 0)
					{
						usort($files, 'GallerySpecialComparer');
					}
					else
					{
						usort($files, 'GalleryComparer');
					}
				}
				if (count($dirs) > 0)
				{
					usort($dirs, 'GalleryComparer');
				}
				return 1;
			}
			return 0;
		}
		return 0;
	}

	function IsDirEmpty($path)
	{
		if (gallery::ParseCurrentDir($dirs, $files, $path))
		{
			$amount = count($dirs) + count($files);
			return ($amount == 0);
		}
		return false;
	}

	function ImportPath($src, $dest='', $verbose=false)
	{
		$batch_dir = gallery::GetSetting('ftp_dir');
		if (strlen($dest) == 0)
		{
			$dest = gallery::CurrentPath();
		}
		$src_path = $batch_dir.'/'.$src;
		$dest_path = $dest.'/'.$src;
		if (is_dir($src_path))
		{
			if (!is_dir($dest_path))
			{
				if (@mkdir($dest_path, 0777));
				{
					if ($verbose) print 'Created directory ('.$dest_path.')<br>';
					print "DIR";
				}

			}
			if ($dir = opendir($src_path))
			{
				while ($file = readdir($dir))
				{
					if ($file != '.' && $file != '..' && substr(strtolower($file), -3) != 'gif')
					{
						Gallery::ImportPath($src.'/'.$file, $dest);
					}
				}
				closedir($dir);
			}
			if (Gallery::IsDirEmpty($src_path))
			{
				rmdir($src_path);
			}
		}
		else
		{
			print 'importing ' . $src_path . ' to ' . $dest_path . ' <br> ';
			if (!gallery::SaveImportedPicture($src_path, dirname($dest_path)))
			{
				die('Couldnt save imported picture... check permissions');
			}
		}

	}

	function HasBatchWaiting()
	{
		$path = Gallery::GetSetting('ftp_dir');
		if (is_dir($path))
		{
			$count = 0;
			if ($dir = opendir($path))
			{
				while ($file = readdir($dir))
				{	
					if ($file != '.' && $file != '..')
					{
						$count++;
					}
				}
				closedir($dir);
			}
			return ($count > 0);
		}
		return false;
	}

	function DeleteRecursive($path)
	{
		if (is_dir($path))
		{
			if ($dir = opendir($path))
			{
				while ($file = readdir($dir))
				{	
					if ($file != '.' && $file != '..')
					{
						if (is_dir($path.'/'.$file))
						{
							gallery::DeleteRecursive($path.'/'.$file);
						}
						else
						{
							// print "about to delete $path/$file<br>";
							unlink($path . '/' . $file);
						}
					}
				}
				// print "about to delete $path<br>";
				rmdir($path);
				closedir($dir);
			}
		}
	}

	function Crumbs()
	{
		$arr = explode('/', gallery::CurrentVirtualPath());
		if (in_array('', $arr))
		{
			$indexes = array();
			for($i=0; $i<count($arr); $i++)
			{
				if ($arr[$i] == '')
				{
					$indexes[] = $i;
				}
			}
			foreach($indexes as $index)
			{
				unset($arr[$index]);
			}
		}
		return $arr;
	}

	function Url($file, $path, $params=array())
	{
		$url = '';
		$str = '';
		$version = substr($GLOBALS['wp_version'], 0, 3);
		if (count($params) > 0)
		{
			$str = implode('&', $params);
			if (strlen($str) > 0)
			{
				$str = '&'.$str;
			}
		}
		switch ($version)
		{
			case '1.3':
			default:
				$url = sprintf('admin.php?page=RumGallery/%s&path=%s%s',
					$file,
					$path,
					$str);
					
		}
		return $url;
	}

	function Redirect($url)
	{
		if (headers_sent())
		{
			?>
			<script language="javascript" type="text/javascript">self.location.replace('<?php print $url; ?>');</script>
			<?
			return;
		}
		else
		{
			header('location: '.$url);
		}
	}
}

class gallery_album
{
	var $thumb;
	var $name;
	var $desc;
	var $pictures = 0;
	var $_file;

	function gallery_album($name, $path='')
	{
		$this->name = $name;
		
		$this->_file = gallery::CurrentPath() . '/' . $name . '/.album';
		if ($path != '')
		{
			$this->_file = $path . '/.album';
		}
		if (file_exists($this->_file))
		{
			$a = explode("\n", file_get_contents($this->_file));
			$this->thumb = $a[0];
			$this->pictures = $a[1];
			$this->desc = $a[2];
		}
	}

	function hasThumb()
	{
		return strlen($this->thumb) > 0;
	}

	function getThumbUrl()
	{
		return gallery::CurrentVirtualPath() . '/' . $this->name . '/.thumbs/'. $this->thumb;
	}

	function refreshFiles($files)
	{
		$src = str_replace('.album', '.files', $this->_file);
		$list = gallery_album::getFileList($src);
		foreach ($files as $file)
		{
			if (!isset($list[$file->name]))
			{
				$list[$file->name] = time();
			}
		}
		$str = '';
		foreach($list as $filename => $timestamp)
		{
			$str .= sprintf("%s:%s\n", $filename, $timestamp);
		}
		$fp = fopen($src, 'w');
		fwrite($fp, $str);
		fclose($fp);
		@chmod($src, 0777);
	}

	function getFileList($file)
	{
		$files = array();
		if (file_exists($file))
		{
			$lines = explode("\n", file_get_contents($file));
			foreach ($lines as $line)
			{
				if (strlen($line) > 0)
				{
					list($file, $timestamp) = explode(':', trim($line));
					$files[$file] = $timestamp;
				}
			}
		}
		return $files;
	}

	function save()
	{
		$str = $this->thumb . "\n";
		$str .= $this->pictures . "\n";
		$str .= $this->desc . "\n";
		$fp = fopen($this->_file, 'w');
		fwrite($fp, $str);
		fclose($fp);
		chmod($this->_file, 0777);
	}
}

class gallery_picture
{
	var $name;
	var $desc;
	var $width;
	var $height;
	var $size;
	var $found = false;

	var $_file;
	var $_picture;
	var $_original = false;
	var $_original_parsed = false;
	var $path;

	function gallery_picture($name, $path='')
	{
		if (strlen($path) == 0)
		{
			$path = gallery::CurrentPath() .'/'. $name;
		}
		if (file_exists($path))
		{
			$a = getimagesize($path);
			$this->width = $a[0];
			$this->height = $a[1];
			$this->size = filesize($path);
			$this->found = true;
		}
		$this->_picture = $path;
		$pos = strrpos($path, '/') + 1;
		$this->name = substr($path, $pos);
		$this->path = substr($path, 0, $pos);
		$this->_file = $this->path . '.info/' . $this->name .'.txt';
		if (file_exists($this->_file))
		{
			$a = explode("\n", file_get_contents($this->_file));
			$this->desc = $a[0];
		}
	}

	function isFound()
	{
		return $this->found;
	}

	function hasOriginal()
	{
		if (!$this->_original_parsed)
		{
			$org = $this->path.'.originals/'.$this->name;
			if (file_exists($org))
			{
				$a = getimagesize($org);
				$this->orgwidth = $a[0];
				$this->orgheight = $a[1];
				$this->orgsize = filesize($org);
				$this->_original = true;
			}
		}
		return $this->_original;
	}

	function getCurrentDirPath()
	{
		return $this->path;
	}

	function getCurrentVirtualPath()
	{
		return str_replace(GALLERY_PATH, '', $this->path);
	}

	function save()
	{
		$str = $this->desc . "\n";
		if (!is_dir($this->path . '.info'))
		{
			mkdir($this->path . '.info', 0777);
		}
		$fp = fopen($this->_file, 'w');
		fwrite($fp, $str);
		fclose($fp);
		chmod($this->_file, 0777);
	}

	function delete()
	{
		if (file_exists($this->_file))
		{
			unlink($this->_file);
		}
		unlink($this->_picture);
		unlink($this->path . '.thumbs/' . $this->name);
	}
}

class picture
{
	var $mime;
	var $height;
	var $width;
	var $resource;

	function picture($file)
	{
		if (file_exists($file))
		{
			$a = getimagesize($file);
			$this->width = $a[0];
			$this->height = $a[1];
			$this->mime = $a['mime'];
			switch ($this->mime)
			{
				default:
					$this->resource = imagecreatefromjpeg($file);
					break;
			}
		}
	}

	function Scale($w,$h)
	{
		$resource = imagecreatetruecolor ($w, $h);
		imagecopyresampled($resource, $this->resource, 0, 0, 0, 0, $w, $h, $this->width, $this->height);
		return $resource;
	}

	function ScaleByWidth($w)
	{
		$w = (int) $w;
		$h = ($w / $this->width) * $this->height;
		return $this->Scale($w, $h);
	}

	function SaveResized($width, $target)
	{
		$resource = $this->ScaleByWidth($width);
		imagejpeg($resource, $target, gallery::getSetting('jpeg_quality'));
		imagedestroy($resource);
		chmod($target, 0777);
	}

	function Close()
	{
		imagedestroy($this->resource);
	}
}

function GalleryComparer($obj1, $obj2)
{
	return strcmp($obj1->name, $obj2->name);
}

function GallerySpecialComparer($obj1, $obj2)
{
	$t1 = gallery::GetCurrentStamp($obj1->name);
	$t2 = gallery::GetCurrentStamp($obj2->name);
	if ($t1 == $t2)
	{
		return strcmp($obj1, $obj2);
	}
	else
	{
		return ($t1 < $t2);
	}
}

function GalleryPost($text)
{
	$ptn = "/(<gallery(.*?)[^>]>)/";
	if (preg_match_all($ptn, $text, $matches, PREG_PATTERN_ORDER))
	{
		/*
		print "<pre>";
		var_dump($matches);
		print "</pre>";
		*/
	}
	return $text;
}


function GalleryInstall()
{
	if (!is_dir(GALLERY_PATH))
	{
		if (!@mkdir(GALLERY_PATH, 0777))
		{
			die('COULDNT CREATE GALLERY PICTURE, CHECK PERMISSIONS IN wp-content DIRECTORY');
		}
	}
}

function rum_is_gallery()
{
	if (basename($_SERVER['SCRIPT_FILENAME']) == 'gallery.php') {
		return $GLOBALS['_rum_gallery'];
	}
	return false;
}

?>
