<?php 
$panel = $VIEW["vars"]["LIST"];
?>
@extends("crm")

@section("titleRight")
	@include("crm._title-right-new-btn")
@stop

@section("content")
	<form class="form-horizontal form-label-left" action="<?php echo $GLOBALS["URL"]->link([$GLOBALS["URL"]->routes[0]]); ?>" method="get">
		<div class="x_panel">
			<div class="x_title">
				<div class="row">
					<div class="col-xs-12">
						<h2 class="font-bold">Új autó eladások keresése</h2>
						<ul class="nav navbar-right panel_toolbox">
							<li><a class="close-link"><i class="fa fa-close"></i></a></li>
							<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="x_content">
				<div class="form-group form-group-customer-details">
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<label class="form-control text-right" style="border-color: transparent; box-shadow: none;">Dátum TÓL-IG:</label>
					</div>
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<input class="form-control" type="date" name="dateFrom" value="<?php if(isset($_GET["dateFrom"]) AND !empty($_GET["dateFrom"])) { echo date("Y-m-d", strtotime($_GET["dateFrom"])); } ?>">
						<div class="height-10 visible-xs"></div>
					</div>
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<input class="form-control" type="date" name="dateTo" value="<?php if(isset($_GET["dateTo"]) AND !empty($_GET["dateTo"])) { echo date("Y-m-d", strtotime($_GET["dateTo"])); } ?>">
						<div class="height-10 visible-xs"></div>
					</div>
				</div>
				<div class="form-group form-group-customer-details">
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<div class="height-10 visible-xs"></div>
					</div>	
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<select class="form-control" name="marketingDisabled">
							<option value="">(Marketing)</option>
							<option value="0" <?php if(isset($_GET["marketingDisabled"]) AND $_GET["marketingDisabled"] !== "" AND !$_GET["marketingDisabled"]) { ?>selected<?php } ?>>Engedélyezve</option>
							<option value="1" <?php if(isset($_GET["marketingDisabled"]) AND $_GET["marketingDisabled"]) { ?>selected<?php } ?>>TILTVA</option>
						</select>
						<div class="height-10 visible-xs"></div>
					</div>		
					<div class="form-group-customer-details-col col-sm-4 col-xs-12"><button type="submit" class="btn btn-primary font-bold display-block center width-100">Keresés</button></div>	
				</div>
			</div>
		</div>
	</form>
	
	<?php if(isset($VIEW["vars"]["LIST"])) { ?>
		@include("crm._list-panel")
	<?php } else { ?>
		<h1 class="color-red text-uppercase font-bold text-center">Nincs találat!</h1>
	<?php } ?>
@stop