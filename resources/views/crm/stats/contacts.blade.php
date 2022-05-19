<div class="row">
	<div class="col-sm-6 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<div class="row">
					<div class="col-xs-12">
						<h2 class="font-bold"><?php echo $VIEW["vars"]["contactStat"]["name"]; ?> [<?php echo $VIEW["vars"]["contactStat"]["dateOut"]; ?>]</h2>
						<ul class="nav navbar-right panel_toolbox">
							<li><a class="close-link"><i class="fa fa-close"></i></a></li>
							<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="x_content">
				<div class=""><canvas class="contactStatDoughnut"></canvas></div>
				<div class="height-40"></div>
				<table class="tile_info contact-stat-table">
					<tr>
						<th><p>Oldal</p></th>
						<th class="text-center"><p>Darabszám</p></th>
						<th class="text-center"><p>Százalék</p></th>
					</tr>						
					<?php 
					foreach($VIEW["vars"]["contactStat"]["rows"] AS $contactStat)
					{
						?>
						<tr>
							<td><p><i class="fa fa-square" style="color: #<?php echo $contactStat["color"]; ?>;"></i><?php echo $contactStat["name"]; ?></p></td>
							<td><p class="text-center"><?php echo $contactStat["countFormatted"]; ?></p></td>
							<td><p class="text-center"><?php echo $contactStat["percentageFormatted"]; ?></p></td>
						</tr>
						<?php
					}
					?>	
					<tr>
						<td class="font-bold" style="border-top: 2px solid #E6E9ED;"><p>ÖSSZESEN</p></td>
						<td class="font-bold" style="border-top: 2px solid #E6E9ED;"><p class="text-center"><?php echo $VIEW["vars"]["contactStat"]["countFormatted"]; ?></p></td>
						<td class="font-bold" style="border-top: 2px solid #E6E9ED;"><p class="text-center"><?php echo $VIEW["vars"]["contactStat"]["percentageFormatted"]; ?></p></td>
					</tr>						
				</table>
			</div>
		</div>
	</div>
	<div class="col-sm-6 col-xs-12">
		<div class="x_panel">
			<div class="x_title">
				<div class="row">
					<div class="col-xs-12">
						<h2 class="font-bold"><?php echo $VIEW["vars"]["contactStatYear"]["name"]; ?> [<?php echo $VIEW["vars"]["contactStatYear"]["dateOut"]; ?>]</h2>
						<ul class="nav navbar-right panel_toolbox">
							<li><a class="close-link"><i class="fa fa-close"></i></a></li>
							<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="x_content">
				<div><canvas id="contactStatBar"></canvas></div>
				<div class="height-40"></div>
				<table class="tile_info contact-stat-table">
					<tr>
						<th><p>Oldal</p></th>
						<th class="text-center"><p>Darabszám</p></th>
						<th class="text-center"><p>Százalék</p></th>
					</tr>						
					<?php 
					foreach($VIEW["vars"]["contactStatYear"]["rows"] AS $contactStat)
					{
						?>
						<tr>
							<td><p><i class="fa fa-square" style="color: #<?php echo $contactStat["color"]; ?>;"></i><?php echo $contactStat["name"]; ?></p></td>
							<td><p class="text-center"><?php echo $contactStat["countFormatted"]; ?></p></td>
							<td><p class="text-center"><?php echo $contactStat["percentageFormatted"]; ?></p></td>
						</tr>
						<?php
					}
					?>	
					<tr>
						<td class="font-bold" style="border-top: 2px solid #E6E9ED;"><p>ÖSSZESEN</p></td>
						<td class="font-bold" style="border-top: 2px solid #E6E9ED;"><p class="text-center"><?php echo $VIEW["vars"]["contactStatYear"]["countFormatted"]; ?></p></td>
						<td class="font-bold" style="border-top: 2px solid #E6E9ED;"><p class="text-center"><?php echo $VIEW["vars"]["contactStatYear"]["percentageFormatted"]; ?></p></td>
					</tr>						
				</table>
			</div>
		</div>
	</div>
</div>
<script src="<?php echo GENTELELLA_DIR; ?>vendors/Chart.js/dist/Chart.min.js"></script>
<script>
if(typeof(Chart) !== "undefined" && $(".contactStatDoughnut").length)
{						
	var chart_doughnut_settings = {
		type: "pie",
		data: {
			labels: [<?php foreach($VIEW["vars"]["contactStat"]["rows"] AS $contactStat) { ?>"<?php echo $contactStat["name"]; ?>",<?php } ?>],
			datasets: [{
				data: [<?php foreach($VIEW["vars"]["contactStat"]["rows"] AS $contactStat) { echo $contactStat["percentageChart"].","; } ?>],
				backgroundColor: [<?php foreach($VIEW["vars"]["contactStat"]["rows"] AS $contactStat) { ?>"#<?php echo $contactStat["color"]; ?>",<?php } ?>],
			}]
		},
		options: { 
			legend: false, 
			responsive: true 
		}
	}		
	$(".contactStatDoughnut").each(function(){				
		var chart_element = $(this);
		var chart_doughnut = new Chart(chart_element, chart_doughnut_settings);				
	});			 	   
}

if (typeof(Chart) !== "undefined" && $("#contactStatBar").length)
{ 		  
	var ctx = document.getElementById("contactStatBar");
	var contactStatBar = new Chart(ctx, {
		type: "bar",
		data: {
			labels: [<?php foreach($VIEW["vars"]["contactStatYear"]["rows"] AS $contactStat) { ?>"<?php echo $contactStat["name"]; ?>",<?php } ?>],
			datasets: [{
				label: "Ajánlatkérések száma",
				backgroundColor: [<?php foreach($VIEW["vars"]["contactStatYear"]["rows"] AS $contactStat) { ?>"#<?php echo $contactStat["color"]; ?>",<?php } ?>],
				data: [<?php foreach($VIEW["vars"]["contactStatYear"]["rows"] AS $contactStat) { ?>"<?php echo $contactStat["count"]; ?>",<?php } ?>]
				},],
		},
		options: {
			tooltips: { enabled: true },
			legend: false, 
		}
	});		  
} 
</script>