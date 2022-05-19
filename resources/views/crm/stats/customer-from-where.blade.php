<div class="x_panel">
	<div class="x_title">
		<div class="row">
			<div class="col-xs-12">
				<h2 class="font-bold">Ügyfél honnan jött?</h2>
				<ul class="nav navbar-right panel_toolbox">
					<li><a class="close-link"><i class="fa fa-close"></i></a></li>
					<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="x_content">
		<?php 
		foreach($VIEW["vars"]["customersFromWhere"] AS $fromWhereName => $fromWhereData)
		{
			?>
			<div class="widget_summary">
				<div class="w_left width-25"><span><?php echo $fromWhereData["name"]; ?></span></div>
				<div class="w_center width-65">
					<div class="progress">
						<div class="progress-bar bg-green" role="progressbar" aria-valuenow="<?php echo $fromWhereData["percentageRound"]; ?>" aria-valuemin="1" aria-valuemax="100" style="width: <?php echo $fromWhereData["percentageRound"]; ?>%;"><span class="sr-only"><?php echo $fromWhereData["percentageFormatted"]; ?></span></div>
					</div>
				</div>
				<div class="w_right width-30"><span><strong><?php echo $fromWhereData["countFormatted"]; ?></strong> (<?php echo $fromWhereData["percentageFormatted"]; ?>)</span></div>
				<div class="clearfix"></div>
			</div>
			<?php
		}
		?>
	</div>
</div>
<script src="<?php echo GENTELELLA_DIR; ?>vendors/bootstrap-progressbar/bootstrap-progressbar.min.js"></script>