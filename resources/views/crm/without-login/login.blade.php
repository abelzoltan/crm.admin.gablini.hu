@extends("crm")

@section("content")
	<form method="post" action="<?php echo $GLOBALS["URL"]->link(["login"]); ?>" class="form-horizontal">
		<?php echo csrf_field(); ?>
		<input type="hidden" name="login-process" value="1">
		<input type="hidden" name="login-url" value="<?php if(isset($_GET["url"])) { echo $_GET["url"]; } ?>">
		<input type="hidden" name="login-get" value="<?php if(isset($_GET["get"])) { echo $_GET["get"]; } ?>">
		<div class="row">
			<div class="col-lg-6 col-md-8 col-xs-10 col-lg-offset-3 col-md-offset-2 col-xs-offset-1 font-size-15">
				<div class="panel panel-default">
					<div class="panel-heading text-center font-bold font-size-22"><?php echo $VIEW["title"]; ?></div>
					<div class="panel-body">
						<?php 
						if(isset($_GET["success"]) AND !empty($_GET["success"]))
						{
							switch($_GET["success"])
							{
								case "new-password":
									$errorMsgHere = "Az új jelszó beállítása sikeresen megtörtént!";
									break;
							}
							?>
							<h4 class="text-center color-success font-bold"><?php echo $errorMsgHere; ?></h4>
							<div class="height-20"></div>
							<?php
						}
						if(isset($_GET["error"]) AND !empty($_GET["error"]))
						{
							switch($_GET["error"])
							{
								case "required":
									$errorMsgHere = "Az oldal megtekintéséhez bejelentkezés szükséges!";
									break;
								case "datas":
									$errorMsgHere = "Hibás e-mail cím vagy jelszó. Próbálja újra!";
									break;
								case "rank":
									$errorMsgHere = "Ön nem rendelkezik megfelelő jogosultsággal a bejelentkezéshez!";
									break;
								case "deleted":
									$errorMsgHere = "Az ön felhasználói fiókját töröltük!";
									break;	
								default:
									$errorMsgHere = "Váratlan hiba!";
									break;
							}
							?>
							<h4 class="text-center color-danger font-bold"><?php echo $errorMsgHere; ?></h4>
							<div class="height-20"></div>
							<?php
						}
						?>
						<div class="form-group">
							<label class="control-label col-md-3 col-sm-2 col-xs-12" for="email">E-mail cím:</label>
							<div class="col-md-9 col-sm-10 col-xs-12"><input type="email" name="email" id="email" class="form-control" autofocus="" value=""></div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3 col-sm-2 col-xs-12" for="pwd">Jelszó:</label>
							<div class="col-md-9 col-sm-10 col-xs-12"><input type="password" name="password" id="pwd" class="form-control"></div>
						</div>
						<div class="form-group"> 
							<div class="text-center col-xs-12">
								<div class="checkbox"><label><input type="checkbox" name="remember"> Maradjon bejelentkezve</label></div>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<div class="text-center">
							<div class="height-5"></div>
							<button type="submit" class="btn btn-lg btn-primary" style="margin: 0;">Bejelentkezés</button>
							<div class="height-5"></div>
						</div>
					</div>
				</div>
				<div class="text-center"><a href="<?php echo PATH_WEB; ?>forgot-password"><span class="color-white">Elfelejtettem a jelszavam</span></a></div>
			</div>
		</div>
	</form>
@stop