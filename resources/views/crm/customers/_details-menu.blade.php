<?php $baseURL = $GLOBALS["URL"]->link([$GLOBALS["URL"]->routes[0], $GLOBALS["URL"]->routes[1]]); ?>
<div class="content-menu-container row">
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition <?php if(!isset($GLOBALS["URL"]->routes[2]) OR empty($GLOBALS["URL"]->routes[2])) { ?>content-menu-active<?php } ?>" href="<?php echo $baseURL; ?>">
			<div class="content-menu-icon bg-footer"><span class="fa fa-id-card color-white"></span></div>
			<div class="content-menu-texts">
				<div class="content-menu-name">Adatlap</div>
				<div class="content-menu-description">Információk az ügyfélről.</div>
			</div>
			<div class="clear"></div>
		</a>
	</div>
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition <?php if($GLOBALS["URL"]->routes[2] == "log") { ?>content-menu-active<?php } ?>" href="<?php echo $baseURL; ?>/log">
			<div class="content-menu-icon bg-purple"><span class="fa fa-book color-white"></span></div>
			<div class="content-menu-texts">
				<div class="content-menu-name">Eseménynapló</div>
				<div class="content-menu-description">Események, műveletek részletezése.</div>
			</div>
			<div class="clear"></div>
		</a>
	</div>
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition <?php if($GLOBALS["URL"]->routes[2] == "edit") { ?>content-menu-active<?php } ?>" href="<?php echo $baseURL; ?>/edit">
			<div class="content-menu-icon bg-primary"><span class="fa fa-pencil color-white"></span></div>
			<div class="content-menu-texts">
				<div class="content-menu-name">Szerkesztés</div>
				<div class="content-menu-description">Alapadatok módosítása.</div>
			</div>
			<div class="clear"></div>
		</a>
	</div>
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition cursor-pointer" data-toggle="modal" data-target="#modal-panel-row-del-<?php echo $customer["id"]; ?>">
			<div class="content-menu-icon bg-danger"><span class="fa fa-times color-white"></span></div>
			<div class="content-menu-texts color-black">
				<div class="content-menu-name">Törlés</div>
				<div class="content-menu-description">Ügyfél törlése. Indoklás szükséges!</div>
			</div>
			<div class="clear"></div>
		</a>
		<div class="modal fade text-center" id="modal-panel-row-del-<?php echo $customer["id"]; ?>" tabindex="-1" role="dialog" aria-labelledby="modal-panel-row-del-<?php echo $customer["id"]; ?>" aria-hidden="true">
			<div class="modal-dialog">
				<form class="modal-content form-horizontal" action="<?php echo $baseURL; ?>/del" method="get">
					<input type="hidden" name="work" value="del">
					<input type="hidden" name="id" value="<?php echo $customer["id"]; ?>">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h4 class="modal-title" id="myModalLabel-file-list-<?php echo $customer["id"]; ?>">Törlés megerősítése</h4>
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
	</div>
</div>
<div class="height-10 visible-lg visible-md"></div>
<div class="content-menu-container row">
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition <?php if($GLOBALS["URL"]->routes[2] == "email") { ?>content-menu-active<?php } ?>" href="<?php echo $baseURL; ?>/email">
			<div class="content-menu-icon bg-green2"><span class="fa fa-envelope color-white"></span></div>
			<div class="content-menu-texts">
				<div class="content-menu-name">E-mail írása</div>
				<div class="content-menu-description">Üzenet küldése az ügyfélnek.</div>
			</div>
			<div class="clear"></div>
		</a>
	</div>	
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition <?php if($GLOBALS["URL"]->routes[2] == "contacts") { ?>content-menu-active<?php } ?>" href="<?php echo $baseURL; ?>/contacts">
			<div class="content-menu-icon bg-blue"><span class="fa fa-file-text color-white"></span></div>
			<div class="content-menu-texts">
				<div class="content-menu-name">Kapcsolatfelvételek</div>
				<div class="content-menu-description">Megkeresések, ajánlatkérések, ...</div>
			</div>
			<div class="clear"></div>
		</a>
	</div>	
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition <?php if($GLOBALS["URL"]->routes[2] == "comments") { ?>content-menu-active<?php } ?>" href="<?php echo $baseURL; ?>/comments">
			<div class="content-menu-icon bg-menu"><span class="fa fa-file-text color-white"></span></div>
			<div class="content-menu-texts">
				<div class="content-menu-name">Megjegyzések</div>
				<div class="content-menu-description">Belső és publikus kommentek.</div>
			</div>
			<div class="clear"></div>
		</a>
	</div>
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition <?php if($GLOBALS["URL"]->routes[2] == "addresses") { ?>content-menu-active<?php } ?>" href="<?php echo $baseURL; ?>/addresses">
			<div class="content-menu-icon bg-warning"><span class="fa fa-map-marker color-white"></span></div>
			<div class="content-menu-texts">
				<div class="content-menu-name">Címek</div>
				<div class="content-menu-description">Ügyfélhez rögzített címek.</div>
			</div>
			<div class="clear"></div>
		</a>
	</div>	
</div>
<div class="height-10 visible-lg visible-md"></div>
<div class="content-menu-container row">
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition <?php if($GLOBALS["URL"]->routes[2] == "cars") { ?>content-menu-active<?php } ?>" href="<?php echo $baseURL; ?>/cars">
			<div class="content-menu-icon bg-dark"><span class="fa fa-car color-white"></span></div>
			<div class="content-menu-texts">
				<div class="content-menu-name">Autók</div>
				<div class="content-menu-description">Ügyfélhez tartozó járművek kezelése.</div>
			</div>
			<div class="clear"></div>
		</a>
	</div>		
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition <?php if($GLOBALS["URL"]->routes[2] == "imported-datas") { ?>content-menu-active<?php } ?>" href="<?php echo $baseURL; ?>/imported-datas">
			<div class="content-menu-icon bg-brown"><span class="fa fa-database color-white"></span></div>
			<div class="content-menu-texts">
				<div class="content-menu-name">Importált adatok</div>
				<div class="content-menu-description">Korábbi rendszerekből.</div>
			</div>
			<div class="clear"></div>
		</a>
	</div>
	<div class="col-md-3 col-xs-12 content-menu-col">
		<a class="content-menu transition <?php if($GLOBALS["URL"]->routes[2] == "imported-datas") { ?>content-menu-active<?php } ?>" href="<?php echo $baseURL; ?>/imported-datas">
			<div class="content-menu-icon bg-gray"><span class="fa fa-wrench color-white"></span></div>
			<div class="content-menu-texts">
				<div class="content-menu-name">Szerviz események</div>
				<div class="content-menu-description">Ügyfélhez kapcsolódó szervizek.</div>
			</div>
			<div class="clear"></div>
		</a>
	</div>
</div>
<div class="height-20"></div>