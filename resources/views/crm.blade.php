<?php
error_reporting(E_ALL ^ E_NOTICE);
$gentelellaDir = GENTELELLA_DIR;
if(isset($VIEW["vars"]) AND !empty($VIEW["vars"])) { foreach($VIEW["vars"] AS $key => $val) { $$key = $val; } }
?>
<!DOCTYPE html>
<html lang="hu-HU">
<head>
	<meta charset="utf-8">
	<title><?php echo strip_tags($GLOBALS["site"]->data->titlePrefix.$VIEW["title"].$GLOBALS["site"]->data->titleSuffix); ?></title>
	
	@yield("headTop")
	
	<link rel="icon" href="<?php echo PATH_WEB; ?>pics/admin-favicon.png" type="image/png">
	<link rel="shortcut icon" href="<?php echo PATH_WEB; ?>pics/admin-favicon.png" type="image/png">
	<link rel="bookmark icon" href="<?php echo PATH_WEB; ?>pics/admin-favicon.png" type="image/png">
	
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	
	<?php 
	if(View::hasSection("headMeta")) { ?> @yield("headMeta") <?php }
	else { ?> @include("_inc-head-meta") <?php }
	?>

	<link href="<?php echo $gentelellaDir; ?>vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	<link href="<?php echo $gentelellaDir; ?>vendors/nprogress/nprogress.css" rel="stylesheet">
	<link href="<?php echo $gentelellaDir; ?>vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet"/>
	<link href="<?php echo $gentelellaDir; ?>build/css/custom.min.css" rel="stylesheet">
	
	<!-- DataTables -->
    <link href="<?php echo $gentelellaDir; ?>/vendors/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $gentelellaDir; ?>/vendors/datatables.net-buttons-bs/css/buttons.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $gentelellaDir; ?>/vendors/datatables.net-fixedheader-bs/css/fixedHeader.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $gentelellaDir; ?>/vendors/datatables.net-responsive-bs/css/responsive.bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $gentelellaDir; ?>/vendors/datatables.net-scroller-bs/css/scroller.bootstrap.min.css" rel="stylesheet">
	
	<!-- Fancybox -->
	<link rel="stylesheet" href="<?php echo PATH_WEB; ?>vendors/fancybox-2.1.5/jquery.fancybox.css">
	
	<!-- Own Style -->
	<link href="<?php echo PATH_ROOT_WEB; ?>css/classes.min.css" rel="stylesheet">
	<link href="<?php echo $gentelellaDir; ?>build/css/style.css?v=20190703" rel="stylesheet">
	
	@yield("headMiddle")
	
	<!-- jQuery -->
	<script src="<?php echo $gentelellaDir; ?>vendors/jquery/dist/jquery.min.js"></script>
	
	<style> @yield("style") </style>
	
	@yield("headBottom")
