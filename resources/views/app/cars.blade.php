@extends("app")

@section("content")
	<div class="my-container">
		<h3 class="font-light text-center font-size-22 line-height-30">Autók száma:</h3>
		<div class="height-10"></div>
		<div class="row">
			<div class="col-sm-6 col-xs-12 col-sm-offset-3"><div class="points-info text-myblue" style="margin-bottom: 0;"><strong><?php echo number_format(count($VIEW["vars"]["cars"]), 0, ",", " "); ?> db</strong></div></div>
		</div>
		<?php 
		$datas = [
			// "carYear" => "Gyártási év",
			"dateOut" => "Rögzítve",
			"brand" => "Márka",
			"name" => "Modell",
			"fuelNameHere" => "Üzemanyag",
			"kmHere" => "Futott km",
			"ccm" => "Hengerűrtartalom",
			"bodyNumber" => "Alvázszám",
			"regNumber" => "Rendszám",
		];
		
		foreach($VIEW["vars"]["cars"] AS $carKey => $car)
		{
			$car["fuelNameHere"] = $VIEW["vars"]["fuels"][$car["data"]->fuel]->nameOut;
			$car["kmHere"] = number_format($car["data"]->km, 0, ",", " ")." km";
			$car["ccmHere"] = number_format($car["data"]->ccm, 0, ",", " ")." cm<sup>3</sup>";
			?>
			<div class="height-30"></div>
			<div class="car car-<?php echo mb_strtolower($car["data"]->brand, "UTF-8"); ?>">
				<div class="font-light text-center font-size-24 line-height-30"><?php echo $car["name"]; ?></div>
				<div class="height-20"></div>
				<?php			
				foreach($datas AS $key => $val)
				{
					if($key == "-" OR $key == "--") { ?><div class="height-20"></div><?php }
					elseif(!empty($car[$key]))
					{ 
						?>
						<div class="car-data-row">
							<div class="car-data-left">
								<div class="car-data-left-inner"><?php echo $val; ?>:</div>
							</div>
							<div class="clear"></div>
							<div class="car-data-right">
								<div class="car-data-right-inner"><?php echo $car[$key]; ?></div>
							</div>	
							<div class="clear"></div>
						</div>
						<?php }
				}
				?>
				<div class="height-30"></div>
				<div class="text-center"><a href="<?php echo PATH_WEB; ?>service?car=<?php echo $car["data"]->id; ?>" class="my-btn my-btn-inverse text-uppercase transition" style="display: inline-block;">Foglaljon szervizidőpontot</a></div>
				<div class="height-20"></div>
			</div>
			<?php
		}
		?>
		<div class="height-20"></div>
	</div>
@stop