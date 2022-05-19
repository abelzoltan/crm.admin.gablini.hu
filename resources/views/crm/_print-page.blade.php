<?php
if(isset($_GET["nyomtatas"]) AND $_GET["nyomtatas"]) 
{
	?>
	<style>
	@page{
		size: landscape;
	}
	.printing-visible{
		display: none !important;
	}
	@media print{		
		thead{
			display: table-row-group !important;
		}
		
		body{
			background-color: #fff !important;
		}
		
		#nprogress, .left_col, .top_nav, .title_right, .navbar-right, .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate, .dt-buttons{
			display: none !important;
		}
		
		.right_col, .x_panel, .x_title, .x_content{
			padding: 0 !important;
			border: 0 !important;
		}
		
		.right_col, .x_content, .table.dataTable{
			margin: 0 !important;
		}
		
		.data-table thead tr th::after{
			content: "" !important;
			display: none !important;
		}
		
		.data-table thead tr, .data-table tbody tr{
			page-break-inside: avoid !important;
		}
		
		.data-table thead tr th, .data-table tbody tr td{
			padding: 4px 2px !important;
			page-break-inside: avoid !important;
		}
		
		.table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th{
			border: 1px solid #000 !important;
		}
		
		.table>tbody>tr>td.panel-row-btn, .table>tbody>tr>th.panel-row-btn, .table>tfoot>tr>td.panel-row-btn, .table>tfoot>tr>th.panel-row-btn, .table>thead>tr>td.panel-row-btn, .table>thead>tr>th.panel-row-btn, .panel-row-btn{
			display: none !important;
			border: 0 !important;
		}
		
		.table-responsive, .service-todo-container .table-responsive{
			overflow-x: visible !important;
		}
		
		.service-todo-question{
			width: 100px; !important;
			max-width: none !important;
			min-width: 0 !important;
		}
		
		.printing-hidden{
			display: none !important;
		}
		
		.printing-visible{
			display: block !important;
		}
		
		.printing-text-center{
			text-align: center !important;
		}
		
		.printing-width-30{
			width: auto !important;
			max-width: none !important;
			min-width: none !important;
		}
	}
	</style>
	<script>
	$(document).ready(function(){
		$(".data-table").DataTable().page.len(-1).draw();
		window.print();
	});
	</script>
	<?php
}
?>