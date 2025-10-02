	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width,initial-scale=1.0">
	<meta name="keywords" content="photography">
	<meta name="description" content="B L Moon Photography.">
	<meta name="author" content="B L Moon Photography.">
    <meta property="og:title" content="B L Moon Photography">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://www.blmoonenterprises.com/">
    <meta property="og:image" content="http://www.blmoonenterprises.com/common/img/ogp.jpg">
    <meta property="og:site_name" content="B L Moon Photography">
    <meta property="og:description" content="B L Moon Photography">
	<link rel="shortcut icon" href="/favicon.ico">
    <!-- apple link -->
	<link rel="stylesheet" href="/common/css/style.css">
    <!--Google Analytics-->
	<script src="/common/js/libs.js" type="text/javascript"></script>
	<script src="/common/js/common.js" type="text/javascript"></script>
	<link rel="stylesheet" href="/ue-scroll-js-master/dist/ue-scroll.min.css" type="text/css">

<!--	<script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    
      ga('create', 'UA-20499489-1', 'auto');
      ga('send', 'pageview');
    </script>
-->     
<script>
	function includeHTML() {
	  var z, i, elmnt, file, xhttp;
	  /*loop through a collection of all HTML elements:*/
	  z = document.getElementsByTagName("*");
	  for (i = 0; i < z.length; i++) {
		elmnt = z[i];
		/*search for elements with a certain atrribute:*/
		file = elmnt.getAttribute("w3-include-html");
		if (file) {
		  /*make an HTTP request using the attribute value as the file name:*/
		  xhttp = new XMLHttpRequest();
		  xhttp.onreadystatechange = function() {
			if (this.readyState == 4) {
			  if (this.status == 200) {elmnt.innerHTML = this.responseText;}
			  if (this.status == 404) {elmnt.innerHTML = "Page not found.";}
			  /*remove the attribute, and call this function once more:*/
			  elmnt.removeAttribute("w3-include-html");
			  includeHTML();
			}
		  }      
		  xhttp.open("GET", file, true);
		  xhttp.send();
		  /*exit the function:*/
		  return;
		}
	  }
	};
</script> 
