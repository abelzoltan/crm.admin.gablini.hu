<?php $panel = $VIEW["vars"]["LIST"]; ?>
@extends("crm")

@section("titleRight")
	@include("crm._title-right-print-btn")
@stop	

@section("style")
	.service-todo-container .table-responsive{
		overflow-x: visible;
	}
	
	.service-todo-table-container{
		width: 100%;
		overflow-x: auto;
	}
	
	.service-todo-table-container .bg-red, .bg-green{
		color: #000;
	}
@stop

@section("bodyBottom")
	@include("crm.services._tracking-check")
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
	function saveEvent(eventID)
	{
		if(eventID != "" && eventID != "undefined")
		{
			$.ajax({
				type: "POST",
				url: "<?php echo PATH_WEB; ?>service-tracking/work-save",
				headers: {"X-CSRF-TOKEN": $("[name='_token']").val()},
				data: "phone=" + $(".services-todo-phone-" + eventID).val() + "&eventID=" + eventID,
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
	
	function closeEvent(eventID)
	{
		if(eventID != "" && eventID != "undefined")
		{
			$.ajax({
				type: "POST",
				url: "<?php echo PATH_WEB; ?>service-tracking/work-close",
				headers: {"X-CSRF-TOKEN": $("[name='_token']").val()},
				data: "&eventID=" + eventID,
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
	</script>
@stop