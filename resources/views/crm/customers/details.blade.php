<?php $customer = $VIEW["vars"]["customer"]; ?>
@extends("crm")

@section("titleRight")
	@include("crm._title-right-back-btn")
@stop

@section("content")
	@include("crm.customers._details-menu")	
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
			<div class="row">
				<div class="col-md-2 col-xs-6 font-size-14">Név:</div>
				<div class="col-md-10 col-xs-6 font-size-14 font-bold"><?php echo $customer["fullName"]; ?></div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			<div class="row">
				<div class="col-md-2 col-xs-6 font-size-14">E-mail cím:</div>
				<div class="col-md-10 col-xs-6 font-size-14 font-bold"><?php echo $customer["email"]; ?></div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			<div class="row">
				<div class="col-md-2 col-xs-6 font-size-14"><?php echo CUSTOMER_CODENAME; ?>:</div>
				<div class="col-md-10 col-xs-6 font-size-14 font-bold"><?php echo $customer["code"]; ?></div>
			</div>			
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			<div class="row">
				<div class="col-md-2 col-xs-6 font-size-14"><?php echo CUSTOMER_TOKENNAME; ?>:</div>
				<div class="col-md-10 col-xs-6 font-size-14 font-bold"><?php echo $customer["token"]; ?></div>
			</div>
			<div class="height-5"></div>
			<div class="height-3 bg-gray2"></div>
			<div class="height-5"></div>
			<div class="row">
				<div class="col-md-2 col-xs-6 font-size-14">Rögzítés időpontja:</div>
				<div class="col-md-10 col-xs-6 font-size-14 font-bold"><?php echo $customer["dateOut"]; ?></div>
			</div>
			<div class="height-5"></div>
			<div class="height-1 bg-gray2"></div>
			<div class="height-5"></div>
			<div class="row">
				<div class="col-md-2 col-xs-6 font-size-14">Rögzítéső munkatárs:</div>
				<div class="col-md-10 col-xs-6 font-size-14 font-bold"><?php echo $customer["userName"]; ?></div>
			</div>
			<div class="height-5"></div>
			<div class="height-3 bg-gray2"></div>
			<div class="height-5"></div>
			<div class="row">
				<div class="col-md-2 col-xs-6 font-size-14">Telefonszám:</div>
				<div class="col-md-10 col-xs-6 font-size-14 font-bold"><?php echo $customer["phoneOut"]; ?></div>
			</div>
			<?php if(!empty($customer["address"])) { ?>
				<div class="height-5"></div>
				<div class="height-1 bg-gray2"></div>
				<div class="height-5"></div>
				<div class="row">
					<div class="col-md-2 col-xs-6 font-size-14">Cím:</div>
					<div class="col-md-10 col-xs-6 font-size-14 font-bold"><?php echo $customer["address"]; ?></div>
				</div>	
			<?php } ?>
		</div>
	</div>
	<div class="x_panel">
		<div class="x_title">
			<div class="row">
				<div class="col-xs-12">
					<h2 class="font-bold">Utolsó 5 megjegyzés</h2>
					<ul class="nav navbar-right panel_toolbox">
						<li><a class="close-link"><i class="fa fa-close"></i></a></li>
						<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="x_content">
			
		</div>
	</div>
	<div class="x_panel">
		<div class="x_title">
			<div class="row">
				<div class="col-xs-12">
					<h2 class="font-bold">Utolsó 10 kapcsolatfelvétel</h2>
					<ul class="nav navbar-right panel_toolbox">
						<li><a class="close-link"><i class="fa fa-close"></i></a></li>
						<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="x_content">
			
		</div>
	</div>	
	<?php 
	if(isset($VIEW["vars"]["logList"])) 
	{ 
		$panel = $VIEW["vars"]["logList"];
		?> @include("crm._list-panel") <?php 
	} 
	?>
@stop