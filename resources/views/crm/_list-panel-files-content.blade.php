<div class="x_panel">
	<div class="x_title">
		<div class="row">
			<div class="col-xs-12">
				<h2 class="font-bold"><?php echo $datas["panelName"]; ?></h2>
			</div>
		</div>
	</div>
	<div class="x_content">
		<form class="form-horizontal form-label-left" action="<?php echo $datas["uploadAction"]; ?>" method="post" enctype="multipart/form-data">
			<?php echo csrf_field(); ?>
			<input type="hidden" name="process" value="1">
			<input type="hidden" name="type" value="<?php echo $datas["type"]; ?>">
			<input type="hidden" name="foreignKey" value="<?php echo $datas["foreignKey"]; ?>">
			<div class="form-group">
				<label class="control-label col-md-3 col-sm-3 col-xs-12"><?php echo $datas["uploadLabel"]; ?>:</label>
				<div class="col-md-7 col-sm-7 col-xs-8">
					<div class="file-input-container">
						<div class="btn btn-primary file-button"><span class="fa fa-upload"></span></div>
						<input type="text" class="form-control file-text" name="files-text" id="<?php echo $datas["type"]; ?>-files-text">
						<div class="clear"></div>
						<input type="file" class="form-control file-input" name="files[]" multiple onchange="document.getElementById('<?php echo $datas["type"]; ?>-files-text').value = this.value;">
					</div>
				</div>
				<div class="col-md-2 col-sm-3 col-xs-4">
					<button type="submit" class="file-link btn btn-success"><i class="fa fa-floppy-o"></i> Mentés</button>
				</div>
			</div>
		</form>
		<?php if(!empty($datas["fileList"])) { ?>
			<div class="ln_solid"></div>
			<form class="form-horizontal form-label-left" action="<?php echo $datas["editAction"]; ?>" method="post" enctype="multipart/form-data">
				<?php echo csrf_field(); ?>
				<input type="hidden" name="process" value="1">
				<input type="hidden" name="type" value="<?php echo $datas["type"]; ?>">
				<input type="hidden" name="foreignKey" value="<?php echo $datas["foreignKey"]; ?>">
				<table class="table table-striped data-table table-vertical-middle" data-page-length="25">
					<thead>
						<tr>
							<th>#</th>
							<th style="width: 250px;" class="no-sort">Nevek</th>
							<th>Eredeti név</th>
							<th>Méret</th>
							<th>Feltöltve</th>
							<?php if(array_values($datas["fileList"])[0]["type"]->imageSlider) { ?>
								<th style="width: 250px;" class="no-sort">Slider adatok</th>
							<?php } ?>
							<th style="width: 40px;" class="no-sort text-center"></th>
							<th style="width: 40px;" class="no-sort text-center"></th>
							<th style="width: 40px;" class="no-sort text-center"></th>
							<th style="width: 40px;" class="no-sort text-center"></th>
							<th style="width: 40px;" class="no-sort text-center"></th>
						</tr>
					</thead>
					<tbody>
						<?php 
						$i = 1;
						foreach($datas["fileList"] AS $fileID => $data) 
						{
							?>
							<tr>
								<td><?php echo $data["file"]->orderNumber; ?>.</td>
								<td><input type="text" name="files[<?php echo $fileID; ?>][name]" class="form-control" placeholder="Név" value="<?php echo $data["file"]->name; ?>" style="width: 100%;"></td>
								<td><a href="<?php echo $data["path"]["web"]; ?>" class="fancybox text-decoration-underline" data-fancybox-group="file-list-<?php echo $datas["type"]."-".$datas["foreignKey"]; ?>" data-fancybox-title="<?php echo $data["file"]->orderNumber.". ".$data["fullName"]; ?>"><?php echo $data["file"]->nameOriginal.".".$data["extension"]->name; ?></a></td>
								<td><?php echo number_format($data["file"]->sizeKB, 0, ",", " "); ?> kB</td>
								<td><?php echo $data["file"]->date; ?></td>
								<?php if($data["type"]->imageSlider) { ?>
									<td>
										<input type="text" name="files[<?php echo $fileID; ?>][sliderLink]" class="form-control" placeholder="Link" value="<?php echo $data["file"]->sliderLink; ?>" style="width: 100%;">
										<div class="height-5"></div>
										<select name="files[<?php echo $fileID; ?>][sliderTargetBlank]" class="form-control" style="width: 100%;">
											<option value="0" <?php if(!$data["file"]->sliderTargetBlank) { ?>selected<?php } ?>>Saját lapon</option>
											<option value="1" <?php if($data["file"]->sliderTargetBlank) { ?>selected<?php } ?>>Új lapon</option>
										</select>
									</td>
								<?php } ?>
								<td class="text-center"><?php if($i < count($datas["fileList"])) { ?><a href="<?php echo $GLOBALS["URL"]->link(["file", "order", "down", $fileID], ["from" => $datas["from"]]); ?>" class="inline-block"><button type="button" class="btn btn-success"><i class="fa fa-arrow-down"></i></button></a><?php } ?></td> 
								<td class="text-center"><?php if($i > 1) { ?><a href="<?php echo $GLOBALS["URL"]->link(["file", "order", "up", $fileID], ["from" => $datas["from"]]); ?>" class="inline-block"><button type="button" class="btn btn-success"><i class="fa fa-arrow-up"></i></button></a><?php } ?></td> 
								<td class="text-center"><button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o"></i></button></td>
								<td class="text-center"><a href="<?php echo $GLOBALS["URL"]->link(["file", "download", $fileID], ["from" => $datas["from"]]); ?>" class="inline-block"><button type="button" class="btn btn-dark"><i class="fa fa-download"></i></button></a></td>
								<td class="text-center">
									<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modal-file-list-del-<?php echo $fileID; ?>"><i class="fa fa-times"></i></button>
									<div class="modal fade" id="modal-file-list-del-<?php echo $fileID; ?>" tabindex="-1" role="dialog" aria-labelledby="modal-file-list-del-<?php echo $fileID; ?>" aria-hidden="true">
										<div class="modal-dialog">
											<div class="modal-content">
												<div class="modal-header">
													<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
													<h4 class="modal-title" id="myModalLabel-file-list-<?php echo $fileID; ?>">Törlés: <?php echo $data["fullName"]; ?></h4>
												</div>
												<div class="modal-body">
													Biztosan szeretné törölni a(z) <strong><?php echo $data["fullName"]; ?></strong> nevű fájlt?
												</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-default" data-dismiss="modal">Nem</button>
													<a href="<?php echo $GLOBALS["URL"]->link(["file", "del", $fileID], ["from" => $datas["from"]]); ?>" class="inline-block"><button type="button" class="btn btn-primary">Igen</button></a>
												</div>
											</div>
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
				<div class="ln_solid"></div>
				<div class="form-group">
					<div class="col-xs-12 text-center">
						<?php if(isset($datas["backButton"])) { ?>
							<span class="hidden-xs">&nbsp;&nbsp;&nbsp;</span>
							<a class="btn btn-lg btn-danger display-block-xs" href="<?php echo $datas["backButton"]; ?>"><i class="fa fa-arrow-left"></i> &nbsp;Vissza</a>
							<div class="visible-xs height-30"></div>
						<?php } ?>	
						<span class="hidden-xs">&nbsp;&nbsp;&nbsp;</span>
						<button type="submit" class="btn btn-lg btn-success display-block-xs"><i class="fa fa-floppy-o"></i> &nbsp;Mentés</button>
						<span class="hidden-xs">&nbsp;&nbsp;&nbsp;</span>
					</div>
				</div>
			</form>
		<?php } ?>	
	</div>
</div>