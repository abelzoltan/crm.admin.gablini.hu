@extends("crm")

@section("content")
	<!-- Ügyfél adatlapra ugrás -->
	@include("crm.customers._form-to-details")
	
	<!-- Ügyfelek keresése -->
	@include("crm.customers._form-search")
	
	<!-- Statisztikák: Ügyfélszámok, Weboldal ajánlatkérések, Ügyfelek honnan jöttek -->
	@include("crm.stats.customer-numbers")
	@include("crm.stats.contacts")
	@include("crm.stats.customer-from-where")
@stop