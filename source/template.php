<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>paste labs</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="imagetoolbar" content="no" />
<meta name="robots" content="index,follow" />
<meta name="author" content="Aaron Oxborrow" />
<meta name="description" http-equiv="description" content="Portfolio of Aaron Oxborrow, a freelance web developer based in Salt Lake City, Utah." />
<meta name="keywords" http-equiv="keywords" content="Aaron Oxborrow, Paste Labs, Paste Design, Freelance Web Developer, Design, Web Design, Graphic Design, Web Development, Code, Flash, ActionScript, XHTML, CSS, JavaScript, PHP, MySQL, Salt Lake City, Sale Lake, UT, Utah" />
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<script type="text/javascript" src="/assets/js/paste.js"></script>
<script type="text/javascript">
 <!--//--><![CDATA[//><!--
	var curr=0;
	var images = new Array('<? echo implode("','", $work_images); ?>');
	for (i=0; i<images.length; i++) {
		var preload = new Image();
		preload.src = images[i];
	}
	function next(){
		var next = curr+1;
		if (next >= images.length) { window.location.href = "<? echo $getNextProject; ?>";	} else { curr = next; swap(next); }
	}
	function prev(){
		var prev = curr-1;
		if (prev < 0) { window.location.href = "<? echo $getPrevProject; ?>"; } else {	curr = prev; swap(prev); }
	}
	function swap(id) {
		img = images[id];
		var folio_image = document.getElementById("folio_image");
		folio_image.src = img;
		curr = id;
		var cp_label = (curr+1)+" of <? echo $curr_work['numpics']; ?>";
		var cp = document.getElementById("currentpage");
		cp.firstChild.data = cp_label;
	}
//--><!]]>
</script>
<link rel="stylesheet" href="/assets/css/paste.css" type="text/css" />
<style type="text/css">
<?
	//$base = new CSS_Color('3d3734');
  	$base = new CSSColor($curr_work['color']); 
	$regcolor = $base->bg['0'];
	$lightcolor = $base->bg['+1'];
	$xlightcolor = $base->bg['+2'];
	$xxlightcolor = $base->bg['+3'];
	$darkcolor = $base->bg['-1'];
	$xdarkcolor = $base->bg['-3'];
	$xxdarkcolor = $base->bg['-4'];
?>
.folio_bg {
	background-color: #<? echo $regcolor; ?>;
}
.folio_dark_bg {
	background-color: #<? echo $darkcolor; ?>;
}
.folio_dark_border {	
	border-style: solid; 
	border-color: #<? echo $darkcolor; ?>; 
	border-width: 3px;
}
.folio_xdark_border {	
	border-style: solid; 
	border-color: #<? echo $xdarkcolor; ?>; 
	border-width: 3px;
}
.folio_bg a:hover img, .folio_xxdark_border {	
	border-style: solid; 
	border-color: #<? echo $xxdarkcolor; ?>; 
	border-width: 3px;
}
.prev_btn a {
	border-top: solid 1px #<? echo $xxdarkcolor; ?>;
	border-right: solid 1px #<? echo $xdarkcolor; ?>;
	border-bottom: solid 1px #<? echo $xdarkcolor; ?>;
}
.prev_btn a:hover {
	border-top: solid 1px #<? echo $darkcolor; ?>;
	border-right: solid 1px #<? echo $darkcolor; ?>;
	border-bottom: solid 1px #<? echo $darkcolor; ?>;
	background-color: #<? echo $lightcolor; ?>;
}
p#currentpage {
	color: #<? echo $xxlightcolor; ?>;
}
.folio_xlight {
	color: #<? echo $xlightcolor; ?>;
}
.folio_light {
	color: #<? echo $lightcolor; ?>;
}
.folio_light_bg {
	background-color: #<? echo $lightcolor; ?>;
}
.folio_xdark_bg {
	background-color: #<? echo $xdarkcolor; ?>;
}
.next_btn a {
	border-bottom: solid 1px #<? echo $xxdarkcolor; ?>;
}
.folio_xxdark_bg, .next_btn a:hover {
	background-color: #<? echo $xxdarkcolor; ?>;
	border-bottom: solid 1px #<? echo $xxdarkcolor; ?>;
}
</style>
</head>
<body>
	
	<div class="header"><a title="cover" href="./"><img src="/assets/images/paste_logo.gif" id="logo" alt="paste logo" width="66" height="126" /></a></div>
	<div class="menu" id="workmenu">
		<ul>
			<li class="folio_bg">WORK</li>
			<li id="infotab_off"><a title="Info" href="/info">INFO</a></li>
		</ul>
	</div>
	<div class="leftwing">
		<p class="intro"><span class="white">Hello, </span>this is <br/>the portfolio of <br/>Aaron Oxborrow.</p>
		<?php echo $drawProjects; ?>
	</div>
	<div class="page folio_bg">
		<div id="pagetop"></div>
		<div id="pagecontent">
			<a title="Next" href="javascript:next()" onmouseover="folio_over()" onmouseout="folio_out()">
				<img src="<? echo $work_images[0]; ?>" alt="click to advance" id="folio_image" class="folio_dark_bg folio_xdark_border" width="400" height="400" />
			</a>
			<div class="navigation">
				<? if ($curr_id != "cover") { ?><p id="currentpage">1 of <? echo $curr_work['numpics']; ?></p><? } ?>
				<p class="nav_btns">
					<span class="prev_btn"><a title="Previous" class="folio_dark_bg" id="prev" href="javascript:prev()">&nbsp;</a></span>
					<span class="next_btn"><a title="Next" id="next" class="folio_xdark_bg" onmouseover="swapClass('folio_image', 'folio_xxdark_border folio_dark_bg')" onmouseout="swapClass('folio_image', 'folio_xdark_border folio_dark_bg')" href="javascript:next()">NEXT</a></span>
				</p>
			</div>
		</div>
		<div id="pagebottom"></div>
	</div>
	<div class="rightwing">
		<h2><? echo $curr_work['title']; ?></h2>
		<p class="subtitle"><? echo $curr_work['subtitle']; ?></p>
		<? echo $curr_work['description']; ?>
		<? if (strlen($curr_work['launchurl']) > 1) { ?><p class="launch"><a title="View Project" href="<? echo $curr_work['launchurl']; ?>" rel="external"><? echo $curr_work['launchtext']; ?></a></p><? } ?>
	</div>
</body>
</html>
