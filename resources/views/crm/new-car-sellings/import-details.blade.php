<?php 
$import = $VIEW["vars"]["import"];
$basicDatas = $import["out"]["basic"];
$rowDatas = $import["out"]["rows"];
?>
@extends("crm")

@section("titleRight")
	@include("crm._title-right-back-btn")
@stop

@section("content")
	<div class="x_panel">
		<div class="x_title">
			<div class="row">
				<div class="col-xs-12">
					<h2 class="font-bold">Új autó eladás import (ID: <?php echo $import["id"]; ?>) alapadatok</h2>
				</div>
			</div>
		</div>
		<div class="x_content">
			<?php 
			$i = 0;
			foreach($basicDatas AS $dataKey => $data)
			{
				if($i > 0)
				{
					?>
					<div class="height-5"></div>
					<div class="height-1 bg-gray2"></div>
					<div class="height-5"></div>
					<?php
				}
				?>
				<div class="row font-size-14">
					<div class="col-md-2 col-xs-6"><?php echo $data["name"]; ?>:</div>
					<div class="col-md-10 col-xs-6 font-bold"><?php echo $data["value"]; ?></div>
				</div>
				<?php
				$i++;
			}
			if(!empty($import["data"]->content))
			{
				?>
				<div class="height-5"></div>
				<div class="height-1 bg-gray2"></div>
				<div class="height-5"></div>
				<div class="row font-size-14">
					<div class="col-md-2 col-xs-6">Fájl letöltése:</div>
					<div class="col-md-10 col-xs-6 font-bold"><a href="<?php echo $GLOBALS["URL"]->currentURL; ?>/file-download" class="btn btn-default bg-gray2 btn-xs color-black"><?php echo $import["fileName"]; ?></a></div>
				</div>
				<?php
			}
			?>
		</div>	
	</div>	
	<?php 
	if($import["success"])
	{
		?>
		<div class="x_panel">
			<div class="x_title">
				<div class="row">
					<div class="col-xs-12">
						<h2 class="font-bold">Adatsorok (<?php echo $import["datasCountFormatted"]; ?>) eredménye</h2>
					</div>
				</div>
			</div>
			<div class="x_content">
				<?php 
				$j = 0;
				foreach($rowDatas AS $rowDataKey => $rowData)
				{
					if($j > 0) { ?><div class="height-20"></div><?php }
					?>
					<div style="padding: 5px; border: 2px solid #ddd;">
						<div class="color-blue font-bold font-size-20 line-height-30"><?php echo $rowData["name"]; ?></div>
						<div style="padding: 5px; border-top: 2px solid #ddd;">
							<?php
							$i = 0;
							foreach($rowData["basic"] AS $dataKey => $data)
							{
								if($i > 0)
								{
									?>
									<div class="height-5"></div>
									<div class="height-1 bg-gray2"></div>
									<div class="height-5"></div>
									<?php
								}
								?>
								<div class="row font-size-14">
									<div class="col-md-2 col-xs-6"><?php echo $data["name"]; ?>:</div>
									<div class="col-md-10 col-xs-6 font-bold"><?php echo $data["value"]; ?></div>
								</div>
								<?php
								$i++;
							}
							?>
							<div class="height-5"></div>
							<div class="row font-size-14" style="border-top: 2px solid #ddd;">
								<?php
								$i = 0;
								foreach($rowData["datas"] AS $dataKey => $data)
								{
									?>
									<div class="col-xs-6">							
										<div class="row" style="border-bottom: 1px solid #ddd; padding: 5px 0;">
											<div class="col-md-5 col-xs-6"><?php echo $data["name"]; ?>:</div>
											<div class="col-md-7 col-xs-6 font-bold"><?php echo $data["value"]; ?></div>
										</div>															
									</div>								
									<?php
									$i++;
								}
								?>
							</div>
						</div>
					</div>	
					<?php
					$j++;
				}
				?>
			</div>	
		</div>	
		<?php
	}
	?>
@stop