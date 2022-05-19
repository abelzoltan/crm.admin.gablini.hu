@extends("crm")

@section("content")
	<div class="x_panel">
		<div class="x_title">
			<div class="row">
				<div class="col-xs-12">
					<h2 class="font-bold">Szerviz események - CSV fájl feltöltése</h2>
				</div>
			</div>
		</div>
		<div class="x_content">
			<form class="form-horizontal form-label-left" action="<?php echo $GLOBALS["URL"]->currentURL; ?>" method="post" enctype="multipart/form-data">
				<?php echo csrf_field(); ?>
				<input type="hidden" name="process" value="1">
				<div class="form-group">
					<label class="control-label col-md-3 col-sm-3 col-xs-12">Tallózás:</label>
					<div class="col-md-9 col-sm-9 col-xs-12">
						<div class="file-input-container">
							<div class="btn btn-primary file-button"><span class="fa fa-upload"></span></div>
							<input type="text" class="form-control file-text" name="files-text" id="csv-files-text">
							<div class="clear"></div>
							<input type="file" class="form-control file-input" name="file" onchange="document.getElementById('csv-files-text').value = this.value;">
						</div>
					</div>
				</div>
				<div class="ln_solid"></div>
				<div class="form-group">
					<div class="col-xs-12 text-center">
						<span class="hidden-xs">&nbsp;&nbsp;&nbsp;</span>
						<a class="btn btn-lg btn-dark display-block-xs" href="<?php echo PATH_WEB; ?>szervizesemeny_demo.csv"><i class="fa fa-download"></i> &nbsp;Demó CSV</a>
						<div class="visible-xs height-30"></div>
						<span class="hidden-xs">&nbsp;&nbsp;&nbsp;</span>
						<button type="submit" class="btn btn-lg btn-success display-block-xs"><i class="fa fa-floppy-o"></i> &nbsp;Mentés</button>
						<span class="hidden-xs">&nbsp;&nbsp;&nbsp;</span>
					</div>
				</div>
			</form>
		</div>	
	</div>	
@stop