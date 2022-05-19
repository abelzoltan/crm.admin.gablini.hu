@extends("crm")

@section("titleRight")
	@include("crm._title-right-print-btn")
	@include("crm._title-right-export-btn")
@stop	

@section("headBottom")
	@include("crm._print-page")
@stop

@section("style")
	.title_right .btn + .clear{
		clear: none;
	}
	
	.service-todo-container .table-responsive{
		overflow-x: visible;
	}
	
	.service-todo-table-container{
		width: 100%;
		overflow-x: auto;
	}
	
	.printing-visible{
		display: none;
	}
	
	.service-todo-table-container .bg-red, .bg-green{
		color: #000;
	}
@stop

@section("bodyBottom")
	<?php if(!(isset($_GET["nyomtatas"]) AND $_GET["nyomtatas"]))  { ?>
	<script src="<?php echo GENTELELLA_DIR; ?>vendors/jQuery.TableHeadFixer/tableHeadFixer.js"></script>
	<script>
		$(".service-todo-container .data-table").wrap( "<div class='service-todo-table-container'></div>" );
		// $(".service-todo-container .data-table").tableHeadFixer({'left' : 5});
	</script>
	<?php } ?>
	@include("crm.services._todo-check")
@stop

@section("content")
	<?php
	if(isset($VIEW["vars"]["todoError"]) AND !empty($VIEW["vars"]["todoError"]))
	{
		switch($VIEW["vars"]["todoError"])
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
			#No questions, Default	
			case "no-questions":
			default:
				$title = "Váratlan hiba!";
				$text = "Sajnáljuk, de az űrlap nem jeleníthető meg!";
				break;	
		}
		?>
		<div class="x_panel">
			<div class="x_title">
				<div class="row">
					<div class="col-xs-12">
						<h2 class="font-bold color-red"><?php echo $title; ?></h2>
						<ul class="nav navbar-right panel_toolbox">
							<li><a class="close-link"><i class="fa fa-close"></i></a></li>
							<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="x_content">
				<p class="color-red font-bold font-size-16 line-leight-22"><?php echo $text; ?></p>
			</div>
		</div>
		<?php
	}
	else
	{
		$panel = $VIEW["vars"]["LIST"];
		?>
		<div class="font-size-18 line-height-26 font-bold color-blue printing-hidden">Kérdőív: <?php echo $VIEW["vars"]["questionnaireName"]; ?></div>
		<div class="height-10 printing-hidden"></div>
		<div class="service-todo-container service-todo-container-<?php echo $GLOBALS["URL"]->routes[1]; ?>">
			<form action="#" onsubmit="return false;">
				<?php echo csrf_field(); ?>
				@include("crm._list-panel") 
			</form>
		</div>
		<div class="modal fade printing-hidden" id="form-modal" tabindex="-1" role="dialog" aria-labelledby="form-modal" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title">HIBA!</h4>
					</div>
					<div class="modal-body"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Bezár</button>
					</div>
				</div>
			</div>
		</div>
		<script>
		function fromWhereChange(e, eventID)
		{
			// console.log(eventID);
			if(eventID != "" && eventID != "undefined")
			{
				fromWhereTextInputSelector = "#services-todo-question-fromWhereText-" + eventID;
				if($(e).val() == "egyeb") 
				{ 
					$(fromWhereTextInputSelector).show(); 
					$(fromWhereTextInputSelector).prop("required", true); 
				}
				else
				{ 
					$(fromWhereTextInputSelector).prop("required", false); 
					$(fromWhereTextInputSelector).hide(); 
				}
			}
		}
		
		function phoneChange(e, customerID, type)
		{
			$.ajax({
				type: "POST",
				url: "<?php echo PATH_WEB.$GLOBALS["URL"]->routes[0]; ?>/work-phone",
				headers: {"X-CSRF-TOKEN": $("[name='_token']").val()},
				data: "customer=" + customerID + "&type=" + type + "&value=" + $(e).parents(".input-group").find("input").val(),
				dataType: "html",
				success: function(msg) {
					console.log($(e).parents(".input-group").find("input").val());
					if(msg == "ok") { $(e).parents(".input-group").addClass("has-success"); }
					else { $(e).parents(".input-group").addClass("has-error"); }
					setTimeout(function() {
						$(e).parents(".input-group").removeClass("has-success").removeClass("has-error");
					}, 1000);
				},
			});
		}
		function serviceStatusChange(eventID)
		{
			if(eventID != "" && eventID != "undefined")
			{
				if($(".services-todo-status-" + eventID).val() != "") { $(".services-todo-question-" + eventID).hide(); }
				else { $(".services-todo-question-" + eventID).show(); }
			}
		}
		function sendAnswer(eventID)
		{
			if(eventID != "" && eventID != "undefined")
			{
				if($(".services-todo-status-" + eventID).val() != "")
				{
					$.ajax({
						type: "POST",
						url: "<?php echo PATH_WEB.$GLOBALS["URL"]->routes[0]; ?>/work-status",
						headers: {"X-CSRF-TOKEN": $("[name='_token']").val()},
						data: "status=" + $(".services-todo-status-" + eventID).val() + "&comment=" + $(".services-todo-comment-" + eventID).val() + "&eventID=" + eventID,
						dataType: "html",
						success: function(msg) {
							if(msg == "ok")
							{
								$("#panel-row-" + eventID).removeClass("bg-red").addClass("bg-green").delay(1000).fadeOut(400, "swing", function(){
									$("#panel-row-" + eventID).addClass("bg-green").remove();
								});
							}
							else
							{
								$("#panel-row-" + eventID).addClass("bg-red");
								$("#form-modal .modal-body").html(msg);
								$("#form-modal").modal("show");
							}
						},
					});
				}
				else
				{
					var formData = $(".qDatas" + eventID).serialize();
					formData += "&eventID=" + eventID;
					$.ajax({
						type: "POST",
						url: "<?php echo PATH_WEB.$GLOBALS["URL"]->routes[0]; ?>/work",
						headers: {"X-CSRF-TOKEN": $("[name='_token']").val()},
						data: formData,
						dataType: "html",
						success: function(msg) {
							if(msg == "ok")
							{
								$("#panel-row-" + eventID).removeClass("bg-red").addClass("bg-green").delay(1000).fadeOut(400, "swing", function(){
									$("#panel-row-" + eventID).addClass("bg-green").remove();
								});
							}
							else
							{
								$("#panel-row-" + eventID).addClass("bg-red");
								$("#form-modal .modal-body").html(msg);
								$("#form-modal").modal("show");
							}
						},
					});
				}
			}
		}
		// $(".data-table input, .data-table textarea, .data-table select").parents("td").removeClass("panel-row-col").addClass("printing-hidden");
		$(".data-table input, .data-table textarea, .data-table select, .td-question-container").addClass("printing-hidden");
		</script>
		<?php
	}
	?>
@stop