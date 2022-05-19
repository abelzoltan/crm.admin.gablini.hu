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
	<?php 
	$row = $GLOBALS["user"];
	$panels = [
		[
			"name" => "Információ",
			"inputs" => [
				[
					"control-label" => "Vezetéknév",
					"type" => "text",
					"input" => [
						"name" => "lastName",
						"value" => $row["data"]->lastName,
						"readonly" => true,
					],
				],
				[
					"control-label" => "Keresztnév",
					"type" => "text",
					"input" => [
						"name" => "firstName",
						"value" => $row["data"]->firstName,
						"readonly" => true,
					],
				],
				[
					"control-label" => "E-mail cím",
					"type" => "email",
					"input" => [
						"name" => "email",
						"value" => $row["data"]->email,
						"readonly" => true,
					],
				],
				[
					"control-label" => "Rang",
					"type" => "text",
					"input" => [
						"name" => "rank",
						"value" => $row["rank"]->name,
						"readonly" => true,
					],
				],
				[
					"control-label" => "Pozíció",
					"type" => "text",
					"input" => [
						"name" => "rank",
						"value" => $row["position"]->nameOut,
						"readonly" => true,
					],
				],
				[
					"type" => "ln_solid",
				],
				[
					"control-label" => "Azonosító (Token)",
					"type" => "text",
					"input" => [
						"name" => "token",
						"value" => $row["data"]->token,
						"readonly" => true,
					],
				],
				[
					"control-label" => "Regisztráció / Létrehozás ideje",
					"type" => "text",
					"input" => [
						"name" => "regDate",
						"value" => $row["data"]->regDate,
						"readonly" => true,
					],
				],
				[
					"control-label" => "Utolsó belépés",
					"type" => "text",
					"input" => [
						"name" => "lastLogin",
						"value" => $row["data"]->lastLogin,
						"readonly" => true,
					],
				],
				
			],
			"buttons" => [],
		],
	];

	$details = [
		"action" => "#",
		"method" => "post",
	];

	$form = new \App\Http\Controllers\FormController();
	echo $form->createForm($details, $panels);
	?>
@stop