function swapClass(id, newClass) {
	document.getElementById(id).className = newClass;
}
function folio_over() {
	swapClass('next', 'folio_xxdark_bg');
	swapClass('folio_image', 'folio_xxdark_border folio_dark_bg');
}
function folio_out() {
	swapClass('next', 'folio_xdark_bg');
	swapClass('folio_image', 'folio_xdark_border folio_dark_bg');
}
function externalLinks() {
 if (!document.getElementsByTagName) return;
 var anchors = document.getElementsByTagName("a");
 for (var i=0; i<anchors.length; i++) {
   var anchor = anchors[i];
   if (anchor.getAttribute("href") &&
       anchor.getAttribute("rel") == "external")
     anchor.target = "_blank";
 }
}
window.onload = externalLinks;

var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-17597147-1']);
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
