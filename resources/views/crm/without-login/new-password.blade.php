@extends("crm")

@section("content")
	<script>
	function formCheck()
	{
		if($("#password1").val() != $("#password2").val())
		{
			alert("A jelszavak nem egyeznek!");
			return false;
		}
		else { return true; }
	}
	</script>
	<form method="post" action="<?php echo $GLOBALS["URL"]->link([$GLOBALS["URL"]->routes[0]]); ?>" class="form-horizontal" onsubmit="return formCheck()">
		<?php echo csrf_field(); ?>
		<input type="hidden" name="process" value="1">
		<input type="hidden" name="hash" value="<?php echo $GLOBALS["URL"]->routes[1]; ?>">
		<div class="row">
			<div class="col-lg-6 col-md-8 col-xs-10 col-lg-offset-3 col-md-offset-2 col-xs-offset-1 font-size-15">
				<div class="panel panel-default">
					<div class="panel-heading text-center font-bold font-size-22"><?php echo $VIEW["title"]; ?></div>
					<div class="panel-body">
						<?php 
						if(isset($_GET["error"]) AND !empty($_GET["error"]))
						{
							switch($_GET["error"])
							{
								default:
									$errorMsgHere = "A jelszavak nem egyeznek!";
									break;
							}
							?>
							<h4 class="text-center color-danger font-bold"><?php echo $errorMsgHere; ?></h4>
							<div class="height-20"></div>
							<?php
						}
						?>
						<div class="form-group">
							<label class="control-label col-md-3 col-sm-2 col-xs-12" for="password1">Új jelszó:</label>
							<div class="col-md-9 col-sm-10 col-xs-12"><input type="password" name="password1" id="password1" class="form-control" autofocus required></div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3 col-sm-2 col-xs-12" for="password2">Új jelszó ismét:</label>
							<div class="col-md-9 col-sm-10 col-xs-12"><input type="password" name="password2" id="password2" class="form-control" required></div>
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
				<div class="text-center"><a href="<?php echo PATH_WEB; ?>"><span class="color-white">&laquo; Mégis bejelentkezem</span></a></div>
			</div>
		</div>
	</form>
@stop	