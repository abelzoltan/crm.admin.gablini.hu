<script>
var serviceTodoRowsCheckIntervalID = window.setInterval(serviceTodoRowsCheck, 30000);
function serviceTodoRowsCheck()
{
	$.ajax({
		type: "POST",
		url: "<?php echo PATH_WEB; ?>service-todo/work-rows-check",
		headers: {"X-CSRF-TOKEN": "<?php echo csrf_token(); ?>"},
		dataType: "json",
		success: function(serviceEventsArray) {
			var serviceEventTableRows = $("#service-todo-table tbody tr");
			for (i = 0; i < serviceEventTableRows.length; i++) 
			{
				var serviceEventTableRowID = $(serviceEventTableRows[i]).attr("id");
				var serviceEventID = serviceEventTableRowID.replace("panel-row-", "");
				
				if(jQuery.inArray(parseInt(serviceEventID), serviceEventsArray) === -1 && jQuery.inArray(serviceEventID, serviceEventsArray) === -1)
				{
					if(!$(serviceEventTableRows[i]).hasClass("service-todo-table-row-done"))
					{
						$(serviceEventTableRows[i]).css({
							"background-color": "#cfcf00",
							"color": "#888",
							"opacity": "0.8",
						});
						$(serviceEventTableRows[i]).find("input, select, textarea").prop("disabled", true);
						$(serviceEventTableRows[i]).children("td:first").html("<strong style='color: #000;'>ELKÉSZÜLT</strong><br>" + $(serviceEventTableRows[i]).children("td:first").html());
						$(serviceEventTableRows[i]).addClass("service-todo-table-row-done");
						// serviceTodoRowsRemove(serviceEventTableRows[i]);
					}
				}
			}			
		},
	});
}

function serviceTodoRowsRemove(element)
{
	setTimeout(function() {
		$(element).fadeOut(400, "swing", function(){
			$(element).remove();
		});
	}, 5000);
}
</script>