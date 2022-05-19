<?php $search = $VIEW["vars"]["search"]; ?>
<form class="form-horizontal form-label-left" action="<?php echo PATH_WEB; ?>customers" method="get">
	<input type="hidden" name="search" value="1">
	<div class="x_panel">
		<div class="x_title">
			<div class="row">
				<div class="col-xs-12">
					<h2 class="font-bold">Ügyfelek keresése</h2>
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
					<input class="form-control" type="text" name="name" placeholder="Név" value="<?php if(isset($search["name"])) { echo $search["name"]; } ?>">
					<div class="height-10 visible-xs"></div>
				</div>	
				<div class="form-group-customer-details-col col-sm-4 col-xs-12">
					<input class="form-control" type="text" name="companyName" placeholder="Cégnév" value="<?php if(isset($search["companyName"])) { echo $search["companyName"]; } ?>">
					<div class="height-10 visible-xs"></div>
				</div>	
				<div class="form-group-customer-details-col col-sm-4 col-xs-12">
					<select class="form-control" type="text" name="user">
						<option value="" class="color-gray2">(Rögzítő munkatárs)</option>
						<?php foreach($VIEW["vars"]["usersForSearch"] AS $userID => $userName) { ?>
							<option value="<?php echo $userID; ?>" <?php if(isset($search["user"]) AND $search["user"] == $userID) { ?>selected<?php } ?>><?php echo $userName; ?></option>
						<?php } ?>	
					</select>
					<div class="height-10 visible-xs"></div>
				</div>												
			</div>
			<div class="form-group form-group-customer-details">
				<div class="form-group-customer-details-col col-sm-4 col-xs-12">
					<input class="form-control" type="text" name="email" placeholder="E-mail cím" value="<?php if(isset($search["email"])) { echo $search["email"]; } ?>">
					<div class="height-10 visible-xs"></div>
				</div>
				<div class="form-group-customer-details-col col-sm-3 col-xs-12">
					<input class="form-control" type="text" name="phone" placeholder="Telefonszám" value="<?php if(isset($search["phone"])) { echo $search["phone"]; } ?>">
					<div class="height-10 visible-xs"></div>
				</div>		
				<div class="form-group-customer-details-col col-sm-3 col-xs-12">
					<input class="form-control" type="text" name="code" placeholder="<?php echo CUSTOMER_CODENAME; ?>" value="<?php if(isset($search["code"])) { echo $search["code"]; } ?>">
					<div class="height-10 visible-xs"></div>
				</div>	
				<div class="form-group-customer-details-col col-sm-2 col-xs-12"><button type="submit" class="btn btn-primary font-bold display-block center width-100">Keresés</button></div>	
			</div>
		</div>
	</div>
</form>	