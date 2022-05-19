<div class="x_panel customer-details-stat1-panel">
	<div class="x_content customer-details-stat1-content">
		<div class="row customer-details-stat1">
			<?php 
			foreach($VIEW["vars"]["customerCount"] AS $countKey => $countData)
			{
				?>
				<div class="col-sm-3 col-xs-6 text-center customer-details-stat1-col">
					<div class="customer-details-stat1-col-separator visible-xs"></div>
					<div class="color-gray"><i class="fa fa-user"></i> <?php echo $countData["name"]; ?></div>
					<div class="font-bold font-size-30 line-height-40 color-gentelella"><?php echo $countData["countFormatted"]; ?></div>
					<div class="color-success"><?php echo $countData["percentageFormatted"]; ?></div>
				</div>
				<?php
			}
			?>
		</div>
	</div>
</div>