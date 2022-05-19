@extends("app")

@section("bodyClass")
	my-bg-gray
@stop

@section("content")
	<div class="my-container">
		<h3 class="font-light text-center font-size-22 line-height-30">Pontokat kétféle módon<br>tud gyűjteni:</h3>
		<div class="height-10"></div>
		<div class="row">
			<div class="col-sm-6 col-xs-12 col-sm-offset-3"><div class="points-info text-myblue"><span class="font-light">Új/haszált autó vásárlása:</span><br><strong>3 000 Ft / 1 pont</strong></div></div>
		</div>
		<div class="height-10"></div>
		<div class="row">
			<div class="col-sm-6 col-xs-12 col-sm-offset-3"><div class="points-info text-myblue"><span class="font-light">Szerviz:</span><br><strong>100 Ft / 1 pont</strong></div></div>
		</div>
		
		<div class="height-1"></div>
		<h3 class="font-light text-center font-size-22 line-height-30">A pontokat az alábbi szolgáltatásokra tudja beváltani:</h3>
		<div class="height-10"></div>
		<div class="table-responsive">
			<table class="table table-hover table-striped points-table">
				<tbody>
					<?php 
					$rows = [
						[
							"name" => "Külső mosás",
							"point" => 1500,
							"text" => "",
						],
						[
							"name" => "Ózonos fertőtlenítés",
							"point" => 2000,
							"text" => "",
						],
						[
							"name" => "Külső-belső mosás",
							"point" => 4000,
							"text" => "",
						],
						[
							"name" => "Izzó csere",
							"point" => 1500,
							"text" => "+izzó ára 10% kedvezménnyel",
						],
						[
							"name" => "Akkumulátor csere",
							"point" => 3000,
							"text" => "+akkumulátor ára 10% kedvezménnyel",
						],
						[
							"name" => "Klímatisztítás",
							"point" => 3000,
							"text" => "3 000 Ft",
						],
						[
							"name" => "Kerékcsere",
							"point" => 8000,
							"text" => "10 000 Ft",
						],
						[
							"name" => "Polírozás",
							"point" => 10000,
							"text" => "25 000 Ft",
						],
					];
					
					foreach($rows AS $row)
					{
						?>
						<tr class="transition">
							<td class="font-bold"><?php echo $row["name"]; ?></td>
							<td class="text-right"><?php echo number_format($row["point"], 0, ",", " "); ?> pont</td>
							<td><?php echo $row["text"]; ?></td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
		
		<h3 class="font-light text-center font-size-22 line-height-30">Aktuális pontok:</h3>
		<div class="height-10"></div>
		<div class="row">
			<div class="col-sm-6 col-xs-12 col-sm-offset-3"><div class="points-info text-myblue"><strong><?php echo $VIEW["vars"]["customerPoints"]["out"]; ?></strong></div></div>
		</div>
	</div>
@stop