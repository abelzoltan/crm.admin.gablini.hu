<?php 
$answer = $VIEW["vars"]["answer"]; 
$serviceEvent = $VIEW["vars"]["serviceEvent"]; 
$comments = $VIEW["vars"]["comments"]; 
if(isset($_SESSION[SESSION_PREFIX."lastAnswerCommentID"]))
{
	$lastAnswerComment = $_SESSION[SESSION_PREFIX."lastAnswerCommentID"];
	unset($_SESSION[SESSION_PREFIX."lastAnswerCommentID"]);
}
else { $lastAnswerComment = 0; }
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
					<h2 class="font-bold">Alapadatok</h2>
					<ul class="nav navbar-right panel_toolbox">
						<li><a class="close-link"><i class="fa fa-close"></i></a></li>
						<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="x_content">
			<?php 
			if(!empty($answer["data"]->originalAnswer)) 
			{
				?>
				<div class="font-bold color-red font-size-16 line-height-20">Ez egy duplikált válasz (1 napon belül több esemény)! Az eredeti válasz adatlapját <a href="<?php echo PATH_WEB.$GLOBALS["URL"]->routes[0]."/".$GLOBALS["URL"]->routes[1]."/".$answer["data"]->originalAnswer; ?>" style="text-decoration: underline;">IDE KATTINTVA</a> érheti el.</div>
				<div class="height-10"></div>
				<div class="height-5"></div>
				<?php
			}
			?>
			
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<div class="row"><div class="col-xs-12 font-size-14 font-bold">ÜGYFÉL ADATOK:</div></div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<div class="row">
				<div class="col-md-3 col-xs-6 font-size-14">Ügyfél:</div>
				<div class="col-md-9 col-xs-6 font-size-14 font-bold"><a href="<?php echo PATH_WEB; ?>customer/<?php echo $answer["customerID"]; ?>" target="_blank" class="color-blue color-red-hover transition"><?php echo $answer["customerName"]; ?></a></div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<div class="row">
				<div class="col-md-3 col-xs-6 font-size-14"><?php echo $VIEW["vars"]["customerCodeName"] ?>:</div>
				<div class="col-md-9 col-xs-6 font-size-14 font-bold"><?php echo $answer["customerCode"]; ?></div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<div class="row">
				<div class="col-md-3 col-xs-6 font-size-14">Ügyfél e-mail címe:</div>
				<div class="col-md-9 col-xs-6 font-size-14 font-bold"><?php echo $answer["customer"]["email"]; ?></div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<div class="row">
				<div class="col-md-3 col-xs-6 font-size-14">Ügyfél telefonszáma:</div>
				<div class="col-md-9 col-xs-6 font-size-14 font-bold"><?php echo $answer["customer"]["phoneOut"]; ?></div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<?php if($serviceEvent !== false) { ?>
				<div class="row"><div class="col-xs-12 font-size-14 font-bold">SZERVIZ ESEMÉNY ADATOK:</div></div>			
				<div class="height-5"></div>
				<div class="height-1 bg-gray2"></div>
				<div class="height-5"></div>
				
				<div class="row">
					<div class="col-md-3 col-xs-6 font-size-14">Munkalapszám:</div>
					<div class="col-md-9 col-xs-6 font-size-14 font-bold"><a href="<?php echo PATH_WEB; ?>service-events/<?php echo $serviceEvent["id"]; ?>" target="_blank" class="color-blue color-red-hover transition"><?php echo $serviceEvent["sheetNumber"]; ?></a></div>
				</div>			
				<div class="height-5"></div>
				<div class="height-1 bg-gray2"></div>
				<div class="height-5"></div>
				
				<div class="row">
					<div class="col-md-3 col-xs-6 font-size-14">Lezárás dátuma:</div>
					<div class="col-md-9 col-xs-6 font-size-14 font-bold"><?php echo $serviceEvent["dateClosedPublic"]; ?></div>
				</div>			
				<div class="height-5"></div>
				<div class="height-1 bg-gray2"></div>
				<div class="height-5"></div>
				
				<div class="row">
					<div class="col-md-3 col-xs-6 font-size-14">Rendszám:</div>
					<div class="col-md-9 col-xs-6 font-size-14 font-bold"><?php echo $serviceEvent["car"]["regNumber"]; ?></div>
				</div>			
				<div class="height-5"></div>
				<div class="height-1 bg-gray2"></div>
				<div class="height-5"></div>
			<?php } ?>
			
			<div class="row"><div class="col-xs-12 font-size-14 font-bold">KÉRDŐÍV ADATOK:</div></div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
	
			<div class="row">
				<div class="col-md-3 col-xs-6 font-size-14">Kérdőív:</div>
				<div class="col-md-9 col-xs-6 font-size-14 font-bold"><?php echo $answer["questionnaireName"]; ?></div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<div class="row">
				<div class="col-md-3 col-xs-6 font-size-14">Kérdőív kódja:</div>
				<div class="col-md-9 col-xs-6 font-size-14 font-bold"><?php echo $answer["questionnaireCode"]; ?></div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<div class="row"><div class="col-xs-12 font-size-14 font-bold">KITÖLTÉS ADATOK:</div></div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<div class="row">
				<div class="col-md-3 col-xs-6 font-size-14">Kitöltés időpontja:</div>
				<div class="col-md-9 col-xs-6 font-size-14 font-bold"><?php echo $answer["dateOut"]; ?></div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<div class="row">
				<div class="col-md-3 col-xs-6 font-size-14">Kitöltő:</div>
				<div class="col-md-9 col-xs-6 font-size-14 font-bold">
					<?php
					if($answer["answerByUser"]) { ?>Munkatárs: <?php echo $answer["userName"]; }
					else { ?>Ügyfél: <?php echo $answer["customerName"]; }
					?>
				</div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			
			<div class="row">
				<div class="col-md-3 col-xs-6 font-size-14">Értékelés:</div>
				<div class="col-md-9 col-xs-6 font-size-14 font-bold">
					<?php
					if($answer["hasBadValue"]) { ?><span class="color-red">ALACSONY ÉRTÉKELÉST KAPTUNK!</span><?php }
					else { ?><span class="color-success">Nem kaptunk alacsony értékelést.</span><?php }
					?>
				</div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
		</div>
	</div>
	<?php 
	if($answer !== NULL AND ((isset($answer["customerData"]) AND !empty($answer["customerData"])) OR (isset($answer["answers"]) AND !empty($answer["answers"])))) 
	{ 
		?>
		<div class="x_panel">
			<div class="x_title">
				<div class="row">
					<div class="col-xs-12">
						<h2 class="font-bold">Kérdések és válaszok</h2>
						<ul class="nav navbar-right panel_toolbox">
							<li><a class="close-link"><i class="fa fa-close"></i></a></li>
							<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
						</ul>
					</div>
				</div>
			</div>	
				<div class="x_content">
					<div class="height-1 bg-gray2"></div>
					<div class="height-5"></div>
					
					<?php
					if(isset($answer["customerData"]) AND !empty($answer["customerData"]))
					{
						?>						
						<div class="row"><div class="col-xs-12 font-size-14 font-bold">BEKÜLDÖTT ÜGYFÉL ADATOK:</div></div>			
						<div class="height-5"></div>
						<div class="height-1 bg-gray2"></div>
						<div class="height-5"></div>		
						<?php
						foreach($answer["customerData"] AS $dataKey => $dataVal)
						{
							?>
							<div class="row">
								<div class="col-md-3 col-xs-6 font-size-14"><?php echo $dataKey; ?>:</div>
								<div class="col-md-9 col-xs-6 font-size-14 font-bold"><?php echo $dataVal; ?></div>
							</div>			
							<div class="height-5"></div>
							<div class="height-1 bg-gray2"></div>
							<div class="height-5"></div>
							<?php 
						}
					}
					if(isset($answer["answers"]) AND !empty($answer["answers"]))
					{
						?>				
						<div class="row"><div class="col-xs-12 font-size-14 font-bold">VÁLASZOK:</div></div>			
						<div class="height-5"></div>
						<div class="height-1 bg-gray2"></div>
						<div class="height-5"></div>
						<?php
						foreach($answer["answers"] AS $dataKey => $data)
						{
							$name = $data["originalName"];
							if($data["required"]) { $name .= " <em class='font-bold color-success'>[KÖTELEZŐ]</em>"; }
							if($data["watched"]) { $name .= " <em class='font-bold color-primary'>[FIGYELT]</em>"; }
							if($data["badValue"]) { $name .= " <em class='font-bold color-red'>[ALACSONY ÉRTÉKELÉS!]</em>"; }
							?>
							<div class="row">
								<div class="col-xs-12 font-size-14 line-height-24"><?php echo $name; ?>:</div>
								<div class="clear"></div>
								<div class="col-xs-12 font-size-14 font-bold"><?php echo $data["val"]; ?></div>
							</div>			
							<div class="height-5"></div>
							<div class="height-1 bg-gray2"></div>
							<div class="height-5"></div>
							<?php 
						}
					}
					?>
				</div>
		</div>
		<?php 
	}
	if(!empty($answer["watchedQuestions"]))
	{
		?>
		<div class="x_panel">
			<div class="x_title">
				<div class="row">
					<div class="col-xs-12">
						<h2 class="font-bold">Figyelt kérdések</h2>
						<ul class="nav navbar-right panel_toolbox">
							<li><a class="close-link"><i class="fa fa-close"></i></a></li>
							<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="x_content">
				<?php
				foreach($answer["watchedQuestions"] AS $dataKey => $data)
				{
					?>
					<div class="bg-gray2 font-size-14 line-height-20 font-bold" style="padding: 5px;"><?php echo $data["name"]; ?></div>
					<div class="height-5"></div>
					
					<div class="row">
						<div class="col-md-3 col-xs-6 font-size-14">Adott válasz:</div>
						<div class="col-md-9 col-xs-6 font-size-14 font-bold"><?php echo $data["val"]; ?></div>
					</div>
					<div class="height-5"></div>
					<div class="height-1 bg-gray2"></div>
					<div class="height-5"></div>
					
					<div class="row">
						<div class="col-md-3 col-xs-6 font-size-14">Alacsony értékek:</div>
						<div class="col-md-9 col-xs-6 font-size-14 font-italic"><?php echo implode("<br>", $data["inputWatchValues"]); ?></div>
					</div>								
					<div class="height-5"></div>
					<div class="height-1 bg-gray2"></div>
					<div class="height-5"></div>
					
					<div class="row">
						<div class="col-md-3 col-xs-6 font-size-14">Értékelés?:</div>
						<div class="col-md-9 col-xs-6 font-size-14 font-bold">
							<?php 
							if($data["badValue"]) { ?><span class="color-red">ALACSONY ÉRTÉKELÉS!</span><?php }
							else { ?><span class="color-success">Megfelelő értékelés.</span><?php }
							?>
						</div>
					</div>								
					<div class="height-5"></div>
					<?php 
				}
				?>
			</div>
		</div>
		<?php
	}
	?>
	<div class="x_panel" id="comments">
		<div class="x_title">
			<div class="row">
				<div class="col-xs-12">
					<h2 class="font-bold">Új megjegyzés</h2>
					<ul class="nav navbar-right panel_toolbox">
						<li><a class="close-link"><i class="fa fa-close"></i></a></li>
						<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="x_content">
			<form class="form-horizontal form-label-left" action="<?php echo PATH_WEB.$GLOBALS["URL"]->routes[0]."/new-comment/".$answer["id"]; ?>" method="post">
				<?php echo csrf_field(); ?>
				<input type="hidden" name="process" value="1">
				<input type="hidden" name="answer" value="<?php echo $answer["id"]; ?>">
				<div class="form-group form-group-customer-details">
					<div class="form-group-customer-details-col col-sm-10 col-xs-12"><textarea name="comment" class="form-control" rows="5" placeholder="Belső megjegyzés írása, pl. beszélgetés eredménye, ..."></textarea></div>
					<div class="form-group-customer-details-col col-sm-2 col-xs-12 text-center">
						<div class="height-40 hidden-xs"></div>
						<div class="height-10 visible-xs"></div>
						<button type="submit" class="btn btn-primary btn-lg font-bold visible-xs-inline-block">Küldés</button>
						<button type="submit" class="btn btn-primary font-bold display-block center width-100 hidden-xs">Küldés</button>
					</div>
				</div>
			</form>	
		</div>
	</div>
	<div class="x_panel">
		<div class="x_title">
			<div class="row">
				<div class="col-xs-12">
					<h2 class="font-bold">Megjegyzések</h2>
					<ul class="nav navbar-right panel_toolbox">
						<li><a class="close-link"><i class="fa fa-close"></i></a></li>
						<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="x_content">
			<?php
			if(!empty($comments))
			{
				?>
				<table class="table table-striped">
					<thead>
						<tr>
							<th style="width: 25%;">Dátum</th>
							<th style="width: 35%;">Munkatárs</th>
							<th>Megjegyzés</th>
						</tr>
					</thead>
					<tbody>
						<?php 
						foreach($comments AS $commentKey => $comment)
						{
							?>
							<tr <?php if($comment["id"] == $lastAnswerComment) { ?>class="bg-green"<?php } ?>>
								<td><?php echo $comment["dateOut"]; ?></td>
								<td><?php echo $comment["user"]; ?></td>
								<td><?php echo $comment["commentHTML"]; ?></td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<?php
			}
			else { ?><h3 class="font-italic text-center text-info">Senki nem fűzött megjegyzést a kitöltéshez!</h3><?php }
			?>
		</div>
	</div>
@stop