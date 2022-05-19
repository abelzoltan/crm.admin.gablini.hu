@extends("crm")

@section("content")
	<div class="x_panel">
		<div class="x_title">
			<div class="row">
				<div class="col-xs-12">
					<h2 class="font-bold">Lekérés</h2>
					<ul class="nav navbar-right panel_toolbox">
						<li><a class="close-link"><i class="fa fa-close"></i></a></li>
						<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="x_content">
			<div class="height-1 bg-gray2"></div>
			<div class="height-7"></div>
			<?php
			foreach($VIEW["vars"]["brands"] AS $brand => $data)
			{
				?>
				<div class="row font-size-16 line-height-30">
					<div class="col-lg-2 col-sm-3 col-xs-12"><?php echo $data["name"]; ?>:</div>
					<div class="col-lg-10 col-sm-7 col-xs-12">
						<?php 
						foreach($data["buttons"] AS $btnKey => $btnData)
						{
							?><a class="file-link btn btn-sm btn-<?php echo $btnData["class"]; ?> font-bold brand-link brand-link-<?php echo $brand."-".$btnKey; ?>" onclick="toggleButtons('<?php echo $brand."-".$btnKey; ?>')"><?php echo $btnData["name"]; ?></a><?php
						}
						?><a class="file-link btn btn-sm btn-default color-black font-bold brand-link brand-link-<?php echo $brand; ?>-custom-form" onclick="toggleButtons('<?php echo $brand; ?>-custom-form')">EGYEDI</a><?php
						foreach($data["buttons"] AS $btnKey => $btnData)
						{
							?>
							<div class="brand-buttons brand-buttons-<?php echo $brand."-".$btnKey; ?> display-none">
								<div class="height-5"></div>
								<div class="height-1 bg-gray2"></div>
								<div>
									<strong class="font-size-14" style="position: relative; top: 3px;"><?php echo $btnData["name"]; ?>:</strong> &nbsp;
									<?php 
									foreach($btnData["premises"] AS $premiseKey => $premise)
									{
										?><a class="file-link btn btn-sm btn-<?php echo $premise["class"]; ?> font-bold premise-link" href="<?php echo $premise["link"]; ?>" style="margin-top: 5px !important;"><?php echo $premise["name"]; ?></a><?php
									}
									?>
								</div>
							</div>
							<?php
						}
						?>
						<div class="brand-buttons brand-buttons-<?php echo $brand; ?>-custom-form display-none">
							<div class="height-5"></div>
							<div class="height-1 bg-gray2"></div>
							<div>
								<div><strong class="font-size-14" style="position: relative; top: 3px;">EGYEDI SZŰRÉS:</strong></div>
								<form action="<?php echo $GLOBALS["URL"]->link($data["link"]); ?>" method="get" target="_blank">
									<input type="hidden" name="correctDates" value="1">
									<input type="hidden" name="brand" value="<?php echo $brand; ?>">
									<div class="row">
										<div class="col-md-4 col-sm-6 col-xs-12">
											<div class="height-3"></div>
											<select class="form-control" name="premise">
												<option value="">ÖSSZES TELEPHELY</option>
												<?php foreach($data["premises"] AS $premise) { ?><option value="<?php echo $premise->premise; ?>"><?php echo $premise->premise; ?></option><?php } ?>
											</select>
										</div>
										<div class="col-md-3 col-sm-6 col-xs-12">
											<div class="height-3"></div>
											<input type="date" class="form-control has-feedback-left" name="dateFrom">
											<span class="form-control-feedback left font-size-12">TÓL</span>
										</div>
										<div class="col-md-3 col-sm-6 col-xs-12">
											<div class="height-3"></div>
											<input type="date" class="form-control has-feedback-left" name="dateTo">
											<span class="form-control-feedback left font-size-12">IG</span>
										</div>
										<div class="col-md-2 col-sm-6 col-xs-12">
											<div class="height-3"></div>
											<button type="submit" class="btn btn-danger">Küldés</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
				<div class="height-7"></div>
				<div class="height-1 bg-gray2"></div>
				<div class="height-7"></div>
				<?php
			}
			?>
		</div>
	</div>
	<script>
	function toggleButtons(btnKey)
	{
		$(".brand-buttons-" + btnKey).slideToggle();
	}
	</script>
@stop