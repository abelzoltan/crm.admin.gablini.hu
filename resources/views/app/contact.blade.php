@extends("app")

@section("bodyClass")
	my-bg-gray
@stop

@section("content")
	<div class="my-container">
		<h2><?php echo $VIEW["title"]; ?></h2>
		<?php 
		foreach($MAIN["premises"] AS $premiseID => $premise)
		{
			?>
			<div class="height-30"></div>
			<div class="premise-container">
				<div class="font-bold font-size-24 line-height-30 text-center text-myblue"><?php echo $premise["name"]; ?></div>
				<?php if(!empty($premise["address"])) { ?>
					<div class="height-10"></div>
					<div class="text-center"><?php echo $premise["address"]; ?></div>
				<?php } if(!empty($premise["phone"])) { ?>
					<div class="height-20"></div>
					<div><a href="tel:<?php echo $premise["phoneLink"]; ?>" class="my-btn my-btn-inverse my-btn-small transition"><?php echo $premise["phone"]; ?></a></div>
				<?php } if(!empty($premise["email"])) { ?>
					<div class="height-20"></div>
					<div><a href="mailto:<?php echo $premise["email"]; ?>" class="my-btn my-btn-inverse my-btn-small transition"><?php echo $premise["email"]; ?></a></div>
				<?php } if(!empty($premise["brands"])) { ?>
					<div class="height-10"></div>
					<div class="premise-data premise-brands">
						<?php 
						foreach($premise["brands"] AS $brandID => $brand) 
						{ 
							?><img src="<?php echo PATH_ROOT_WEB; ?>pics/logok/<?php echo $brand->name; ?>.png" alt="<?php echo $brand->nameOut; ?>" class="premise-brands-img"><?php 
						} 
						?>
					</div>	
				<?php } ?>
				<div class="height-20"></div>
				<div class="font-bold text-primary font-size-20 line-height-24"><?php echo $car["regNumber"]; ?></div>
			</div>
			<?php
		}
		?>
		<div class="height-20"></div>
	</div>
@stop