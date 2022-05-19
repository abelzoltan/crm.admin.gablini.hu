@extends("app")

@section("content")
	<div class="my-container">
		<div class="height-20"></div>
		<div id="menu-logo"><img src="<?php echo PATH_WEB; ?>pics/logo-gablini.png" alt="Gablini Kft." class="img-responsive center"></div>
		<div class="height-20"></div>
		<div class="row">
			<?php
			foreach($VIEW["vars"]["navMain"] AS $url => $name)
			{
				if($url == "points") { $name .= " (".$VIEW["vars"]["customerPoints"]["out"].")"; }
				?><div class="col-md-4 col-sm-6 col-xs-12"><a href="<?php echo PATH_WEB.$url; ?>" class="my-btn menu-btn"><?php echo $name; ?></a></div><?php
			}
			?>
		</div>
		<div class="height-20"></div>
		<div class="row">
			<?php
			foreach($VIEW["vars"]["navHeader"] AS $url => $name)
			{
				?><div class="col-md-4 col-sm-6 col-xs-12"><a href="<?php echo PATH_WEB.$url; ?>" class="my-btn menu-btn"><?php echo $name; ?></a></div><?php
			}
			?>
		</div>
	</div>
@stop