<?php $panel = $VIEW["vars"]["LIST"]; ?>
@extends("crm")

@section("titleRight")
	@include("crm._title-right-print-btn")
@stop	

@section("headBottom")
	@include("crm._print-page")
@stop

@section("content")
	<form action="#" onsubmit="return false;">
		<?php echo csrf_field(); ?>
		@include("crm._list-panel") 
	</form>
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
	function serviceStatusSave(eventID)
	{
		if(eventID != "" && eventID != "undefined")
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
							$("#panel-row-" + eventID).remove();
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
	$(".data-table input, .data-table textarea, .data-table select").parents("td").removeClass("panel-row-col").addClass("printing-hidden");
	</script>
@stop