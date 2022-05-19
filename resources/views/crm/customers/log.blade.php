<?php 
$customer = $VIEW["vars"]["customer"]; 
?>
@extends("crm")

@section("titleRight")
	@include("crm._title-right-back-btn")
@stop

@section("content")
	@include("crm.customers._details-menu")	
	<?php 
	if(isset($VIEW["vars"]["LIST"])) 
	{ 
		$panel = $VIEW["vars"]["LIST"];
		?> @include("crm._list-panel") <?php 
	} 
	else { ?><h2 class="font-bold text-center">Jelenleg még nem történt esemény!</h2><?php } 
	?>
@stop