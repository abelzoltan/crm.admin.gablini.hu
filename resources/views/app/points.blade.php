@extends("app")

@section("bodyClass")
	my-bg-gray
@stop

@section("content")
	<div class="my-container">
		<h3 class="font-light text-center font-size-22 line-height-30">Aktuális pontok:</h3>
		<div class="height-10"></div>
		<div class="row">
			<div class="col-sm-6 col-xs-12 col-sm-offset-3"><div class="points-info text-myblue"><strong><?php echo $VIEW["vars"]["customerPoints"]["out"]; ?></strong></div></div>
		</div>
		
		<h3 class="font-light text-center font-size-22 line-height-30">Szerviz események száma:</h3>
		<div class="height-10"></div>
		<div class="row">
			<div class="col-sm-6 col-xs-12 col-sm-offset-3"><div class="points-info text-myblue"><strong><?php echo number_format(count($VIEW["vars"]["serviceEvents"]), 0, ",", " "); ?> db</strong></div></div>
		</div>	
		
		<div class="height-10"></div>
		
		<div class="table-responsive">
			<table class="table table-hover table-striped points-table">
				<thead>
					<tr>
						<th style="width: 100px;">Dátum</th>
						<th style="width: 80px;">Típus</th>
						<th>Autó</th>
						<th>Rendszám</th>
						<th>Hibaleírás</th>
						<th style="width: 110px;" class="text-right">Összeg</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$lastYear = NULL;
					$sum = $points = 0;
					foreach($VIEW["vars"]["serviceEvents"] AS $eventKey => $event)
					{
						#Separator
						$year = date("Y", strtotime($event["data"]->dateClosed));
						if($year != $lastYear AND $year != "")
						{
							?>
							<tr class="points-year">
								<td colspan="7"><?php echo $year; ?></td>
							</tr>
							<?php
						}
						$lastYear = $year;
						
						#Row
						$sum += $event["total"];
						$points += $event["score"];
						
						$event["carRegNumberHere"] = $VIEW["vars"]["cars"][$event["data"]->car]["regNumber"];
						$event["carNameHere"] = $VIEW["vars"]["cars"][$event["data"]->car]["name"];
						?>
						<tr class="transition">
							<td><?php echo $event["dateClosedPublic"]; ?></td>
							<td><?php echo $event["typeName"]; ?></td>
							<td><?php echo $event["carNameHere"]; ?></td>
							<td><?php echo $event["carRegNumberHere"]; ?></td>
							<td><span class="font-size-12"><?php echo $event["text"]; ?></span></td>
							<td class="text-right"><?php echo $event["totalOut"]; ?></td>
						</tr>	
						<?php
					}
					?>
				</tbody>
				<tfoot>
					<tr>
						<th colspan="5"></th>
						<th class="text-right text-myblue"><?php echo number_format($sum, 0, ",", " "); ?> Ft</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
@stop