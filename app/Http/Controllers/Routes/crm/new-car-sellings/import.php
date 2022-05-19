<?php 
$newcarSellings = new \App\Http\Controllers\NewCarSellingController;
if(isset($routes[1]))
{
	if(isset($routes[2])) 
	{ 
		if($routes[2] == "file-download")
		{
			$import = $newcarSellings->getImport($routes[1]);
			if($import === false) { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
			elseif(!empty($import["data"]->content))
			{
				$fileName = $import["fileName"];
				header("Content-Type: text/csv; charset=iso-8859-2");
				header("Content-Disposition: attachment; filename=\"".$fileName."\"");
				$output = fopen("php://output", "w");
				fwrite($output, iconv("utf-8", "iso-8859-2", $import["data"]->content));
				fclose($output);
				exit;
			}
			else { $URL->redirect([$routes[0], $routes[1]], ["error" => "unknown"]); }
		}
		else { $URL->redirect([$routes[0], $routes[1]], ["error" => "unknown"]); }
	}
	else
	{
		#Import details
		$VIEW["vars"]["import"] = $newcarSellings->getImport($routes[1]);
		if($VIEW["vars"]["import"] === false) { $URL->redirect([$routes[0]], ["error" => "unknown"]); }
		else
		{
			$VIEW["title"] = "Új autó eladás importálás adatlap";
			$VIEW["name"] = "new-car-sellings.import-details";
		}
	}
}
elseif(isset($_POST["process"]) AND $_POST["process"])
{
	$importReturn = $newcarSellings->import();	
	if(isset($importReturn["errorMessage"]) AND !empty($importReturn["errorMessage"])) { $URL->redirect([$URL->routes[0]], ["error" => "newcar-selling-import-".$importReturn["errorMessage"]]); }
	else { $URL->redirect([$routes[0], $importReturn["id"]], ["success" => "newcar-selling-import"]); }
}
else
{
	$VIEW["title"] = "Új autó eladások importálása CSV fájlból";
	$VIEW["name"] = "new-car-sellings.import";
}
?>