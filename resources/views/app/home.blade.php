@extends("app")

@if(count($VIEW["vars"]["promotions"]) > 0)
	@section("headMiddle")
		<style><?php include(DIR_PUBLIC."vendors/Swiper-3.4.2/dist/css/swiper.min.css"); ?></style>
		<script>
			<?php
			$swiperInc = file_get_contents(DIR_PUBLIC."vendors/Swiper-3.4.2/dist/js/swiper.min.js");
			echo "\r\n".str_replace("maps/swiper.min.js.map", PATH_WEB."vendors/Swiper-3.4.2/dist/js/maps/swiper.min.js.map", $swiperInc);
			unset($swiperInc);
			?>
		</script>
	@stop
@endif

@section("content")
	<div class="my-container">
		<div class="height-20"></div>
		<div id="menu-logo"><img src="<?php echo PATH_WEB; ?>pics/logo-gablini-2020-app.png" alt="Gablini Kft." class="img-responsive center"></div>
		<div class="height-20"></div>
		
		<?php
		if(count($VIEW["vars"]["promotions"]) > 0)
		{
			?>
			<div class="my-slider" id="main-slider">
				<div class="swiper-container">
					<div class="swiper-wrapper">
						<?php
						foreach($VIEW["vars"]["promotions"] AS $promotion)
						{				
							if($promotion["pic"] !== false)
							{
								?>
								<div class="swiper-slide my-slider-item">
									<a href="<?php echo PATH_WEB; ?>service?targy=<?php echo $promotion["name"]; ?>" class="my-slider-item-link">
										<img src="<?php echo $promotion["picLink"]; ?>" class="img-responsive center" alt="<?php echo $promotion["name"]; ?>">
									</a>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
			</div>
			<script>
			$(document).ready(function(){
				var mainSlider = new Swiper ("#main-slider .swiper-container", {
					loop: true,
					autoplay: 2000,
					autoHeight: true,
					preventClicks: false,
					preventClicksPropagation: false,
				}); 	
			});
			</script>
			<?php
		}
		?>
		
		<div class="row">
			<?php
			foreach($VIEW["vars"]["navMain"] AS $url => $name)
			{
				if($url == "points") { $name .= " (".$VIEW["vars"]["customerPoints"]["out"].")"; }
				?><div class="col-md-4 col-sm-6 col-xs-12"><a href="<?php echo PATH_WEB.$url; ?>" class="my-btn menu-btn transition"><?php echo $name; ?></a></div><?php
			}
			foreach($VIEW["vars"]["navHeader"] AS $url => $name)
			{
				if($url == "logout") { continue; }
				?><div class="col-md-4 col-sm-6 col-xs-12"><a href="<?php echo PATH_WEB.$url; ?>" class="my-btn menu-btn transition"><?php echo $name; ?></a></div><?php
			}
			?>
		</div>
	</div>
@stop