<?php 
$brandNames = [
	"nissan" => "Nissan",
	"peugeot" => "Peugeot",
	"kia" => "Kia",
	"hyundai" => "Hyundai",
	"citroen" => "Citroen",
	"infiniti" => "Infiniti",
	"egyeb" => "Márkafüggetlen",
];
?>

@extends("app")

@section("content")
	<div class="my-container">
		<?php 
		if(count($VIEW["vars"]["promotions"]) > 0)
		{
			foreach($VIEW["vars"]["promotions"] AS $promotion)
			{
				?>
				<div class="height-30"></div>
				<div class="car <?php if(!empty($promotion["mainBrand"])) {  echo "car-".$promotion["mainBrand"]; } ?>">
					<?php if($promotion["pic"] !== false) { ?>
						<div><img src="<?php echo $promotion["picLink"]; ?>" alt="<?php echo $promotion["name"]; ?>" class="img-responsive center"></div>
						<div class="height-20"></div>
					<?php } ?>
					<div class="font-light text-center font-size-24 line-height-30"><?php echo $promotion["name"]; ?></div>
					<?php if(!empty($promotion["text"])) { ?>
						<div class="height-20"></div>
						<div class="text-justify"><?php echo str_replace("&nbsp;", " ", $promotion["text"]); ?></div>
					<?php } ?>
					<div class="height-10"></div>
					<form class="form-horizontal myform-horizontal" action="<?php echo PATH_WEB; ?>service" method="get">
						<div class="form-group">
							<input type="hidden" name="targy" value="<?php echo $promotion["name"]; ?>">
							<select class="form-control input-lg" name="car">
								<option class="myform-select-option-first" value="">Kérjük válasszon autót!</option>
								<?php
								foreach($promotion["brands"] AS $brandHere)
								{
									if(isset($VIEW["vars"]["cars"][$brandHere]) AND count($VIEW["vars"]["cars"][$brandHere]))
									{
										$brandName = (isset($brandNames[$brandHere])) ? $brandNames[$brandHere] : mb_strtoupper($brandHere, "UTF-8");
										?>
										<!--optgroup label="<?php echo $brandName; ?>"-->
											<?php foreach($VIEW["vars"]["cars"][$brandHere] AS $car) { ?><option value="<?php echo $car["id"]; ?>"><?php echo $car["name"]; ?></option><?php } ?>
										<!--/optgroup-->
										<?php
									}
								}
								?>
							</select>
						</div>
						<div class="height-10"></div>
						<div class="text-center"><button type="submit" class="my-btn transition" style="display: inline-block;">ÉRDEKEL</button></div>
					</form>
					<div class="height-20"></div>
				</div>
				<?php
			}
		}
		else 
		{
			?>
			<div class="height-30"></div>
			<div class="font-light text-center">Jelenleg sajnos nincs egyetlen akciónk sem az Ön számára!</div>
			<?php
		}
		?>
	</div>
@stop