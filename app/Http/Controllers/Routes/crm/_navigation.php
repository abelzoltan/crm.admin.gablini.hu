<?php 
#Menus
$VIEW["vars"]["navigation"] = $navigation = [	
	"crm" => [
		"name" => "CRM menedzsment",
		"ranks" => $basicRanks, 
		"menu" => [	
			"home" => [
				"url" => PATH_WEB,
				"targetBlank" => false,
				"icon" => "fa fa-home",
				"name" => "Kezdőlap",
				"ranks" => [], 
			],	
			"customers" => [
				"url" => PATH_WEB."customers",
				"targetBlank" => false,
				"icon" => "fa fa-users",
				"name" => "Ügyfelek",
				"ranks" => [], 
			],
			"customer-details" => [
				"url" => "",
				"targetBlank" => false,
				"icon" => "fa fa-user",
				"name" => "Ügyfél adatlap",
				"ranks" => [], 
			],
			"questionnaire-answers" => [
				"url" => PATH_WEB."questionnaire-answers",
				"targetBlank" => false,
				"icon" => "fa fa-question-circle",
				"name" => "Kérdőív válaszok",
				"ranks" => [], 
			],			
		],
	],
	"services" => [
		"name" => "Szerviz események",
		"ranks" => $basicRanks, 
		"menu" => [				
			"imports" => [
				"url" => PATH_WEB."service-import",
				"targetBlank" => false,
				"icon" => "fa fa-upload",
				"name" => "Események importálása",
				"ranks" => [],  
			],
			"events" => [
				"url" => PATH_WEB."service-events",
				"targetBlank" => false,
				"icon" => "fa fa-calendar",
				"name" => "Felvitt események",
				"ranks" => [],  
			],
			"events-answered" => [
				"url" => PATH_WEB."service-events-answered",
				"targetBlank" => false,
				"icon" => "fa fa-check",
				"name" => "Esemény kezelések",
				"ranks" => [3, 4, 6],  
			],
			"exports" => [
				"url" => PATH_WEB."service-exports",
				"targetBlank" => false,
				"icon" => "fa fa-download",
				"name" => "Exportok",
				"ranks" => [],  
			],
		],
	],
	"service-todo" => [
		"name" => "Enying - Szerviz teendők",
		"ranks" => $ranksForEnyingMenu, 
		"menu" => [
			/*"service-todo-general" => [
				"url" => PATH_WEB."service-todo/general",
				"targetBlank" => false,
				"icon" => "gablini.png",
				"name" => "Márkafüggetlen teendők",
				"ranks" => [],  
			],
			"service-todo-hyundai" => [
				"url" => PATH_WEB."service-todo/hyundai",
				"targetBlank" => false,
				"icon" => "hyundai.png",
				"name" => "Hyundai teendők",
				"ranks" => [],  
			],
			"service-todo-kia" => [
				"url" => PATH_WEB."service-todo/kia",
				"targetBlank" => false,
				"icon" => "kia.png",
				"name" => "Kia teendők",
				"ranks" => [],  
			],
			"service-todo-nissan" => [
				"url" => PATH_WEB."service-todo/nissan",
				"targetBlank" => false,
				"icon" => "nissan.png",
				"name" => "Nissan teendők",
				"ranks" => [],  
			],
			"service-todo-peugeot" => [
				"url" => PATH_WEB."service-todo/peugeot",
				"targetBlank" => false,
				"icon" => "peugeot.png",
				"name" => "<span class='color-black'>Peugeot teendők</span>",
				"ranks" => [],  
			],
			"service-todo-citroen" => [
				"url" => PATH_WEB."service-todo/citroen",
				"targetBlank" => false,
				"icon" => "citroen.png",
				"name" => "Citroen teendők",
				"ranks" => [],  
			],
			"service-todo-infiniti" => [
				"url" => PATH_WEB."service-todo/infiniti",
				"targetBlank" => false,
				"icon" => "infiniti.png",
				"name" => "Infiniti teendők",
				"ranks" => [],  
			],*/
			"service-todo-2021" => [
				"url" => PATH_WEB."service-todo/2021",
				"targetBlank" => false,
				"icon" => "gablini.png",
				"name" => "Teendők",
				"ranks" => [],  
			],
			"service-tracking" => [
				"url" => PATH_WEB."service-tracking",
				"targetBlank" => false,
				"icon" => "fa fa-phone",
				"name" => "Telefonszám nyomozás",
				"ranks" => [],  
			],
		],
	],
	"new-car-sellings" => [
		"name" => "Új autó eladás események",
		"ranks" => $basicRanks,
		"menu" => [				
			"new-car-sellings-imports" => [
				"url" => PATH_WEB."new-car-sellings-import",
				"targetBlank" => false,
				"icon" => "fa fa-upload",
				"name" => "Események importálása",
				"ranks" => [],  
			],
			"new-car-sellings-events" => [
				"url" => PATH_WEB."new-car-sellings-events",
				"targetBlank" => false,
				"icon" => "fa fa-calendar",
				"name" => "Felvitt események",
				"ranks" => [],  
			],
			"new-car-sellings-exports" => [
				"url" => PATH_WEB."new-car-sellings-exports",
				"targetBlank" => false,
				"icon" => "fa fa-download",
				"name" => "Exportok",
				"ranks" => [],  
			],
		],
	],
	"new-car-sellings-todo" => [
		"name" => "Új autó eladás teendők",
		"ranks" => $ranksForNewCarSellingsMenu,
		"menu" => [
			"new-car-sellings-home" => [
				"url" => PATH_WEB."new-car-sellings-home",
				"targetBlank" => false,
				"icon" => "fa fa-home",
				"name" => "Új autó eladás kezdőlap",
				"ranks" => [],  
			],
			"new-car-sellings-todo-2022" => [
				"url" => PATH_WEB."new-car-sellings-todo/2022",
				"targetBlank" => false,
				"icon" => "gablini.png",
				"name" => "Teendők - ÚJ KÉRDŐÍV",
				"ranks" => [],  
			],
			"new-car-sellings-todo-general" => [
				"url" => PATH_WEB."new-car-sellings-todo/general",
				"targetBlank" => false,
				"icon" => "gablini.png",
				"name" => "Márkafüggetlen teendők",
				"ranks" => [],  
			],
			"new-car-sellings-todo-hyundai" => [
				"url" => PATH_WEB."new-car-sellings-todo/hyundai",
				"targetBlank" => false,
				"icon" => "hyundai.png",
				"name" => "Hyundai teendők",
				"ranks" => [],  
			],
			"new-car-sellings-todo-kia" => [
				"url" => PATH_WEB."new-car-sellings-todo/kia",
				"targetBlank" => false,
				"icon" => "kia.png",
				"name" => "Kia teendők",
				"ranks" => [],  
			],
			"new-car-sellings-todo-nissan" => [
				"url" => PATH_WEB."new-car-sellings-todo/nissan",
				"targetBlank" => false,
				"icon" => "nissan.png",
				"name" => "Nissan teendők",
				"ranks" => [],  
			],
			"new-car-sellings-todo-peugeot" => [
				"url" => PATH_WEB."new-car-sellings-todo/peugeot",
				"targetBlank" => false,
				"icon" => "peugeot.png",
				"name" => "<span class='color-black'>Peugeot teendők</span>",
				"ranks" => [],  
			],
			"new-car-sellings-todo-citroen" => [
				"url" => PATH_WEB."new-car-sellings-todo/citroen",
				"targetBlank" => false,
				"icon" => "citroen.png",
				"name" => "Citroen teendők",
				"ranks" => [],  
			],
			"new-car-sellings-todo-infiniti" => [
				"url" => PATH_WEB."new-car-sellings-todo/infiniti",
				"targetBlank" => false,
				"icon" => "infiniti.png",
				"name" => "Infiniti teendők",
				"ranks" => [],  
			],
		],
	],
	"others" => [
		"name" => "További oldalak",
		"ranks" => $basicRanks, 
		"menu" => [
			"all-websites" => [
				"url" => "",
				"targetBlank" => false,
				"icon" => "fa fa-globe",
				"name" => "Publikus oldalak",
				"ranks" => [], 
				"menu" => [
					"g.hu" => [
						"url" => "//gablini.hu",
						"targetBlank" => true,
						"icon" => "fa fa-google",
						"name" => "Gablini",
						"ranks" => [], 
						"menu" => [], 
					],
					"hg.hu" => [
						"url" => "//hyundaigablini.hu",
						"targetBlank" => true,
						"icon" => "fa fa-header",
						"name" => "Hyundai Gablini",
						"ranks" => [], 
						"menu" => [], 
					],
					"kg.hu" => [
						"url" => "//kiagablini.hu",
						"targetBlank" => true,
						"icon" => "fa fa-circle-o",
						"name" => "Kia Gablini",
						"ranks" => [], 
						"menu" => [], 
					],
					"ng.hu" => [
						"url" => "//nissangablini.hu",
						"targetBlank" => true,
						"icon" => "fa fa-car",
						"name" => "Nissan Gablini",
						"ranks" => [], 
						"menu" => [], 
					],
					"pg.hu" => [
						"url" => "//peugeotgablini.hu",
						"targetBlank" => true,
						"icon" => "fa fa-pinterest-p",
						"name" => "Peugeot Gablini",
						"ranks" => [], 
						"menu" => [], 
					],
				], 
			],
			"admin-websites" => [
				"url" => "//admin2017.gablini.hu",
				"targetBlank" => true,
				"icon" => "fa fa-lock",
				"name" => "Admin panel 2017",
				"ranks" => [], 
			],
		],
	],
	"hidden" => [
		"name" => "Rejtett menük",
		"ranks" => $users->loginAcceptedRanks, 
		"menu" => [
			"profile" => [
				"url" => PATH_WEB."profile",
				"targetBlank" => false,
				"icon" => "",
				"name" => "Profil",
				"ranks" => [], 
				"menu" => [], 
			],
			
			"new-car-sellings-log" => [
				"url" => PATH_WEB."new-car-sellings-log",
				"targetBlank" => false,
				"icon" => "",
				"name" => "Új autó eladások napló",
				"ranks" => [],  
			],
		],
	],
];

