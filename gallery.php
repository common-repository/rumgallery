<?php 
/* Don't remove this line. */
require('./wp-blog-header.php');

get_header();

?>
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
		var win = window.open('gallery-popup.php?path='+escape(path));
	}
</script>
<div id="content" class="narrowcolumn">
<div class="post">
	<? the_rum_gallery(); ?>
</div>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
