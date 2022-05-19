<?php $panel = $VIEW["vars"]["LIST"]; ?>
@extends("crm")

@section("titleRight")
	@include("crm._title-right-print-btn")
@stop	

@section("headBottom")
	@include("crm._print-page")
@stop

@section("style")
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
		// $(".service-todo-container .data-table").tableHeadFixer({'left' : 8});
	</script>
	<?php } ?>
	@include("crm.services._todo-check")
@stop

@section("content")
	<div class="service-todo-container">
		<form action="#" onsubmit="return false;">
			<?php echo csrf_field(); ?>
			@include("crm._list-panel") 
		</form>
	</div>
	<div class="modal fade" id="form-modal" tabindex="-1" role="dialog" aria-labelledby="form-modal" aria-hidden="true">
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
	function serviceStatusChange(eventID)
	{
		if(eventID != "" && eventID != "undefined")
		{
			if($(".services-todo-status-" + eventID).val() != "") { $(".td-question-container-" + eventID).hide(); }
			else { $(".td-question-container-" + eventID).show(); }
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
					url: "<?php echo PATH_WEB; ?>service-todo/work-status",
					headers: {"X-CSRF-TOKEN": $("[name='_token']").val()},
					data: "status=" + $(".services-todo-status-" + eventID).val() + "&comment=" + $(".services-todo-comment-" + eventID).val() + "&eventID=" + eventID,
					dataType: "html",
					success: function(msg) {
						if(msg == "ok")
						{
							$("#panel-row-" + eventID).css("cssText", "background: #1ABB9C !important;").delay(1000).fadeOut(400, "swing", function(){
								$("#panel-row-" + eventID).css("cssText", "background: transparent;").remove();
							});
						}
						else
						{
							$("#panel-row-" + eventID).css("cssText", "background: #E74C3C !important;");
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
					url: "<?php echo PATH_WEB; ?>service-todo/work",
					headers: {"X-CSRF-TOKEN": $("[name='_token']").val()},
					data: formData,
					dataType: "html",
					success: function(msg) {
						if(msg == "ok")
						{
							$("#panel-row-" + eventID).css("cssText", "background: #1ABB9C !important;").delay(1000).fadeOut(400, "swing", function(){
								$("#panel-row-" + eventID).css("cssText", "background: transparent;").remove();
							});
						}
						else
						{
							$("#panel-row-" + eventID).css("cssText", "background: #E74C3C !important;");
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
@stop