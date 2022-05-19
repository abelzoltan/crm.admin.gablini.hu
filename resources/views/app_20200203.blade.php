<?php
error_reporting(E_ALL ^ E_NOTICE);
if(isset($VIEW["vars"]) AND !empty($VIEW["vars"])) { foreach($VIEW["vars"] AS $key => $val) { $$key = $val; } }
?>
<!DOCTYPE html>
<html lang="hu-HU">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="language" content="hu_hu">
	<meta name="country" content="hu">
	<meta name="distribution" content="global">	
	<meta name="theme-color" content="#333f4b">
	<meta name="msapplication-navbutton-color" content="#333f4b">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="#333f4b">
	<title><?php echo strip_tags($GLOBALS["site"]->data->titlePrefix.$VIEW["title"].$GLOBALS["site"]->data->titleSuffix); ?></title>
	
	@yield("headTop")
	
	<link rel="icon" href="<?php echo PATH_WEB; ?>pics/admin-favicon.png" type="image/png">
	<link rel="shortcut icon" href="<?php echo PATH_WEB; ?>pics/admin-favicon.png" type="image/png">
	<link rel="bookmark icon" href="<?php echo PATH_WEB; ?>pics/admin-favicon.png" type="image/png">
	
	<?php 
	if(View::hasSection("headMeta")) { ?> @yield("headMeta") <?php }
	else { ?> @include("_inc-head-meta") <?php }
	?>
	
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="<?php echo PATH_WEB; ?>vendors/bootstrap-3.3.7/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo PATH_ROOT_WEB; ?>css/classes.min.css" rel="stylesheet">
	<link href="<?php echo PATH_WEB; ?>css/gablini-app.css" rel="stylesheet">
	
	@yield("headMiddle")
	
	<!-- jQuery -->
	<script src="<?php echo PATH_WEB; ?>vendors/jquery-3.2.1.min.js"></script>
	
	<style> @yield("style") </style>
	
	@yield("headBottom")
</head>
<body>
	<?php if(true) { ?>
		@yield("bodyTop")
		
		<header id="main-header">
			<div id="main-header-title"><?php echo $VIEW["title"]; ?></div>
			<a id="main-header-menu-open" href="<?php echo PATH_WEB; ?>"><i class="fa fa-bars"></i></a>
			<a id="main-header-others-open" onclick="headerMenuToggle();"><i class="fa fa-ellipsis-v"></i></a>
			<nav id="main-header-others" class="main-header-others">
				<ul id="main-header-others-ul" class="main-header-others-ul">
					<?php
					foreach($VIEW["vars"]["navHeader"] AS $url => $name)
					{
						$active = ($url == $GLOBALS["URL"]->routes[0]) ? true : false;
						?><li class="main-header-others-li"><a href="<?php echo PATH_WEB.$url; ?>" class="main-header-others-a<?php if($active) { ?> main-header-others-a-active<?php } ?>"><?php echo $name; ?></a></li><?php
					}
					?>
					<li class="clear main-header-others-li-clear"></li>
				</ul>
				<div class="clear"></div>
			</nav>
		</header>
		<div id="body">
			<div id="main-header-under"></div>
			<div id="main"> @yield("content") </div>
			<div class="height-20"></div>
			<footer id="main-footer"><?php echo $GLOBALS["CUSTOMER"]["name"]; ?></footer>
		</div>
		
		<script>
		function headerMenuToggle()
		{
			var className = "main-header-others";
			var classNameOpened = className + "-opened";
			
			// Opened --> Close
			if($("." + className).hasClass(classNameOpened)) { $("." + className).removeClass(classNameOpened); }
			else { $("." + className).addClass(classNameOpened); }
		}
		</script>
		
		@yield("bodyBottom")
	<?php } else { ?>
		<!-- Without login -->
	<?php } ?>
</body>
</html>