<?php $event = $VIEW["vars"]["event"]; ?>
@extends("crm")

@section("titleRight")
	@include("crm._title-right-back-btn")
@stop

@section("content")
	<?php 
	foreach($event["output"] AS $outputKey => $output)
	{
		if(count($output["data"]) > 0)
		{
			?>
			<div class="x_panel">
				<div class="x_title">
					<div class="row">
						<div class="col-xs-12">
							<h2 class="font-bold"><?php echo $output["name"]; ?></h2>
							<ul class="nav navbar-right panel_toolbox">
								<li><a class="close-link"><i class="fa fa-close"></i></a></li>
								<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="x_content">
					<div class="height-5"></div>
					<div class="height-1 bg-gray2"></div>
					<div class="height-5"></div>
					<?php 
					if($outputKey == "questionnaire") 
					{
						$leftCol = "col-sm-6 col-xs-12";
						$rightCol = "col-sm-6 col-xs-12";
					}
					else
					{
						$leftCol = "col-md-2 col-xs-6";
						$rightCol = "col-md-10 col-xs-6";
					}
					foreach($output["data"] AS $dataKey => $data)
					{
						?>
						<div class="row">
							<div class="<?php echo $leftCol; ?> font-size-14"><?php echo $data["name"]; ?>:</div>
							<div class="<?php echo $rightCol; ?> font-size-14 font-bold"><?php echo $data["val"]; ?></div>
						</div>			
						<div class="height-5"></div>
						<div class="height-1 bg-gray2"></div>
						<div class="height-5"></div>
						<?php 
					}
					?>
				</div>
			</div>
			<?php
		}
	}
	
	if(!empty($event["statuses"]))
	{
		?>
		<div class="x_panel">
			<div class="x_title">
				<div class="row">
					<div class="col-xs-12">
						<h2 class="font-bold">Állapotváltozások</h2>
						<ul class="nav navbar-right panel_toolbox">
							<li><a class="close-link"><i class="fa fa-close"></i></a></li>
							<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="x_content">
				<div class="row font-bold">
						<div class="col-sm-2 col-xs-12 font-size-14">Dátum</div>
						<div class="col-sm-2 col-xs-12 font-size-14">Munkatárs</div>
						<div class="col-sm-4 col-xs-12 font-size-14">Státusz</div>
						<div class="col-sm-4 col-xs-12 font-size-14">Megjegyzés</div>
					</div>	
				<div class="height-5"></div>
				<div class="height-2 bg-gray2"></div>
				<div class="height-5"></div>
				<?php 
				foreach($event["statuses"] AS $statusKey => $status) 
				{
					?>
					<div class="row">
						<div class="col-sm-2 col-xs-12 font-size-14"><?php echo $status["dateOut"]; ?></div>
						<div class="col-sm-2 col-xs-12 font-size-14"><?php echo $status["userName"]; ?></div>
						<div class="col-sm-4 col-xs-12 font-size-14"><?php echo $status["statusName"]; ?></div>
						<div class="col-sm-4 col-xs-12 font-size-14"><?php echo $status["comment"]; ?></div>
					</div>			
					<div class="height-5"></div>
					<div class="height-1 bg-gray2"></div>
					<div class="height-5"></div>
					<?php 
				}
				?>
			</div>
		</div>
		<?php
	}
	?>
@stop