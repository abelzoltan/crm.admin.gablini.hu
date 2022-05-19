<?php 
$car = $VIEW["vars"]["car"];
?>
@extends("app")

@section("content")
	<div class="my-container">
		<h2><?php echo $VIEW["title"]; ?></h2>
		<div class="height-1"></div>
		<div>Kérjük, írja le a szervizlátogatás célját, és kattintson a "Küldés" gombra.</div>
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
		if(!isset($formDatas["postData"]["subject"])) { $formDatas["postData"]["subject"] = (isset($_GET["targy"]) AND !empty($_GET["targy"])) ? $_GET["targy"] : "Szerviz bejelentkezés"; }
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
					<div class="color-success form-msg"><p>Köszönjük! Munkatársunk hamarosan jelentkezik!</p></div> 
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
					<input type="hidden" name="fromWhere" value="sos-help-app">
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
					<div class="form-group"><input type="text" class="form-control input-lg" name="subject" placeholder="Tárgy*" required value="<?php echo $formDatas["postData"]["subject"]; ?>"></div>
					<div class="form-group"><input type="text" class="form-control input-lg" name="name" placeholder="Név*" required value="<?php echo $formDatas["postData"]["name"]; ?>"></div>
					<div class="form-group"><input type="email" class="form-control input-lg" name="email" placeholder="E-mail cím*" required value="<?php echo $formDatas["postData"]["email"]; ?>"></div>
					<div class="form-group"><input type="text" class="form-control input-lg" name="phone" placeholder="Telefonszám*" required value="<?php echo $formDatas["postData"]["phone"]; ?>"></div>
					<div class="form-group"><textarea class="form-control input-lg" name="msg" placeholder="Üzenet" rows="8"><?php echo $formDatas["postData"]["msg"]; ?></textarea></div>
					
					<div class="height-1"></div>
					
					<div class="form-group"><input type="text" class="form-control input-lg" name="chosenDate" onfocus="this.type='date'" onblur="if(this.value == '') this.type='text'" placeholder="Időpont foglalás" value="<?php echo $formDatas["postData"]["chosenDate"]; ?>"></div>
					<div class="form-group"><input type="text" class="form-control input-lg" name="carType" placeholder="Autó típusa*" required value="<?php echo $formDatas["postData"]["carType"]; ?>"></div>
					<div class="form-group"><input type="number" class="form-control input-lg" name="carYear" placeholder="Autó évjárata" value="<?php echo $formDatas["postData"]["carYear"]; ?>" min="1900" max="<?php echo date("Y"); ?>"></div>
					<div class="form-group"><input type="text" class="form-control input-lg" name="carRegNumber" placeholder="Autó rendszáma*" required value="<?php echo $formDatas["postData"]["carRegNumber"]; ?>"></div>
					<div class="form-group"><input type="text" class="form-control input-lg" name="carBodyNumber" placeholder="Autó alvázszáma*" required value="<?php echo $formDatas["postData"]["carBodyNumber"]; ?>"></div>
					<div class="form-group text-center">
						<label class="myform-checkbox">
							<input type="checkbox" name="acceptTerms" value="1" required <?php if($formDatas["postData"]["acceptTerms"]) { ?>checked<?php } ?>>
							<span>Elolvastam és elfogadom az <a href="<?php echo PATH_ROOT_WEB; ?>gdpr/gablini_adatvedelmi_tajekoztato_es_hozzajarulas_adatkezeleshez.pdf" target="_blank" class="link transition">adatvédelmi nyilatkozatot</a>.</span>
						</label>	
					</div>
					<div class="form-group text-center">Amennyiben rendszeresen tájékoztatást szeretne kapni a GABLINI akcióiról, újdonságairól, híreiről, regisztráljon és legyen hírlevelünk olvasója!</div>
					<div class="form-group text-center">
						<div class="radio-inline-text">Feliratkozom a hírlevélre:</div>
						<label class="radio-inline">
							<input type="radio" name="newsletter" value="1">
							<div class="radio-inline-bg transition"></div>
							<span>Igen</span>
						</label>
						<label class="radio-inline">
							<input type="radio" name="newsletter" value="0">
							<div class="radio-inline-bg transition"></div>
							<span>Nem</span>
						</label>
					</div>
					<div class="form-group text-center g-recaptcha-container">
						<script src="https://www.google.com/recaptcha/api.js"></script>
						<div class="g-recaptcha display-inline-block" data-sitekey="<?php echo $MAIN["FORM"]->recaptchaSiteKey; ?>"></div>
					</div>
					<div class="text-center" id="myform-loading-btn"><button type="submit" class="my-btn transition" id="myform-submit-button">KÜLDÉS</button></div>
					<div class="text-center display-none" id="myform-loading-txt">
						<img src="<?php echo PATH_ROOT_WEB; ?>pics/form-loading.gif" alt="A feldolgozás folyamatban, köszönjük a türelmét!" class="img-responsive center">
						<div class="color-blue form-msg">A feldolgozás folyamatban, köszönjük a türelmét!</div>
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