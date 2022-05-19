<?php 
$search = $VIEW["vars"]["search"];
$panel = $VIEW["vars"]["LIST"];
?>
@extends("crm")

@section("titleRight")
	@include("crm._title-right-new-btn")
@stop

@section("content")
	<form class="form-horizontal form-label-left" action="<?php echo $GLOBALS["URL"]->link([$GLOBALS["URL"]->routes[0]]); ?>" method="get">
		<input type="hidden" name="search" value="1">
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
						<label class="form-control text-right" style="border-color: transparent; box-shadow: none;">Eladás TÓL-IG:</label>
					</div>
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<input class="form-control" type="date" name="dateSellingFrom" value="<?php if(isset($search["dateSellingFrom"])) { echo date("Y-m-d", strtotime($search["dateSellingFrom"])); } ?>">
						<div class="height-10 visible-xs"></div>
					</div>
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<input class="form-control" type="date" name="dateSellingTo" value="<?php if(isset($search["dateSellingTo"])) { echo date("Y-m-d", strtotime($search["dateSellingTo"])); } ?>">
						<div class="height-10 visible-xs"></div>
					</div>
				</div>
				<div class="form-group form-group-customer-details">
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<label class="form-control text-right" style="border-color: transparent; box-shadow: none;">Rögzítés dátuma TÓL-IG:</label>
					</div>
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<input class="form-control" type="date" name="dateFrom" value="<?php if(isset($search["dateFrom"])) { echo date("Y-m-d", strtotime($search["dateFrom"])); } ?>">
						<div class="height-10 visible-xs"></div>
					</div>
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<input class="form-control" type="date" name="dateTo" value="<?php if(isset($search["dateTo"])) { echo date("Y-m-d", strtotime($search["dateTo"])); } ?>">
						<div class="height-10 visible-xs"></div>
					</div>
				</div>
				<div class="form-group form-group-customer-details">
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<!--input class="form-control" type="text" name="sheetNumber" placeholder="Munkalapszám / Ajánlat sorszáma" value="<?php if(isset($search["sheetNumber"])) { echo $search["sheetNumber"]; } ?>"-->
						<div class="height-10 visible-xs"></div>
					</div>	
					<div class="form-group-customer-details-col col-sm-4 col-xs-12">
						<select class="form-control" name="questionnaireAnswered">
							<option value="">(Kérdőív kitöltés)</option>
							<option value="y" <?php if(isset($search["questionnaireAnswered"]) AND $search["questionnaireAnswered"]) { ?>selected<?php } ?>>Kérdőív KITÖLTVE</option>
							<option value="n" <?php if(isset($search["questionnaireAnswered"]) AND !$search["questionnaireAnswered"]) { ?>selected<?php } ?>>Kérdőív üres</option>
						</select>
						<div class="height-10 visible-xs"></div>
					</div>		
					<div class="form-group-customer-details-col col-sm-4 col-xs-12"><button type="submit" class="btn btn-primary font-bold display-block center width-100">Keresés</button></div>	
				</div>
			</div>
		</div>
	</form>
	@include("crm._list-panel")
@stop