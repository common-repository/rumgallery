<?php 
/* Don't remove this line. */
require('./wp-blog-header.php');

?>
<html>
	<style>
	a.album img {
		border: 1px solid black;
	}
	a.album {
		border: none;
	}

	a.picture {
		
	}

	a.picture img {
		border: 1px solid black;
	}
	</style>
<script language="javascript" type="text/javascript">
	function popImg(path)
	{
		alert(path);
	}
</script>
</html>
<body style="margin: 2px;">
	<img src="wp-content/<? print GALLERY_DIRECTORY; ?>/<? print RawUrldecode($_GET['path']); ?>" alt="" align="center"/>
</body>
</html>
