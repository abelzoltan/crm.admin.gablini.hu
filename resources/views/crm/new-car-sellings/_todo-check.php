<script>
var newcarSellingTodoRowsCheckIntervalID = window.setInterval(newcarSellingTodoRowsCheck, 30000);
function newcarSellingTodoRowsCheck()
{
	$.ajax({
		type: "POST",
		url: "<?php echo PATH_WEB; ?>new-car-sellings-todo/work-rows-check",
		headers: {"X-CSRF-TOKEN": "<?php echo csrf_token(); ?>"},
		dataType: "json",
		success: function(eventsArray) {
			var eventTableRows = $("#newcar-selling-todo-table tbody tr");
			for (i = 0; i < eventTableRows.length; i++) 
			{
				var eventTableRowID = $(eventTableRows[i]).attr("id");
				var eventID = eventTableRowID.replace("panel-row-", "");
				
				if(jQuery.inArray(parseInt(eventID), eventsArray) === -1 && jQuery.inArray(eventID, eventsArray) === -1)
				{
					if(!$(eventTableRows[i]).hasClass("newcar-selling-todo-table-row-done"))
					{
						$(eventTableRows[i]).css({
							"background-color": "#cfcf00",
							"color": "#888",
							"opacity": "0.8",
						});
						$(eventTableRows[i]).find("input, select, textarea").prop("disabled", true);
						$(eventTableRows[i]).children("td:first").html("<strong style='color: #000;'>ELKÉSZÜLT</strong><br>" + $(eventTableRows[i]).children("td:first").html());
						$(eventTableRows[i]).addClass("newcar-selling-todo-table-row-done");
						// newcarSellingTodoRowsRemove(eventTableRows[i]);
					}
				}
			}			
		},
	});
}

function newcarSellingTodoRowsRemove(element)
{
	setTimeout(function() {
		$(element).fadeOut(400, "swing", function(){
			$(element).remove();
		});
	}, 5000);
}
</script>