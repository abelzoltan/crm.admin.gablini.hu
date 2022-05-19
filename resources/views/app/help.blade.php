@extends("app")

@section("bodyClass")
	my-bg-gray
@stop

@section("content")
	<div class="my-container">
		<div class="height-30"></div>
		<div class="text-myblue text-center text-uppercase">Segítség esetén segélyvonalunk</div>
		<div class="height-10"></div>
		<div class="text-myblue text-center text-uppercase"><strong>24 órás</strong> segítségnyújtást biztosít.</div>
		<div class="height-20"></div>
		<div class="row">
			<div class="col-xs-10 col-xs-offset-1">
				<div class="text-center"><a href="tel:+36304446262" class="my-btn my-btn-small transition">+36 30 444 62 62</a></div>
				<div class="height-20"></div>
				<div class="text-center"><a href="tel:+36304446262" class="my-btn my-btn-small transition">HÍVÁS</a></div>
			</div>
		</div>
	</div>
@stop