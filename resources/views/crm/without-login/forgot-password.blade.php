@extends("crm")

@section("content")
	<form method="post" action="<?php echo $GLOBALS["URL"]->link([$GLOBALS["URL"]->routes[0]]); ?>" class="form-horizontal">
		<?php echo csrf_field(); ?>
		<input type="hidden" name="process" value="1">
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
								default:
									$errorMsgHere = "Az új jelszó igénylése sikeresen megtörtént!";
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
								case "token":
									$errorMsgHere = "A megadott token érvénytelen vagy lejárt!";
									break;
								default:
									$errorMsgHere = "Az e-mail cím nem található az adatbázisban!";
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
							<div class="col-md-9 col-sm-10 col-xs-12"><input type="email" name="email" id="email" class="form-control" autofocus required></div>
						</div>
						<div class="form-group"> 
							<div class="height-10"></div>
							<div class="text-center col-xs-12 font-italic">Ha a megadott e-mail cím létezik az adatbázisban, e-mail üzenetben értesítjük a további teendőkről!</div>
						</div>
					</div>
					<div class="panel-footer">
						<div class="text-center">
							<div class="height-5"></div>
							<button type="submit" class="btn btn-lg btn-primary" style="margin: 0;">Új jelszó igénylése</button>
							<div class="height-5"></div>
						</div>
					</div>
				</div>
				<div class="text-center"><a href="<?php echo PATH_WEB; ?>"><span class="color-white">&laquo; Vissza a bejelentkezéshez</span></a></div>
			</div>
		</div>
	</form>
@stop	