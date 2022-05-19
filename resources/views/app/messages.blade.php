@extends("app")

@section("bodyClass")
	my-bg-gray
@stop

@section("content")
	<div class="my-container">
		<?php 
		$hasMessage = false;
		if(count($VIEW["VARS"]["notifications"]) > 0)
		{
			$minDate = date("Y-m-d 00:00:00", strtotime("-1 month"));
			foreach($VIEW["VARS"]["notifications"] AS $notification)
			{
				if($minDate <= $notification["data"]->sendDate)
				{
					$hasMessage = true;
					?>
					<div class="height-30"></div>
					<div class="premise-container">
						<div class="font-bold font-size-24 line-height-30 text-myblue"><?php echo $notification["data"]->title; ?></div>
						<div class="height-10"></div>
						<div class="font-bold"><?php echo $notification["sendDateOut"]; ?></div>
						<div class="height-10"></div>
						<div class="text-justify">
							<?php echo $notification["data"]->bodyFull; ?>
							<div class="clear"></div>
						</div>
					</div>
					<?php
				}
			}
		}
		
		if(!$hasMessage)
		{
			?>
			<div class="height-30"></div>
			<div class="premise-container">
				<div class="font-bold font-size-24 line-height-30 text-myblue text-center">Jelenleg nincs friss üzenet a postaládájában!</div>
			</div>
			<?php
		}
		?>
		<div class="height-20"></div>
	</div>
@stop