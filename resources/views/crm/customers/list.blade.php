@extends("crm")

@section("titleRight")
	@include("crm._title-right-new-btn")
@stop

@section("content")
	<!-- Ügyfél adatlapra ugrás -->
	@include("crm.customers._form-to-details")
	
	<!-- Ügyfelek keresése -->
	@include("crm.customers._form-search")
	
	<!-- Ügyfél lista -->
	<?php 
	if(isset($VIEW["vars"]["search"]["search"]) AND $VIEW["vars"]["search"]["search"]) 
	{ 
		$customerRows = $VIEW["vars"]["customerRows"];
		$customerRowCount = count($customerRows);
		$customerRowCountFormatted = number_format($customerRowCount, 0, ",", " ");
		
		$getData = $GLOBALS["URL"]->get;
		if(isset($getData["error"])) { unset($getData["error"]); }
		$getString = http_build_query($getData);
		?>
		<div class="x_panel">
			<div class="x_title">
				<div class="row">
					<div class="col-xs-12">
						<h2 class="font-bold">Ügyfelek listája <span class="color-blue">[<?php echo $customerRowCountFormatted; ?> találat]</span></h2>
						<ul class="nav navbar-right panel_toolbox">
							<li><a class="close-link"><i class="fa fa-close"></i></a></li>
							<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="x_content">
				<?php 
				if(count($customerRows) == 0) 
				{
					?>
					<div class="height-10"></div>
					<h2 class="color-danger font-bold text-center text-uppercase">A megadott keresési paraméterekkel nem található ügyfél az adatbázisban!</h2>
					<div class="height-10"></div>
					<?php
				}
				else
				{
					?>
					<table class="table table-striped data-table table-vertical-middle" data-page-length="25">
						<thead>
							<tr>
								<th style="width: 5%;" class="panel-row-col">#</th>
								<th class="panel-row-col">Rögzítő munkatárs</th>
								<th class="panel-row-col">Ügyfélszám</th>
								<th class="panel-row-col">Név</th>
								<th class="panel-row-col">E-mail cím</th>
								<th class="panel-row-col">Cégnév</th>
								<th class="panel-row-col">Telefonszám</th>
								<th style="width: 40px;" class="panel-row-btn no-sort text-center"></th>
								<th style="width: 40px;" class="panel-row-btn no-sort text-center"></th>
							</tr>
						</thead>
						<tbody>	
							<?php 
							$i = 1;
							foreach($customerRows AS $rowID => $row) 
							{ 
								?>
								<tr id="panel-row-<?php echo $rowID; ?>">
									<td class="panel-row-col"><?php echo $i; ?>.</td>
									<td class="panel-row-col"><?php echo $row["userName"]; ?></td>
									<td class="panel-row-col"><?php echo $row["code"]; ?></td>
									<td class="panel-row-col font-bold"><?php echo $row["name"]; ?></td>
									<td class="panel-row-col"><?php echo $row["email"]; ?></td>
									<td class="panel-row-col"><?php echo $row["companyName"]; ?></td>
									<td class="panel-row-col"><?php echo $row["phone"]; ?></td>
									<td class="panel-row-btn text-center"><a href="<?php echo PATH_WEB; ?>customer/<?php echo $row["id"]; ?>" class="inline-block" title="Ügyfél adatlap"><button type="button" class="btn btn-primary"><i class="fa fa-user"></i></button></a></td>
									<td class="panel-row-btn text-center">
										<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-panel-row-del-<?php echo $row["id"]; ?>" title="Ügyfél törlése"><i class="fa fa-times"></i></button>
										<div class="modal fade" id="modal-panel-row-del-<?php echo $row["id"]; ?>" tabindex="-1" role="dialog" aria-labelledby="modal-panel-row-del-<?php echo $row["id"]; ?>" aria-hidden="true">
											<div class="modal-dialog">
												<form class="modal-content form-horizontal" action="<?php echo PATH_WEB; ?>customer/<?php echo $row["id"]; ?>/del" method="get">
													<input type="hidden" name="work" value="del">
													<input type="hidden" name="id" value="<?php echo $row["id"]; ?>">
													<input type="hidden" name="getData" value="<?php echo $getString; ?>">
													<div class="modal-header">
														<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
														<h4 class="modal-title" id="myModalLabel-file-list-<?php echo $row["id"]; ?>">Törlés megerősítése</h4>
													</div>
													<div class="modal-body">
														<div>Ha biztosan szeretné törölni az ügyfelet, kérem indokolja:</div>
														<div class="height-10"></div>
														<div><input class="form-control display-block width-80 center" type="text" name="reason" placeholder="Indoklás" required></div>
													</div>
													<div class="modal-footer">
														<button type="button" class="btn btn-default" data-dismiss="modal">Nem</button>
														<button type="submit" class="btn btn-primary">Igen</button>
													</div>
												</form>
											</div>
										</div>
									</td>
								</tr>
								<?php
								$i++;
							} 
							?>	
						</tbody>
					</table>
					<?php
				}
				?>
			</div>
		</div>
		<?php 
	} 
	?>
	
	<!-- Statisztikák: Ügyfélszámok, Ügyfelek honnan jöttek -->
	@include("crm.stats.customer-numbers")
	@include("crm.stats.customer-from-where")
@stop