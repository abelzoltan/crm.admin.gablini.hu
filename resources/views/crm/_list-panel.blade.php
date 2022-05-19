<div class="x_panel">
	<div class="x_title">
		<div class="row">
			<div class="col-xs-12">
				<h2 class="font-bold"><?php echo $panel["panelName"]; ?></h2>
				<ul class="nav navbar-right panel_toolbox">
					<li><a class="close-link"><i class="fa fa-close"></i></a></li>
					<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="x_content">
		<div class="table-responsive">
			<?php if(isset($panel["table"])) { ?>
			<table class="table table-striped data-table table-vertical-middle" data-page-length="<?php if(isset($panel["dataPageLength"])) { echo $panel["dataPageLength"]; } else { ?>25<?php } ?>" <?php if(isset($panel["table"]["id"])) { ?>id="<?php echo $panel["table"]["id"]; ?>"<?php } ?>>
				<thead>
					<tr>
						<?php 
						foreach($panel["table"]["header"] AS $item) 
						{ 
							?><th style="<?php echo $item["style"]; ?>" class="panel-row-col <?php echo $item["class"]; ?>"><?php echo $item["name"]; ?></th><?php 
						} 
						
						if(isset($panel["table"]["buttons"]))
						{
							for($i = 1; $i <= count($panel["table"]["buttons"]); $i++) { ?><th style="width: 40px;" class="panel-row-btn no-sort text-center"></th><?php }
						}
						?>
					</tr>
				</thead>
				<tbody>	
					<?php 
					$i = 1;
					foreach($panel["table"]["rows"] AS $rowID => $row) 
					{ 
						?>
						<tr id="panel-row-<?php echo $rowID; ?>" class="<?php echo $row["class"]; ?>">
							<?php
							foreach($row["columns"] AS $item) 
							{
								?><td style="<?php echo $item["style"]; ?>" class="panel-row-col <?php echo $item["class"]; ?>"><?php echo $item["name"]; ?></td><?php 
							}
						if(isset($panel["table"]["buttons"]))
						{
							foreach($panel["table"]["buttons"] AS $buttonName) 
							{ 
								if(isset($row["buttons"][$buttonName])) { $btnDatas = $row["buttons"][$buttonName]; }
								else { $btnDatas = []; }
								if(isset($row["buttons"][$buttonName]) AND isset($row["buttons"][$buttonName]["href"])) { $btnDatas["href"] = $row["buttons"][$buttonName]["href"]; }
								if(isset($row["buttons"][$buttonName]) AND isset($row["buttons"][$buttonName]["title"])) { $btnDatas["title"] = $row["buttons"][$buttonName]["title"]; }
								if(isset($row["buttons"][$buttonName]) AND isset($row["buttons"][$buttonName]["onclick"])) { $btnDatas["onclick"] = $row["buttons"][$buttonName]["onclick"]; }
								if(isset($row["buttons"][$buttonName]) AND isset($row["buttons"][$buttonName]["class"])) { $btnDatas["class"] = $row["buttons"][$buttonName]["class"]; }
								if(isset($row["buttons"][$buttonName]) AND isset($row["buttons"][$buttonName]["icon"])) { $btnDatas["icon"] = $row["buttons"][$buttonName]["icon"]; }
								if(isset($row["buttons"][$buttonName]) AND isset($row["buttons"][$buttonName]["target"])) { $btnDatas["target"] = $row["buttons"][$buttonName]["target"]; }
								
								switch($buttonName)
								{
									case "order-down":
										if(!isset($btnDatas["href"])) { $btnDatas["href"] = $GLOBALS["URL"]->currentURL."/order/down/".$rowID; }
										if(!isset($btnDatas["title"])) { $btnDatas["title"] = "Rendezés: Sorszám növelése 1-el"; }
										if(!isset($btnDatas["class"])) { $btnDatas["class"] = "success"; }
										if(!isset($btnDatas["icon"])) { $btnDatas["icon"] = "arrow-down"; }
										
										?><td class="panel-row-btn text-center"><?php if($i < count($panel["table"]["rows"])) { ?><a href="<?php echo $btnDatas["href"]; ?>" class="inline-block" title="<?php echo $btnDatas["title"]; ?>"><button type="button" class="btn btn-<?php echo $btnDatas["class"]; ?>"><i class="fa fa-<?php echo $btnDatas["icon"]; ?>"></i></button></a><?php } ?></td><?php
										break; 
									case "order-up":
										if(!isset($btnDatas["href"])) { $btnDatas["href"] = $GLOBALS["URL"]->currentURL."/order/up/".$rowID; }
										if(!isset($btnDatas["title"])) { $btnDatas["title"] = "Rendezés: Sorszám csökkentése 1-el"; }
										if(!isset($btnDatas["class"])) { $btnDatas["class"] = "success"; }
										if(!isset($btnDatas["icon"])) { $btnDatas["icon"] = "arrow-up"; }
										
										?><td class="panel-row-btn text-center"><?php if($i > 1) { ?><a href="<?php echo $btnDatas["href"]; ?>" class="inline-block" title="<?php echo $btnDatas["title"]; ?>"><button type="button" class="btn btn-<?php echo $btnDatas["class"]; ?>"><i class="fa fa-<?php echo $btnDatas["icon"]; ?>"></i></button></a><?php } ?></td> <?php
										break;
									case "edit":
										if(!isset($btnDatas["href"])) { $btnDatas["href"] = $GLOBALS["URL"]->currentURL."/edit/".$rowID; }
										if(!isset($btnDatas["title"])) { $btnDatas["title"] = "Szerkesztés"; }
										if(!isset($btnDatas["class"])) { $btnDatas["class"] = "primary"; }
										if(!isset($btnDatas["icon"])) { $btnDatas["icon"] = "pencil"; }
										
										?><td class="panel-row-btn text-center"><a href="<?php echo $btnDatas["href"]; ?>" class="inline-block" title="<?php echo $btnDatas["title"]; ?>"><button type="button" class="btn btn-<?php echo $btnDatas["class"]; ?>"><i class="fa fa-<?php echo $btnDatas["icon"]; ?>"></i></button></a></td><?php
										break;
									case "del":
										if(!isset($btnDatas["href"])) { $btnDatas["href"] = $GLOBALS["URL"]->currentURL."/del/".$rowID; }
										if(!isset($btnDatas["title"])) { $btnDatas["title"] = "Törlés"; }
										if(!isset($btnDatas["class"])) { $btnDatas["class"] = "danger"; }
										if(!isset($btnDatas["icon"])) { $btnDatas["icon"] = "times"; }
										?>
										<td class="panel-row-btn text-center">
											<button type="button" class="btn btn-<?php echo $btnDatas["class"]; ?>" data-toggle="modal" data-target="#modal-panel-row-del-<?php echo $rowID; ?>" title="<?php echo $btnDatas["title"]; ?>"><i class="fa fa-<?php echo $btnDatas["icon"]; ?>"></i></button>
											<div class="modal fade" id="modal-panel-row-del-<?php echo $rowID; ?>" tabindex="-1" role="dialog" aria-labelledby="modal-panel-row-del-<?php echo $rowID; ?>" aria-hidden="true">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-header">
															<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
															<h4 class="modal-title" id="myModalLabel-file-list-<?php echo $rowID; ?>">Törlés megerősítése</h4>
														</div>
														<div class="modal-body">
															Biztosan szeretné törölni az elemet?
														</div>
														<div class="modal-footer">
															<button type="button" class="btn btn-default" data-dismiss="modal">Nem</button>
															<a href="<?php echo $btnDatas["href"]; ?>" class="inline-block"><button type="button" class="btn btn-primary">Igen</button></a>
														</div>
													</div>
												</div>
											</div>
										</td>
										<?php 
										break;
									case "active":
									case "visible":
										if($row["data"]->$buttonName) { ?><td class="panel-row-btn text-center"><a href="<?php echo $GLOBALS["URL"]->currentURL; ?>/deactivate/<?php echo $rowID; ?>" class="inline-block" title="Jelenleg aktív => deaktiválás"><button type="button" class="btn btn-info"><i class="fa fa-eye"></i></button></a></td><?php }
										else { ?><td class="panel-row-btn text-center"><a href="<?php echo $GLOBALS["URL"]->currentURL; ?>/activate/<?php echo $rowID; ?>" class="inline-block" title="Jelenleg inaktív => aktiválás"><button type="button" class="btn btn-warning"><i class="fa fa-eye-slash"></i></button></a></td><?php }
										break;	
									default:
										?><td class="panel-row-btn text-center"><a href="<?php echo $btnDatas["href"]; ?>" class="inline-block" title="<?php echo $btnDatas["title"]; ?>"<?php if(isset($btnDatas["onclick"]) AND !empty($btnDatas["onclick"])) { ?> onclick="<?php echo $btnDatas["onclick"]; ?>"<?php } if(isset($btnDatas["target"]) AND !empty($btnDatas["target"])) { ?> target="<?php echo $btnDatas["target"]; ?>"<?php } ?>><button type="button" class="btn btn-<?php echo $btnDatas["class"]; ?>"><i class="fa fa-<?php echo $btnDatas["icon"]; ?>"></i></button></a></td><?php
										break;
								}
							}
						}						
							?>
						</tr>
						<?php
						$i++;
					} 
					?>	
				</tbody>
			</table>
			<?php } ?>
		</div>
	</div>
</div>