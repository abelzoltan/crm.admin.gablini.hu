@extends("app")

@section("content")
	<div class="my-container">
		<h2><?php echo $VIEW["title"]; ?></h2>
		<div class="height-10"></div>
		<div>Szerviz események száma: <strong><?php echo number_format(count($VIEW["vars"]["serviceEvents"]), 0, ",", " "); ?> db</strong></div>
		
		<table class="table table-hover table-striped">
			<thead>
				<tr>
					<th>Dátum</th>
					<th>Típus</th>
					<th>Autó</th>
					<th>Rendszám</th>
					<th>Hibaleírás</th>
					<th>Összeg</th>
					<th>Pontok</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$lastYear = NULL;		
				foreach($VIEW["vars"]["serviceEvents"] AS $eventKey => $event)
				{
					#Separator
					$year = date("Y", strtotime($event["data"]->dateClosed));
					if($year != $lastYear AND $year != "")
					{
						?>
						<tr>
							<td colspan="7"><?php echo $year; ?></td>
						</tr>
						<?php
					}
					$lastYear = $year;
					
					#Row
					?>
					<tr>
						<td><?php echo $event["dateClosedPublic"]; ?></td>
						<td><?php echo $event["typeName"]; ?></td>
						<td><?php echo $event["carNameHere"]; ?></td>
						<td><?php echo $event["carRegNumberHere"]; ?></td>
						<td><span class="font-size-12"><?php echo $event["text"]; ?></span></td>
						<td><?php echo $event["totalOut"]; ?></td>
						<td><?php echo $event["scoreOut"]; ?></td>
					</tr>	
					<?php
				}
				?>
			</tbody>
		</table>
		<?php 
		$datas = [
			"-" => "-",
			"dateOut" => "Rögzítve",
			"typeName" => "Típus",
			"dateSetoutPublic" => "Szerviz munkalap kiállítása",
			"dateClosedPublic" => "Lezárás dátuma",
			"--" => "-",
			"carNameHere" => "Autó",
			"carRegNumberHere" => "Rendszám",
			"---" => "-",
			"text" => "Hibaleírás",
		];
		
		foreach($VIEW["vars"]["serviceEvents"] AS $eventKey => $event)
		{
			break;
			$event["carRegNumberHere"] = $VIEW["vars"]["cars"][$event["data"]->car]["regNumber"];
			$event["carNameHere"] = $VIEW["vars"]["cars"][$event["data"]->car]["name"];
			?>
			<div class="height-30"></div>
			<div class="white-container">
				<div class="font-bold font-size-24 line-height-30"><?php echo $event["sheetNumber"]; ?></div>
				<?php			
				foreach($datas AS $key => $val)
				{
					if($key == "-" OR $key == "--" OR $key == "---") { ?><div class="height-20"></div><?php }
					else 
					{ 
						?>
						<div><?php echo $val; ?>: <strong><?php echo $event[$key]; ?></strong></div>
						<div class="height-5"></div>
						<?php }
				}
				?>
				<div class="height-20"></div>
				<div class="font-bold text-danger">Összeg: <?php echo $event["totalOut"]; ?></div>
				<div class="height-5"></div>
				<div class="font-bold text-primary">Pontok száma: <?php echo $event["scoreOut"]; ?></div>
			</div>
			<?php
		}
		?>
	</div>
@stop