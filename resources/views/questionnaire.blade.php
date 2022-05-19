<?php 
error_reporting(E_ALL ^ E_NOTICE);
if(isset($MAIN["error"])) { $error = $MAIN["error"]; }
if(isset($MAIN["questionnaire"])) { $data = $MAIN["questionnaire"]; }
if(isset($MAIN["customer"])) { $customer = $MAIN["customer"]; }
if(isset($MAIN["questionnaireModel"])) { $qForm = $MAIN["questionnaireModel"]; }
?>
<!DOCTYPE html>
<html lang="hu">
<head>
	<meta charset="utf-8">
	<title>
		<?php 
		if(!empty($VIEW["titlePrefix"])) { echo $VIEW["titlePrefix"]; } 		
		if(!empty($VIEW["title"])) { echo $VIEW["title"]; }
		elseif(!empty($VIEW["meta"]["og:title"])) { echo $VIEW["meta"]["og:title"]; }
		else { ?>Gablini.hu<?php }		
		if(!empty($VIEW["titleSuffix"])) { echo $VIEW["titleSuffix"]; } 
		?>
	</title>

	<!-- Meta datas -->	
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="revisit-after" content="7 days">
	<meta name="language" content="hu_hu">
	<meta name="country" content="hu">
	<meta name="subject" content="commercial">
	<meta name="resource-type" content="document">
	<meta name="rating" content="general">
	<meta name="robots" content="all,follow">
	<meta name="language" content="HU_hu">
	<meta name="country" content="HU">
	<meta name="distribution" content="global">
	<meta name="theme-color" content="#0f0f0f">
	<meta name="msapplication-navbutton-color" content="#0f0f0f">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="#0f0f0f">

	<!-- Cache -->
	<?php header("Cache-Control: max-age=604800"); ?>
	<meta name="expires" content="<?php echo date("D, d M Y", strtotime("+1 week")); ?>">

	<!-- Favicon -->	
	<link rel="icon" href="<?php echo PATH_ROOT_WEB; ?>pics/favicon.png" type="image/png">
	<link rel="shortcut icon" href="<?php echo PATH_ROOT_WEB; ?>pics/favicon.png" type="image/png"> 
	
	<!-- jQuery -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

	<!-- Stylesheets -->
	<link rel="stylesheet" href="<?php echo PATH_ROOT_WEB; ?>vendors/bootstrap-3.3.7/css/bootstrap.min.css">
	
	<link href="<?php echo PATH_ROOT_WEB; ?>css/classes.min.css" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo PATH_WEB; ?>css/questionnaire.css">		

	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
		
	<!-- Changeable meta -->
	<?php 
	foreach($VIEW["meta"] AS $key => $val)
	{
		if(!empty($val))
		{
			if(substr($key, 0, 3) == "og:") { $keyAttr = "property"; }
			else { $keyAttr = "name"; }
			?><meta <?php echo $keyAttr; ?>="<?php echo $key; ?>" content="<?php echo $val; ?>"><?php
		}
	}
	
	#Brand based style
	if(isset($data))
	{
		?>	
		<style>
			<?php if(!empty($data["colors"]["bg"])) { ?>
				body{
					background-color: <?php echo $data["colors"]["bg"]; ?>;
				}
			<?php } if(!empty($data["colors"]["bgTop"])) { ?>
				#background-top{
					background-color: <?php echo $data["colors"]["bgTop"]; ?>;
				}
				
				.my-input:focus{
					border-color: <?php echo $data["colors"]["bgTop"]; ?>;
				}
				
				.my-checkbox:checked + .my-checkbox-input, .my-radio:checked + .my-radio-input{
					border-color: <?php echo $data["colors"]["bgTop"]; ?>;
				}

				.my-checkbox:checked + .my-checkbox-input .my-checkbox-input-content, .my-radio:checked + .my-radio-input .my-radio-input-content{
					background-color: <?php echo $data["colors"]["bgTop"]; ?>;
				}
			<?php } if(!empty($data["colors"]["formTop"])) { ?>
				#form-border-top{
					background-color: <?php echo $data["colors"]["formTop"]; ?>;
				}
			<?php } if(!empty($data["bgImage"])) { ?>
				#background-top{
					background-image: url("<?php echo $data["bgImage"]; ?>");
					background-position: center;
				}
			<?php } ?>
		</style>
		<?php
	}
	?>
