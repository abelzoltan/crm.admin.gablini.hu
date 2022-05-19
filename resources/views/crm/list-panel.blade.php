<?php $panel = $VIEW["vars"]["LIST"]; ?>
@extends("crm")

@section("titleRight")
	@include("crm._title-right-new-btn")
@stop

@section("content")
	@include("crm._list-panel")
@stop