</head>
<body class="nav-<?php if(isset($VIEW["vars"]["navSM"]) AND $VIEW["vars"]["navSM"]){ ?>sm<?php } else { ?>md<?php } ?>" <?php if(!$_SESSION[USER_LOGGED_IN]) { ?>id="body-login"<?php } ?>>
	@yield("bodyTop")
	<?php
	if($_SESSION[USER_LOGGED_IN])
	{
		?>
		<div class="container body">
			<div class="main_container">
				<div class="col-md-3 left_col menu_fixed">
					<div class="left_col scroll-view">
						<div class="navbar nav_title" style="border: 0;">
							<a href="<?php echo PATH_WEB; ?>" class="site_title"><i class="fa fa-home"></i> <span>Gablini CRM</span></a>
						</div>
						<div class="clearfix"></div>
						<div class="profile clearfix">
							<a href="<?php echo PATH_WEB; ?>" class="profile_pic display-block"><img src="<?php echo $GLOBALS["user"]["profilePic"]; ?>" alt="" class="img-circle profile_img"></a>
							<div class="profile_info">
								<span>??dv??zl??nk,</span>
								<h2><a href="<?php echo PATH_WEB; ?>profile" class="font-bold"><?php echo $GLOBALS["user"]["name"]; ?></a></h2>
							</div>
						</div>
						<div class="height-30"></div>
						<div class="clearfix"></div>
						<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
							<?php 
							foreach($navigation AS $navKey => $navData)
							{
								if(in_array($GLOBALS["user"]["data"]->rank, $navData["ranks"]) AND count($navData["menu"]) > 0 AND $navKey != "hidden")
								{
									?>
									<div class="menu_section">
										<h3><?php echo $navData["name"]; ?></h3>
										<ul class="nav side-menu">
											<?php 
											foreach($navData["menu"] AS $menuKey => $menu)
											{
												if(count($menu["ranks"]) == 0 OR in_array($GLOBALS["user"]["data"]->rank, $menu["ranks"]))
												{
													$liClass = "side-menu-li-item-".$menuKey;
													if($activeMenu["level"] == 1 AND $activeMenu["menuKey"] == $menuKey) { $liClass .= " active current-page"; }
													elseif($activeMenu["level"] == 2 AND $activeMenu["menuKey"] == $menuKey) { $liClass .= " active current-page"; }
													?>
													<li class="<?php echo $liClass; ?>">
														<a <?php if(!empty($menu["url"])) { ?>href="<?php echo $menu["url"]; ?>"<?php if($menu["targetBlank"]) { ?> target="_blank"<?php } } ?>><?php if(!empty($menu["icon"]) AND strpos($menu["icon"], ".png") !== false) { ?><img src="<?php echo PATH_WEB; ?>pics/logok/<?php echo $menu["icon"]; ?>" alt=""> <?php } elseif(!empty($menu["icon"])) { ?><i class="<?php echo $menu["icon"]; ?>"></i> <?php } echo $menu["name"]; if(count((array)$menu["menu"]) > 0) { ?><span class="fa fa-chevron-down"></span><?php } ?></a>
														<?php 
														if(count((array)$menu["menu"]) > 0)
														{
															?>
															<ul class="nav child_menu <?php if($liClass == "active" OR $liClass == "current-page" OR $liClass == "active current-page" OR $liClass == "current-page active") { ?>display-block<?php } ?>">
																<?php
																foreach($menu["menu"] AS $subMenuKey => $subMenu)
																{
																	$liClass = "side-menu-li-item-".$menuKey;
																	if($activeMenu["level"] == 2 AND $activeMenu["menuKey"] == $menuKey AND $activeMenu["subMenuKey"] == $subMenuKey) { $liClass .= " active current-page"; }
																	?><li class="<?php echo $liClass; ?>"><a <?php if(!empty($subMenu["url"])) { ?>href="<?php echo $subMenu["url"]; ?>"<?php if($subMenu["targetBlank"]) { ?> target="_blank"<?php } } ?>><?php if(!empty($subMenu["icon"])) { ?><i class="<?php echo $subMenu["icon"]; ?>"></i> <?php } echo $subMenu["name"]; ?></a></li><?php 
																}
																?>
															</ul>
															<?php
														}
														?>
													</li>
													<?php
												}
											}
											?>
										</ul>
									</div>
									<?php
								}
							}
							?>
							<div class="clear"></div>
							<div class="height-20"></div>
						</div>
						<div class="sidebar-footer hidden-small">
							<a data-toggle="tooltip" data-placement="top" title="??gyfelek" href="<?php echo PATH_WEB; ?>">
								<span class="glyphicon glyphicon-home" aria-hidden="true"></span>
							</a>
							<a data-toggle="tooltip" data-placement="top" title="Profil" href="<?php echo PATH_WEB; ?>profile">
								<span class="glyphicon glyphicon-user" aria-hidden="true"></span>
							</a>
							<a data-toggle="tooltip" data-placement="top" title="" class="sidebar-footer-no-icon">
								<span class="glyphicon" aria-hidden="true"></span>
							</a>
							<a data-toggle="tooltip" data-placement="top" title="Kijelentkez??s" href="<?php echo PATH_WEB; ?>logout">
								<span class="glyphicon glyphicon-off" aria-hidden="true"></span>
							</a>
						</div>
					</div>
				</div>
				<div class="top_nav">
					<div class="nav_menu">
						<nav>
							<div class="nav toggle"><a id="menu_toggle"><i class="fa fa-bars"></i></a></div>
							<?php if((count($activeMenu["menuData"]["ranks"]) == 0 AND in_array($GLOBALS["user"]["data"]->rank, $activeMenu["navData"]["ranks"])) OR in_array($GLOBALS["user"]["data"]->rank, $activeMenu["menuData"]["ranks"])) { ?>
								<div class="navbar-title">
									<span class="hidden-xs"><?php echo $activeMenu["menuData"]["name"]; ?></span>
									<?php if($activeMenu["level"] == 2) { ?><span class="hidden-xs"> &raquo; </span><?php echo $activeMenu["subMenuData"]["name"]; } ?>
								</div>
							<?php } ?>
							<ul class="nav navbar-nav navbar-right">
								<li>
									<a class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
										<img src="<?php echo $GLOBALS["user"]["profilePic"]; ?>" alt="">
										<span class="hidden-xs"><?php echo $GLOBALS["user"]["name"]; ?></span>
										<span class="fa fa-angle-down"></span>
									</a>
									<ul class="dropdown-menu dropdown-usermenu pull-right">
										<li><a href="<?php echo PATH_WEB; ?>profile"><i class="fa fa-user"></i>&nbsp;&nbsp; <span>Profil</span></a></li>
										<li><a href="<?php echo PATH_WEB; ?>logout"><i class="fa fa-power-off"></i>&nbsp;&nbsp; <span>Kijelentkez??s</span></a></li>
									</ul>
								</li>
							</ul>
						</nav>
					</div>
				</div>
				<div class="right_col" role="main">
					<div>
						<?php if((count($activeMenu["menuData"]["ranks"]) == 0 AND in_array($GLOBALS["user"]["data"]->rank, $activeMenu["navData"]["ranks"])) OR in_array($GLOBALS["user"]["data"]->rank, $activeMenu["menuData"]["ranks"])) { ?>
							<div class="page-title">
								<div class="title_left">
									<h3 class="font-bold text-uppercase"><?php echo $VIEW["title"]; ?></h3>
								</div>
								<div class="title_right">@yield("titleRight")</div>
							</div>
							<div class="clearfix"></div>
							<?php
							if(isset($_GET["success"]) AND !empty($_GET["success"]))
							{
								switch($_GET["success"])
								{
									case "visibility":
										$successMsgHere = "A <strong>l??that??s??g</strong> be??ll??tva!";
										break;
									case "order":
										$successMsgHere = "A <strong>rendez??s</strong> sikeresen megt??rt??nt!";
										break;
									case "edit":
										$successMsgHere = "A <strong>szerkeszt??s</strong> sikeresen megt??rt??nt!";
										break;
									case "new":
										$successMsgHere = "A <strong>l??trehoz??s</strong> sikeresen megt??rt??nt!";
										break;
									case "del":
										$successMsgHere = "A <strong>t??rl??s</strong> sikeresen megt??rt??nt!";
										break;
									case "file-upload":
										$successMsgHere = "A <strong>f??jlok felt??lt??se</strong> sikeresen megt??rt??nt!";
										break;
									case "file-order":
										$successMsgHere = "A <strong>f??jlok rendez??se</strong> sikeresen megt??rt??nt!";
										break;
									case "file-edit":
										$successMsgHere = "A <strong>f??jlok szerkeszt??se</strong> sikeresen megt??rt??nt!";
										break;	
									case "file-del":
										$successMsgHere = "A <strong>f??jl t??rl??se</strong> sikeresen megt??rt??nt!";
										break;	
									case "activate":
										$successMsgHere = "Az <strong>aktiv??l??s</strong> sikeresen megt??rt??nt!";
										break;	
									case "deactivate":
										$successMsgHere = "A <strong>deaktiv??l??s</strong> sikeresen megt??rt??nt!";
										break;	
									case "recover":
										$successMsgHere = "A <strong>vissza??ll??t??s</strong> sikeresen megt??rt??nt!";
										break;
									case "service-import":
									case "newcar-selling-import":
										$successMsgHere = "Az <strong>import??l??s</strong> sikeresen megt??rt??nt!";
										break;
									default:
										$successMsgHere = "A m??velet sikeresen elv??gezve: <strong>{$_GET["success"]}</strong>! ";
										break;
								}
								?>
								<div class="row">
									<div class="col-xs-12">
										<div class="height-5"></div>
										<h2 class="color-success font-bold"><?php echo $successMsgHere; ?></h2>
										<div class="height-5"></div>
									</div>
								</div>
								<?php
							}
							if(isset($_GET["error"]) AND !empty($_GET["error"]))
							{
								switch($_GET["error"])
								{
									case "edit-required":
										$errorMsgHere = "Sikertelen <strong>szerkeszt??s</strong>: a csillaggal jel??lt adatok kit??lt??se k??telez??!";
										break;
									case "edit-email":
										$errorMsgHere = "Sikertelen <strong>szerkeszt??s</strong>: a megadott e-mail c??m m??r foglalt!";
										break;	
									case "edit-password":
										$errorMsgHere = "Sikertelen <strong>szerkeszt??s</strong>: a megadott jelszavak nem egyeznek!";
										break;		
									case "del-no-id":
										$errorMsgHere = "Sikertelen <strong>t??rl??s</strong>: nem ??rkezett azonos??t??!";
										break;
									case "del-no-row":
										$errorMsgHere = "Sikertelen <strong>t??rl??s</strong>: a megadott azonos??t??val nem l??tezik elem!";
										break;	
									case "del-reason":
										$errorMsgHere = "Sikertelen <strong>t??rl??s</strong>: az indokl??s kit??lt??se k??telez??!";
										break;	
									case "order":
										$errorMsgHere = "A <strong>rendez??s</strong> sikertelen!";
										break;
									case "order-first-element-up":
										$errorMsgHere = "<strong>Sikertelen rendez??s</strong>: az els?? elem nem mozgathat?? fentebb!";
										break;
									case "order-last-element-down":
										$errorMsgHere = "<strong>Sikertelen rendez??s</strong>: az utols?? elem nem mozgathat?? lentebb!";
										break;	
									case "order-unknown":
										$errorMsgHere = "Sikertelen rendez??s</strong>: v??ratlan hiba t??rt??nt!";
										break;
									case "file-download":
										$errorMsgHere = "A f??jl nem el??rhet??!";
										break;	
									case "double-item":
										$errorMsgHere = "A megadott adatokkal m??r l??tezik rekord az adatb??zisban!";
										break;
									case "redirects-double-url":
										$errorMsgHere = "A megadott URL-hez m??r van hozz??rendelve akt??v ??tir??ny??t??s!";
										break;	
									case "redirects-same-url":
										$errorMsgHere = "A ??tir??ny??tand?? URL ??rt??ke nem lehet azonos a c??l URL ??rt??k??vel!";
										break;
									case "users-rank":
										$errorMsgHere = "Nincs jogosults??ga a felhaszn??l?? adatainak m??dos??t??s??hoz vagy t??rl??s??hez!";
										break;		
									case "customer-not-exists":
										$errorMsgHere = "Az ??gyf??l nem l??tezik!";
										break;
									case "service-import-no-file":
									case "newcar-selling-import-no-file":
										$errorMsgHere = "Nem t??lt??tt fel f??jlt!";
										break;
									case "service-import-wrong-extension":
									case "newcar-selling-import-wrong-extension":
										$errorMsgHere = "Csak CSV form??tum?? f??jl felt??lt??se enged??lyezett!";
										break;
									case "service-import-get-content":
									case "newcar-selling-import-get-content":
										$errorMsgHere = "A f??jl tartalma nem olvashat?? be!";
										break;	
									case "service-import-empty-lines":
									case "newcar-selling-import-empty-lines":
										$errorMsgHere = "A f??jl nem tartalmaz sorokat!";
										break;	
									case "service-import-no-rows":
									case "newcar-selling-import-no-rows":
										$errorMsgHere = "A f??jl nem tartalmaz feldolgozhat?? sorokat!";
										break;	
									case "unknown":
										$errorMsgHere = "V??ratlan hiba t??rt??nt!";
										break;	
									default:
										$errorMsgHere = "Ismeretlen hiba!";
										break;
								}
								?>
								<div class="row">
									<div class="col-xs-12">
										<div class="height-5"></div>
										<h2 class="color-danger font-bold"><?php echo $errorMsgHere; ?></h2>
										<div class="height-5"></div>
									</div>
								</div>
								<?php
							}
							?>
							<div class="height-10"></div>
							<div class="row">
								<div class="col-xs-12">
									<div id="main-inner">
										@yield("content")
									</div>
								</div>
							</div>
						<?php } elseif($activeMenu["level"] > 0) { ?>
							<div class="page-title"></div>
							<div class="height-40"></div>
							<h3 class="font-bold text-center text-uppercase text-danger">Az oldal megtekint??s??hez nincs megfelel?? jogosults??ga!</h3>
						<?php } else { ?>
							<div class="page-title"></div>
							<div class="height-40"></div>
							<h3 class="font-bold text-center text-uppercase text-danger">A keresett oldal nem tal??lhat??!</h3>
						<?php } ?>
					</div>
				</div>
				
			</div>
		</div>
		<?php
	}
	else
	{
		?>
		<div id="wrapper">
			@yield("content")
		</div>
		<?php
	}
	?>
	
	<script src="<?php echo $gentelellaDir; ?>vendors/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/fastclick/lib/fastclick.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/nprogress/nprogress.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js"></script>
	<!-- DataTables -->
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net/js/jquery.dataTables.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-buttons/js/buttons.flash.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-buttons/js/buttons.html5.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-buttons/js/buttons.print.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-fixedheader/js/dataTables.fixedHeader.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-responsive-bs/js/responsive.bootstrap.js"></script>
	<script src="<?php echo $gentelellaDir; ?>vendors/datatables.net-scroller/js/dataTables.scroller.min.js"></script>
	<script>
	$(".data-table").DataTable({
		dom: "lfrtipB",
		responsive: false,
		<?php if(isset($VIEW["vars"]["LIST"]) AND isset($VIEW["vars"]["LIST"]["order"]) AND !empty($VIEW["vars"]["LIST"]["order"])) { ?>order: [[<?php echo $VIEW["vars"]["LIST"]["order"]["column"]; ?>, "<?php echo $VIEW["vars"]["LIST"]["order"]["type"]; ?>"]],<?php } ?>
		lengthMenu: [[1, 2, 3, 4, 5, 10, 25, 50, 100, 500, -1], [1, 2, 3, 4, 5, 10, 25, 50, 100, 500, "??sszes"]],
		language: {
			"decimal":        "",
			"emptyTable":     "Nincsenek adatok",
			"info":           "Megjelen??tve: _START_ - _END_, ??sszesen: _TOTAL_ elem",
			"infoEmpty":      "Nincs megjelen??thet?? elem",
			"infoFiltered":   "(??sszesen _MAX_ elemb??l t??rt??nt a sz??r??s)",
			"infoPostFix":    "",
			"thousands":      ",",
			"lengthMenu":     "Megjelen??t??s: _MENU_ elem",
			"loadingRecords": "T??lt??s...",
			"processing":     "Feldolgoz??s...",
			"search":         "Keres??s:",
			"zeroRecords":    "Nincs tal??lat",
			"paginate": {
				"first":      "Els??",
				"last":       "Utols??",
				"next":       "K??vetkez??",
				"previous":   "El??z??"
			},
			"aria": {
				"sortAscending":  ": Rendez??s n??vekv?? sorrendbe",
				"sortDescending": ": Rendez??s cs??kken?? sorrendbe"
			},
			"buttons": {
				"copyTitle": "M??sol??s v??g??lapra",
				// "copyKeys": "",
				"copySuccess": {
					_: "%d sor ??tm??solva",
					1: "1 sor ??tm??solva"
				}
			},
		},
		buttons: [
			{
				extend: "copyHtml5",
				exportOptions: {
                    columns: ".panel-row-col"
                },
				className: "btn-sm",
				text: "M??sol"
			},
			{
				extend: "csvHtml5",
				exportOptions: {
                    columns: ".panel-row-col"
                },
				className: "btn-sm",
				text: "CSV",
			},
			{
				extend: "excel",
				className: "btn-sm",
				exportOptions: {
                    columns: ".panel-row-col"
                },
				text: "Excel"
			},
			{
				extend: "pdf",
				className: "btn-sm",
				exportOptions: {
                    columns: ".panel-row-col"
                },
				text: "PDF"
			},
			{
				extend: "print",
				className: "btn-sm",
				exportOptions: {
                    columns: ".panel-row-col"
                },
				text: "Nyomtat"
			},
		],
	});
	</script>
	<script src="<?php echo PATH_WEB; ?>vendors/fancybox-2.1.5/jquery.fancybox.pack.js"></script>
	<script src="<?php echo PATH_WEB; ?>vendors/fancybox-2.1.5/jquery.mousewheel-3.0.6.pack.js"></script>
	<script>
	$(document).ready(function(){
		$(".fancybox").fancybox();
		
		setInterval(function(){
			$.ajax({
				type: "POST",
				url: "<?php echo PATH_WEB; ?>session-lifetime",
				headers: {"X-CSRF-TOKEN": "<?php echo csrf_token(); ?>"},
				dataType: "html",
				success: function(msg) {
					// console.log(msg);
				}
			});
		}, 180000); // 3 Minutes
	});
	</script>
	<script src="<?php echo $gentelellaDir; ?>build/js/custom.min.js"></script>			
	@yield("bodyBottom")
</body>
</html>