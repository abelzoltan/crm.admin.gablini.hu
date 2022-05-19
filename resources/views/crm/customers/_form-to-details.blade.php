<?php $jump = $VIEW["vars"]["jump"]; ?>
<form class="form-horizontal form-label-left" action="<?php echo PATH_WEB; ?>customers" method="get">
	<input type="hidden" name="jump" value="1">
	<div class="x_panel">
		<div class="x_title">
			<div class="row">
				<div class="col-xs-12">
					<h2 class="font-bold">Ügyfél adatlapra</h2>
					<ul class="nav navbar-right panel_toolbox">
						<li><a class="close-link"><i class="fa fa-close"></i></a></li>
						<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
					</ul>
				</div>
			</div>
		</div>
		<div class="x_content">
			<div class="form-group form-group-customer-details">
				<div class="form-group-customer-details-col col-sm-1 col-xs-12">
					<input class="form-control" type="number" name="id" placeholder="ID" value="<?php if(isset($jump["id"])) { echo $jump["id"]; } ?>">
					<div class="height-10 visible-xs"></div>
				</div>	
				<div class="form-group-customer-details-col col-sm-2 col-xs-12">
					<input class="form-control" type="text" name="token" placeholder="<?php echo CUSTOMER_TOKENNAME; ?>" maxlength="4" value="<?php if(isset($jump["token"])) { echo $jump["token"]; } ?>">
					<div class="height-10 visible-xs"></div>
				</div>	
				<div class="form-group-customer-details-col col-sm-3 col-xs-12">
					<div class="input-group">
						<span class="input-group-addon"><?php echo CUSTOMER_CODEBEFORE; ?></span>
						<input class="form-control" type="text" name="code" placeholder="<?php echo CUSTOMER_CODENAME; ?>" value="<?php if(isset($jump["code"])) { echo $jump["code"]; } ?>">
					</div>					
					<div class="height-10 visible-xs"></div>
				</div>					
				<div class="form-group-customer-details-col col-sm-4 col-xs-12">
					<input class="form-control" type="email" name="email" placeholder="E-mail cím" value="<?php if(isset($jump["email"])) { echo $jump["email"]; } ?>">
					<div class="height-10 visible-xs"></div>
				</div>	
				<div class="form-group-customer-details-col col-sm-2 col-xs-12"><button type="submit" class="btn btn-success font-bold display-block center width-100">Adatlapra</button></div>	
			</div>
			<?php if(isset($jump["jump"]) AND $jump["jump"]) { ?>
				<div class="height-10"></div>
				<h2 class="color-danger font-bold text-center text-uppercase">A keresés alapján nem található ügyfél!</h2>
			<?php } ?>
		</div>
	</div>
</form>	