<?php 
$car = $VIEW["vars"]["car"];
$titleOriginal = $VIEW["title"];
$VIEW["title"] = "Szerviz";
?>
@extends("app")

@section("bodyClass")
	my-bg-gray
@stop

@section("content")
	<div class="my-container">
		<h3 class="font-light text-center text-uppercase font-size-24 line-height-32" style="margin-bottom: 0;"><?php echo $titleOriginal; ?></h3>
		<div class="height-10"></div>
		<div class="text-myblue text-center text-uppercase">Kérjük, írja le a szervizlátogatás célját, és kattintson a "Küldés" gombra.</div>
		<div class="height-20"></div>
		<?php 
		#Basic datas
		$formDatas = [
			"action" => $GLOBALS["URL"]->currentURL,
			"postData" => [],
			"success" => false,
			"errors" => "",
		];

		#Get datas
		$subjectList = [
			"Szerviz bejelentkezés",
			"Időszakos szerviz",
			"Garanciális probléma",
			"Gumicsere",
		];
		
		if(!isset($formDatas["postData"]["subject"]) AND isset($_GET["targy"]) AND !empty($_GET["targy"])) { $formDatas["postData"]["subject"] = $_GET["targy"]; }
		if(isset($formDatas["postData"]["subject"]) AND !empty($formDatas["postData"]["subject"]) AND !in_array($formDatas["postData"]["subject"], $subjectList)) { $subjectList[] = $formDatas["postData"]["subject"]; }
		
		if(!isset($formDatas["postData"]["name"])) { $formDatas["postData"]["name"] = $GLOBALS["CUSTOMER"]["name"]; }
		if(!isset($formDatas["postData"]["email"])) { $formDatas["postData"]["email"] = $GLOBALS["CUSTOMER"]["email"]; }
		if(!isset($formDatas["postData"]["phone"])) { $formDatas["postData"]["phone"] = $GLOBALS["CUSTOMER"]["phone"]; }
		if($car !== false)
		{
			if(!isset($formDatas["postData"]["carType"])) { $formDatas["postData"]["carType"] = $car["name"]; }
			if(!isset($formDatas["postData"]["carRegNumber"])) { $formDatas["postData"]["carRegNumber"] = $car["regNumber"]; }
			if(!isset($formDatas["postData"]["carBodyNumber"])) { $formDatas["postData"]["carBodyNumber"] = $car["bodyNumber"]; }
			if(!isset($formDatas["postData"]["carYear"])) { $formDatas["postData"]["carYear"] = $car["data"]->carYear; }
		}

		#Process return
		if(isset($_SESSION[SESSION_PREFIX."form-data"]))
		{
			if(isset($_SESSION[SESSION_PREFIX."form-data"]["datas"]) AND !empty($_SESSION[SESSION_PREFIX."form-data"]["datas"])) { $formDatas["postData"] = $_SESSION[SESSION_PREFIX."form-data"]["datas"]; }
			if(isset($_SESSION[SESSION_PREFIX."form-data"]["errors"]) AND !empty($_SESSION[SESSION_PREFIX."form-data"]["errors"])) { $formDatas["errors"] = $_SESSION[SESSION_PREFIX."form-data"]["errors"]; }
			unset($_SESSION[SESSION_PREFIX."form-data"]);
		}
		elseif(isset($_SESSION[SESSION_PREFIX."form-finish"]))
		{
			$formDatas["success"] = true;
			unset($_SESSION[SESSION_PREFIX."form-finish"]);
		}
		?>
		<div id="urlap">
			<div id="myform">
				<script>
				function formLoading()
				{
					$("#myform-loading-btn").hide();
					$("#myform-loading-txt").show();
				}
				</script>
				<?php if(isset($formDatas["success"]) AND $formDatas["success"]) { ?>
					<div class="text-success form-msg"><p style="line-height: 150%;">A kérelmet rögzítettük!<br>Munkatársunk hamarosan felveszi Önnel a kapcsolatot!</p></div> 
					<div class="height-10"></div>
				<?php } if(isset($formDatas["errors"]) AND !empty($formDatas["errors"])) { ?>
					<div class="color-red form-msg">
						<?php
						foreach($formDatas["errors"] AS $msgKey)
						{
							switch($msgKey)
							{
								case "captcha": $msg = "Igazolja, hogy nem robot!"; break;
								case "accept-terms": $msg = "Az adatvédelmi nyilatkozat elfogadása kötelező!"; break;
								case "missing-fields": $msg = "A csillaggal jelölt mezők kitöltése kötelező!"; break;
								case "unknown-type": $msg = "Az űrlap beazonosítása sikertelen! Kérjük próbálja meg ismét!"; break;
								default: $msg = "Váratlan hiba történt! Kérjük próbálja meg ismét!"; break;
							}
							?><p><?php echo $msg; ?></p><?php
						}
						?>
					</div> 
					<div class="height-10"></div>
				<?php } ?>
				<form class="form-horizontal myform-horizontal" action="<?php echo $formDatas["action"]; ?>" method="post" onsubmit="formLoading()" <?php if($formDatas["hasFileUpload"]) { ?>enctype="multipart/form-data"<?php } ?> id="myform-form">
					<input type="hidden" name="_referer" value="<?php echo $GLOBALS["URL"]->currentURL; ?>">
					<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
					<input type="hidden" name="type" value="szerviz-bejelentkezes">
					<input type="hidden" name="fromWhere" value="<?php echo $GLOBALS["URL"]->routes[0]; ?>">
					<input type="hidden" name="process" value="1">		
					<?php if(isset($_GET["telephely"]) AND !empty($_GET["telephely"])) { ?>
						<input type="hidden" name="premiseAddress" value="<?php echo $_GET["telephely"]; ?>">
					<?php } else { ?>
						<div class="form-group">
							<select class="form-control input-lg" name="premiseAddress" required>
								<option class="myform-select-option-first" value="">Telephely*</option>
								<?php
								$premiseOK = ["m3", "zuglo", "budaors", "godollo"];
								foreach($MAIN["premises"] AS $premiseAddressID => $premiseAddress)
								{
									if(in_array($premiseAddress["data"]->name, $premiseOK))
									{
										?><option value="<?php echo $premiseAddress["data"]->id; ?>" <?php if($formDatas["postData"]["premiseAddress"] == $premiseAddress["data"]->id) { ?>selected<?php } ?>><?php echo $premiseAddress["data"]->nameOut; ?></option><?php
									}
								}
								?>
							</select>
						</div>
					<?php } ?>
					<div class="form-group">
						<select class="form-control input-lg" name="subject" required>
							<option class="myform-select-option-first" value="">Tárgy*</option>
							<?php foreach($subjectList AS $subjectHere) { ?>
								<option value="<?php echo $subjectHere; ?>" <?php if($formDatas["postData"]["subject"] == $subjectHere) { ?>selected<?php } ?>><?php echo $subjectHere; ?></option>
							<?php } ?>	
						</select>
					</div>
					<div class="form-group"><input type="text" class="form-control input-lg" name="name" placeholder="Név*" required readonly value="<?php echo $formDatas["postData"]["name"]; ?>"></div>
					<div class="form-group"><input type="email" class="form-control input-lg" name="email" placeholder="E-mail cím*" required value="<?php echo $formDatas["postData"]["email"]; ?>"></div>
					<div class="form-group"><input type="text" class="form-control input-lg" name="phone" placeholder="Telefonszám*" required value="<?php echo $formDatas["postData"]["phone"]; ?>"></div>
					<div class="form-group"><textarea class="form-control input-lg" name="msg" placeholder="Üzenet" rows="8"><?php echo $formDatas["postData"]["msg"]; ?></textarea></div>
					
					<div class="height-1"></div>
					
					<div class="form-group"><input type="text" class="form-control input-lg" name="chosenDate" onfocus="this.type='date'" onblur="if(this.value == '') this.type='text'" placeholder="Időpont foglalás" value="<?php echo $formDatas["postData"]["chosenDate"]; ?>"></div>
					<div class="form-group"><input type="text" class="form-control input-lg" name="carType" placeholder="Autó típusa*" required value="<?php echo $formDatas["postData"]["carType"]; ?>"></div>
					<div class="form-group"><input type="number" class="form-control input-lg" name="carYear" placeholder="Autó évjárata" value="<?php echo $formDatas["postData"]["carYear"]; ?>" min="1900" max="<?php echo date("Y"); ?>"></div>
					<div class="form-group"><input type="text" class="form-control input-lg" name="carRegNumber" placeholder="Autó rendszáma*" required value="<?php echo $formDatas["postData"]["carRegNumber"]; ?>"></div>
					<div class="form-group"><input type="text" class="form-control input-lg" name="carBodyNumber" placeholder="Autó alvázszáma*" required value="<?php echo $formDatas["postData"]["carBodyNumber"]; ?>"></div>
					
					<h3 class="font-light text-center" style="margin-bottom: 0;">Hozom-viszem:</h3>
					<div class="height-5"></div>
					<div class="form-group text-center" style="margin-top: 0;">
						<label class="radio-inline">
							<input type="radio" name="serviceBringTake" value="1" <?php if(isset($formDatas["postData"]["serviceBringTake"]) AND $formDatas["postData"]["serviceBringTake"]) { ?>checked<?php } ?>>
							<div class="radio-inline-bg transition"></div>
							<span>Igen</span>
						</label>
						<label class="radio-inline">
							<input type="radio" name="serviceBringTake" value="0" <?php if(isset($formDatas["postData"]["serviceBringTake"]) AND !$formDatas["postData"]["serviceBringTake"]) { ?>checked<?php } ?>>
							<div class="radio-inline-bg transition"></div>
							<span>Nem</span>
						</label>
					</div>
					<h3 class="font-light text-center" style="margin-bottom: 0;">Csereautó:</h3>
					<div class="height-5"></div>
					<div class="form-group text-center" style="margin-top: 0;">
						<label class="radio-inline">
							<input type="radio" name="serviceCourtesyCar" value="1" <?php if(isset($formDatas["postData"]["serviceCourtesyCar"]) AND $formDatas["postData"]["serviceCourtesyCar"]) { ?>checked<?php } ?>>
							<div class="radio-inline-bg transition"></div>
							<span>Igen</span>
						</label>
						<label class="radio-inline">
							<input type="radio" name="serviceCourtesyCar" value="0" <?php if(isset($formDatas["postData"]["serviceCourtesyCar"]) AND !$formDatas["postData"]["serviceCourtesyCar"]) { ?>checked<?php } ?>>
							<div class="radio-inline-bg transition"></div>
							<span>Nem</span>
						</label>
					</div>
					
					<div class="text-center" id="myform-loading-btn"><button type="submit" class="my-btn transition" id="myform-submit-button">KÜLDÉS</button></div>
					<div class="text-center display-none" id="myform-loading-txt">
						<div class="height-10"></div>
						<div class="fa-3x color-primary"><i class="fa fa-spinner fa-spin"></i></div>
						<div class="height-5"></div>
						<div class="color-primary form-msg">A feldolgozás folyamatban, köszönjük a türelmét!</div>
					</div>
				</form>	
			</div>
			<!--[if lte IE 9]>
				<script>
				$("[placeholder]").each(function(){
					if($(this).val() == "" || $(this).val() == null) { $(this).val($(this).attr("placeholder")); }
				});
				</script>
			<![endif]-->
		</div>
		<div class="height-10"></div>
	</div>
@stop