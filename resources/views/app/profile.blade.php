<?php 
$appUser = $GLOBALS["APPUSER"];
$customer = $GLOBALS["CUSTOMER"]; 

$appUser["data"]->_points = $VIEW["vars"]["customerPoints"]["out"];
?>
@extends("app")

@section("bodyClass")
	my-bg-gray
@stop

@section("content")
	<div class="height-30"></div>
	<div class="my-container">
		<?php
		$appUserDatas = [
			"name" => "Név",
			// "token" => "Belépési token",
			"loyaltyCard" => "Hűségkártya száma",
			"_points" => "Pontok száma",
			"-" => "-",
		];
		$customerDatas = [
			"email" => "E-mail cím",
			"phone" => "Telefonszám",
			"mobile" => "Mobil",
			"-" => "-",
			// "token" => "Token",
			"code" => "Azonosító",
			"progressCode" => "Ügyfélkód",
			"dateOut" => "Rögzítve",
			"tireContractNumber" => "Gumitárolási szerződés",
		];
		
		foreach($appUserDatas AS $key => $val)
		{
			if(strpos($key, "-") === 0) { ?><div class="height-30"></div><?php }
			elseif(!empty($appUser["data"]->$key))
			{ 				
				?>
				<div class="profile-row">
					<div class="profile-left">
						<div class="profile-left-inner"><?php echo $val; ?></div>
					</div>
					<div class="profile-right">
						<div class="profile-right-inner"><?php echo $appUser["data"]->$key; ?></div>
					</div>	
					<div class="clear"></div>
				</div>
				<?php 
			}
		}
		foreach($customerDatas AS $key => $val)
		{
			if(strpos($key, "-") === 0) { ?><div class="height-30"></div><?php }
			elseif(!empty($customer[$key])) 
			{ 
				?>
				<div class="profile-row">
					<div class="profile-left">
						<div class="profile-left-inner"><?php echo $val; ?></div>
					</div>
					<div class="profile-right">
						<div class="profile-right-inner"><?php echo $customer[$key]; ?></div>
					</div>	
					<div class="clear"></div>
				</div>
				<?php }
		}
		?>
	</div>
@stop