<?php 
$appUser = $GLOBALS["APPUSER"];
?>
@extends("app")

@section("bodyClass")
	my-bg-gray
@stop

@section("content")
	<div class="my-container">
		<h3 class="font-light text-center text-uppercase font-size-24 line-height-32" style="margin-bottom: 0;">Hűségkártya száma:</h3>
		<?php 
		#Basic datas
		$formDatas = [
			"action" => $GLOBALS["URL"]->currentURL,
			"postData" => [
				"loyaltyCard" => $appUser["data"]->loyaltyCard,
			],
			"success" => false,
			"errors" => "",
		];

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
					<div class="text-success form-msg"><p style="line-height: 150%;">A változtatást a rendszer elmentette!</p></div> 
					<div class="height-10"></div>
				<?php } if(isset($formDatas["errors"]) AND !empty($formDatas["errors"])) { ?>
					<div class="color-red form-msg"><p style="line-height: 150%;">Váratlan hiba történt!</p></div> 
					<div class="height-10"></div>
				<?php } ?>
				<form class="form-horizontal myform-horizontal" action="<?php echo $formDatas["action"]; ?>" method="post" onsubmit="formLoading()" <?php if($formDatas["hasFileUpload"]) { ?>enctype="multipart/form-data"<?php } ?> id="myform-form">
					<input type="hidden" name="_referer" value="<?php echo $GLOBALS["URL"]->currentURL; ?>">
					<input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
					<input type="hidden" name="process" value="1">	

					<div class="form-group"><input type="text" class="form-control input-lg" name="loyaltyCard" value="<?php echo $formDatas["postData"]["loyaltyCard"]; ?>"></div>
					
					<div class="text-center" id="myform-loading-btn"><button type="submit" class="my-btn transition" id="myform-submit-button">MENTÉS</button></div>
					<div class="text-center display-none" id="myform-loading-txt">
						<div class="height-10"></div>
						<div class="fa-3x color-primary"><i class="fa fa-spinner fa-spin"></i></div>
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