if(isset($routes[0]) AND $routes[0] == "customer" AND isset($routes[1])) { $navigation["crm"]["menu"]["customer-details"]["url"] = $VIEW["vars"]["navigation"]["crm"]["menu"]["customer-details"]["url"] = PATH_WEB.$routes[0]."/".$routes[1]; }
else 
{
	unset($navigation["crm"]["menu"]["customer-details"]);
	unset($VIEW["vars"]["navigation"]["crm"]["menu"]["customer-details"]);
}

#Active menu
$VIEW["vars"]["activeMenu"] = $activeMenu = [
	"navKey" => "",
	"menuKey" => "",
	"subMenuKey" => "",
	"navData" => [
		"ranks" => [],
	],
	"menuData" => [
		"ranks" => [],
	],
	"subMenuData" => [
		"ranks" => [],
	],
	"level" => 0,
];
foreach($navigation AS $navKey => $navData)
{
	if(count($navData["menu"]) > 0)
	{
		foreach($navData["menu"] AS $menuKey => $menu)
		{
			if(!empty($menu["url"]) AND ($GLOBALS["URL"]->currentURL == $menu["url"] OR PATH_WEB.$routes[0] == $menu["url"] OR PATH_WEB.$routes[0]."/".$routes[1] == $menu["url"]))
			{
				$VIEW["vars"]["activeMenu"]["navKey"] = $activeMenu["navKey"] = $navKey;
				$VIEW["vars"]["activeMenu"]["menuKey"] = $activeMenu["menuKey"] = $menuKey;
				$VIEW["vars"]["activeMenu"]["navData"] = $activeMenu["navData"] = $navData;
				$VIEW["vars"]["activeMenu"]["menuData"] = $activeMenu["menuData"] = $menu;
				$VIEW["vars"]["activeMenu"]["level"] = $activeMenu["level"] = 1;
				$VIEW["title"] = $menu["name"];
			}
			elseif(count((array)$menu["menu"]) > 0)
			{
				foreach($menu["menu"] AS $subMenuKey => $subMenu)
				{
					if(!empty($subMenu["url"]) AND ($GLOBALS["URL"]->currentURL == $subMenu["url"] OR PATH_WEB.$routes[0] == $subMenu["url"] OR PATH_WEB.$routes[0]."/".$routes[1] == $subMenu["url"]))
					{
						$VIEW["vars"]["activeMenu"]["navKey"] = $activeMenu["navKey"] = $navKey;
						$VIEW["vars"]["activeMenu"]["menuKey"] = $activeMenu["menuKey"] = $menuKey;
						$VIEW["vars"]["activeMenu"]["subMenuKey"] = $activeMenu["menuKey"] = $subMenuKey;
						$VIEW["vars"]["activeMenu"]["navData"] = $activeMenu["navData"] = $navData;
						$VIEW["vars"]["activeMenu"]["menuData"] = $activeMenu["menuData"] = $menu;
						$VIEW["vars"]["activeMenu"]["subMenuData"] = $activeMenu["subMenuData"] = $subMenu;
						$VIEW["vars"]["activeMenu"]["level"] = $activeMenu["level"] = 2;
						$VIEW["title"] = $subMenu["name"];
					}
				}
			}
		}
	}
}
?>