</head>	
<body>
	<div id="background-top">
		<?php if(isset($VIEW["vars"]["logo"]) AND !empty($VIEW["vars"]["logo"])) { ?>
			<div id="background-top-img">
				<div class="vertical-middle-table">
					<div class="vertical-middle-td">
						<img src="<?php echo $VIEW["vars"]["logo"]; ?>?v=2021" alt="<?php echo $VIEW["title"]; ?>">
					</div>
				</div>
			</div>
		<?php } ?>
	</div>
	<div id="form-container">
		<div id="form-border-top"></div>
		<div id="form">
			<?php
			if(isset($_SESSION[SESSION_PREFIX."answerReturn"]) AND $_SESSION[SESSION_PREFIX."answerReturn"]["success"])
			{
				if(strpos($data["code"], "newcarSellingEvents") !== false)
				{
					?>
					<div id="form-title" class="form-success text-center font-bold">
						A kitöltött kérdőívet megkaptuk, köszönjük az idejét és kellemes utazást kívánunk az új autójával!<br><br>
						Várjuk az első éves szervizen, időpont foglalás miatt keresse kollegánkat.
					</div>
					<?php
				}
				else
				{
					?>
					<div id="form-title" class="form-success text-center font-bold">
						Köszönjük, hogy kitöltötte kérdőívünket!<br>
						Reméljük elégedett volt szolgáltatásunkkal!
					</div>
					<?php
				}
			}
			elseif(isset($error) AND !empty($error))
			{
				switch($error)
				{
					#Questionnaire
					case "active-from":
						$title = "Kérdőív hiba!";
						$text = "Sajnáljuk, de a kérdőív jelenleg még nem elérhető! Kérjük próbálja meg később.";
						break;	
					case "active-to":
						$title = "Kérdőív hiba!";
						$text = "Sajnáljuk, de a kérdőív kitöltése már nem lehetséges!";
						break;	
					case "active":
						$title = "Kérdőív hiba!";
						$text = "Sajnáljuk, de a kérdőív nem elérhető!";
						break;
					case "no-match":
						$title = "Kérdőív hiba!";
						$text = "Sajnáljuk, de a keresett kérdőív nem található!";
						break;
					case "wrong-questionnaire":
						$title = "Kérdőív hiba!";
						$text = "Sajnáljuk, de ez a kérdőív az Ön számára nem elérhető!";
						break;
					#Customer
					case "no-customer-hash":
						$title = "Ügyfél hiba!";
						$text = "Sajnáljuk, de az űrlapot csak cégünk ügyfelei tölthetik ki!";
						break;
					case "wrong-customer-hash":
						$title = "Ügyfél hiba!";
						$text = "Sajnáljuk, de az ügyfél-azonosítás sikertelen!";
						break;
					#Foreign row
					case "no-foreign-hash":
					case "no-foreign-controller":
					case "wrong-foreign-hash":
						$title = "Azonosítási hiba!";
						$text = "Sajnáljuk, de az űrlap kitöltéséhez nincs megfelelő jogosultsága!";
						break;
					case "customer-id-match":
						$title = "Azonosítási hiba!";
						$text = "Sajnáljuk, de a kitöltéshez szükséges adatok eltérést mutatnak. Az űrlap kitöltéséhez nincs megfelelő jogosultsága!";
						break;
					#Answer already exists	
					case "existing-answer":
						$title = $VIEW["title"];
						$text = "<span class='form-success'>Ön már korábban kitöltötte ezt a kérdőívünket. Köszönjük, hogy válaszaival segíti a munkánkat!</span>";
						break;	
					#No questions, Default	
					case "no-questions":
					default:
						$title = "Váratlan hiba!";
						$text = "Sajnáljuk, de az űrlap nem jeleníthető meg!";
						break;	
				}
				?>
				<div id="form-title"><?php echo $title; ?></div>
				<div class="form-text form-error font-bold"><?php echo $text; ?></div>
				<?php
			}
			elseif(!isset($data) OR empty($data) OR !$data)
			{
				?>
				<div id="form-title">Váratlan hiba!</div>
				<div class="form-text form-error">Sajnáljuk, de az űrlap nem jeleníthető meg!</div>
				<?php
			}
			else
			{
				$inputTypes = $data["inputTypes"];
				$customerName = $customer["name"];
				$customerEmail = $customer["email"];
				
				if(strpos($data["code"], "newcarSellingEvents") !== false)
				{
					$labelName = "Az Ön neve";
					$labelEmail = "Az Ön e-mail címe";
				}
				else
				{
					$labelName = "Név";
					$labelEmail = "E-mail cím";
				}
				
				?>
				<div id="form-title"><?php echo $data["name"]; ?></div>
				<div class="form-text"><?php echo str_replace("{customerName}", $customerName, $data["textTop"]); ?></div>
				<?php 
				if(isset($_SESSION[SESSION_PREFIX."answerReturn"]) AND !$_SESSION[SESSION_PREFIX."answerReturn"]["success"])
				{ 
					if(!empty($_SESSION[SESSION_PREFIX."answerReturn"]["errors"]))
					{
						if(in_array("customerData", $_SESSION[SESSION_PREFIX."answerReturn"]["errors"]) OR in_array("required", $_SESSION[SESSION_PREFIX."answerReturn"]["errors"])) { ?><div class="form-text form-error">A csillaggal (<span class="required">*</span>) jelölt kérdésekre kötelező választ adni!</div><?php }
						else { ?><div class="form-text form-error">Váratlan hiba történt! Kérjük próbálja meg újra!</div><?php }
					}
					else { ?><div class="form-text form-error">Váratlan hiba történt! Kérjük próbálja meg újra!</div><?php }
					
					$values = $_SESSION[SESSION_PREFIX."answerReturn"]["datas"]; 
					if(isset($values["_customerName"])) { $customerName = $values["_customerName"]; }
					if(isset($values["_customerEmail"])) { $customerEmail = $values["_customerEmail"]; }
				}
				else { $values = []; }
				?>
				<form id="questionnaire" method="post" action="<?php echo $GLOBALS["URL"]->currentURL; ?>">
					<?php echo csrf_field(); ?>
					<input type="hidden" name="process" value="1">
					<input type="hidden" name="customer" value="<?php echo $customer["code"]; ?>">
					<input type="hidden" name="questionnaire" value="<?php echo $GLOBALS["URL"]->routes[0]; ?>">
					<input type="hidden" name="hash1" value="<?php echo $GLOBALS["URL"]->routes[1]; ?>">
					<input type="hidden" name="hash2" value="<?php echo $GLOBALS["URL"]->routes[2]; ?>">
					<div class="form-row">
						<div class="form-question"><?php echo $VIEW["vars"]["customerCodeName"]; ?>: <span class="required">*</span></div>
						<div class="form-input"><input type="text" name="_customerCode" class="my-input" value="<?php echo $customer["code"]; ?>" readonly></div>
					</div>
					<div class="form-row">
						<div class="form-question"><?php echo $labelName; ?>: <span class="required">*</span></div>
						<div class="form-input"><input type="text" name="_customerName" class="my-input" value="<?php echo $customerName; ?>"></div>
					</div>
					
					<div class="form-row">
						<div class="form-question"><?php echo $labelEmail; ?>: <span class="required">*</span></div>
						<div class="form-input"><input type="text" name="_customerEmail" class="my-input" value="<?php echo $customerEmail; ?>"></div>
					</div>
					<?php 
					foreach($data["questions"] AS $questionID => $question)
					{
						$inputType = $inputTypes[$question["data"]->inputType];
						if($inputType["url"] == "hidden") { ?><input type="hidden" name="<?php echo $question["inputName"]; ?>" value="<?php echo $question["val"]; ?>"><?php }
						else
						{
							?>
							<div class="form-row">
								<div class="form-question"><?php echo $question["questionHTML"]; ?></div>
								<div class="form-input">
									<?php
									if(isset($values[$question["inputName"]])) { $val = $values[$question["inputName"]]; }
									else { $val = $question["val"];	}							
									
									switch($inputType["url"])
									{
										case "select":
											?>
											<select class="my-input my-select" name="<?php echo $question["inputName"]; if($question["data"]->multiple) { ?>[]<?php } ?>" <?php echo $question["attributesHTML"]; if($question["data"]->multiple) { ?> size="<?php echo count($question["options"]); ?>"<?php } ?>>
												<?php 
												if(!empty($question["placeholder"])) { ?><option value=""><?php echo $question["placeholder"]; ?></option><?php }
												foreach($question["options"] AS $option) { ?>
													<option value="<?php echo $option; ?>" <?php if($option == $val) { ?>selected<?php } ?>><?php echo $option; ?></option>
												<?php } ?>
											</select>
											<?php
											break;	
										case "textarea":
											?><textarea class="my-input my-textarea" name="<?php echo $question["inputName"]; ?>" <?php echo $question["attributesHTML"]; ?>><?php echo $val; ?></textarea><?php
											break;
										case "checkbox":
											if(!isset($val) OR empty($val) OR !is_array($val)) { $val = []; }
											foreach($question["options"] AS $option)
											{
												?>
												<label class="my-checkbox-container">
													<input type="checkbox" class="my-checkbox" name="<?php echo $question["inputName"]; ?>[]" value="<?php echo $option; ?>" <?php if(in_array($option, $val)) { ?>checked <?php } echo $question["attributesHTML"]; ?>>
													<div class="my-checkbox-input"><div class="my-checkbox-input-content"></div></div>
													<div class="my-checkbox-label"><?php echo $option; ?></div>
												</label>
												<div class="clear"></div>												
												<?php
											}
											break;	
										case "radio":
											foreach($question["options"] AS $option)
											{
												?>
												<label class="my-radio-container">
													<input type="radio" class="my-radio" name="<?php echo $question["inputName"]; ?>" value="<?php echo $option; ?>" <?php if($val == $option) { ?>checked <?php } echo $question["attributesHTML"]; ?>>
													<div class="my-radio-input"><div class="my-radio-input-content"></div></div>
													<div class="my-radio-label"><?php echo $option; ?></div>
												</label>
												<div class="clear"></div>												
												<?php
											}
											break;	
										case "text":
										case "email":
										case "number":
										case "date":
											$inputTypeHere = $inputType["url"];
										default:
											if(!isset($inputTypeHere)) { $inputTypeHere = "text"; }
											?><input type="<?php echo $inputTypeHere; ?>" class="my-input" name="<?php echo $question["inputName"]; ?>" value="<?php echo $val; ?>" <?php echo $question["attributesHTML"]; ?>><?php
											unset($inputTypeHere);
											break;	
									}
									?>
								</div>
							</div>
							<?php
						}
					}
					?>
					
					<div class="form-row">
						<div class="form-question"><?php echo $qForm->fromWhereQuestion; ?><span class="required">*</span></div>
						<div class="form-input">
							<select class="my-input my-select" name="_fromWhere" required onchange="fromWhereChange(this)">
								<?php 
								$fromWhereVal = (isset($values["_fromWhere"])) ? $values["_fromWhere"] : NULL;
								if(!empty($qForm->fromWhereQuestionPlaceholder)) { ?><option value=""><?php echo $qForm->fromWhereQuestionPlaceholder; ?></option><?php }	
								foreach($qForm->fromWheres() AS $fromWhereKey => $fromWhere)
								{
									if($fromWhere["active"]) { ?><option value="<?php echo $fromWhereKey; ?>" <?php if($fromWhereKey == $fromWhereVal) { ?>selected<?php } ?>><?php echo $fromWhere["name"]; ?></option><?php }
								} 
								?>
							</select>
							<input class="my-input" name="_fromWhereText" value="<?php if(isset($values["_fromWhereText"])) { echo $values["_fromWhereText"]; } ?>" placeholder="<?php echo $qForm->fromWhereTextQuestionPlaceholder; ?>" id="fromWhereTextInput" <?php if($fromWhereVal == "egyeb") { ?>required<?php } else { ?>style="display: none;"<?php } ?>>
						</div>
					</div>
					<script>
						function fromWhereChange(e)
						{
							if($(e).val() == "egyeb") 
							{ 
								$("#fromWhereTextInput").slideDown(); 
								$("#fromWhereTextInput").prop("required", true); 
							}
							else
							{ 
								$("#fromWhereTextInput").prop("required", false); 
								$("#fromWhereTextInput").slideUp(); 
							}
						}
					</script>
					
					<div class="form-row form-button"><button type="submit" class="my-btn">Küldés</button></div>
				</form>
				<div class="form-text"><?php echo $data["textBottom"]; ?></div>
				<?php
			}
			?>
		</div>
	</div>
	
	<!--[if lte IE 9]>
		<script>
		$("[placeholder]").each(function(){
			if($(this).val() == "" || $(this).val() == null) { $(this).val($(this).attr("placeholder")); }
		});
		</script>
	<![endif]-->
</body>
</html>
<?php if(isset($_SESSION[SESSION_PREFIX."answerReturn"])) { unset($_SESSION[SESSION_PREFIX."answerReturn"]); } ?>