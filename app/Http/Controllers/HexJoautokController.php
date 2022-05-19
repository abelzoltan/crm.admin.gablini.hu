<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\HexJoautok;

class HexJoautokController extends HexController
{	
	#CData details
	public function cdataXML()
	{
		return ["tipus", "leiras", "belso-kenyelmi-felszereltsegek", "passziv-es-mechanikus-biztonsag", "vezetestamogato-rendszerek", "multimedia-rendszerek", "szallitas-rakodas"];
	}
	
	#Export to joautok.hu
	public function getCarsForXML($typeID, $deleted = 0, $key = "id")
	{
		$rows = $this->model->getCarsByType($typeID, 0, $orderNumber = "price, brand, name");
		
		$return = [];
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$car = $this->getCarForXML($row->id, false); 
			if(isset($car["price"]) AND !empty($car["price"])) { $return[$keyHere] = $car; }
		}
		return $return;
	}
	
	public function getCarsForXML2($types, $deleted = 0, $key = "id")
	{
		$rows = $this->model->getCarsByTypes($types, 0, $orderNumber = "price, brand, name");
		
		$return = [];
		foreach($rows AS $i => $row)
		{
			if(empty($key)) { $keyHere = $i; }
			else { $keyHere = $row->$key; }
			$car = $this->getCarForXML($row->id, false); 
			if(isset($car["price"]) AND !empty($car["price"])) { $return[$keyHere] = $car; }
		}
		return $return;
	}
	
	public function getCarForXML($id, $allDatas = false)
	{
		$return = [];
		$car = $this->getCar($id, $allDatas);
		
		$return["price"] = $car["price"];
		$return["datas"] = [];
		
		# ----------
		
		#Egyedi azonosító
		$return["datas"]["egyedi-azonosito"] = $car["url"];
		
		#(!) Eurotax
		$return["datas"]["eurotax"] = NULL;
		
		#Márka
		$dataHere = $car["car"]->brand;
		$dataThere = "marka";
		if(!empty($dataHere)) { $return["datas"][$dataThere] = mb_convert_case($dataHere, MB_CASE_UPPER, "utf-8"); }
		else { $return["datas"][$dataThere] = NULL; }
		
		#Modell és típus
		$dataHere = $car["car"]->model;
		$dataThere = "modell";
		if(!empty($dataHere)) { $return["datas"][$dataThere] = $dataHere; }
		else { $return["datas"][$dataThere] = NULL; }
		
		$dataHere = $car["car"]->name;
		$dataThere = "tipus";
		if(!empty($dataHere) AND !empty($return["datas"]["modell"])) { $return["datas"][$dataThere] = mb_substr($dataHere, mb_strlen($return["datas"]["modell"], "utf-8"), NULL, "utf-8");  }
		else { $return["datas"][$dataThere] = NULL; }
		
		#Évjárat
		$dataHereKey = "evjarat";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->value;
			$dataThere = "evjarat";
			if(!empty($dataHere)) { $return["datas"][$dataThere] = str_replace("/", ".", $dataHere); }
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#Kilométer
		$dataHereKey = "futottkm";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->baseValue;
			$dataThere = "kilometer";
			if(!empty($dataHere)) { $return["datas"][$dataThere] = $dataHere; }
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#Dokumentumok (Okmányok)
		$dataHereKey = "okmany";
		if(isset($car["datas"][$dataHereKey]))
		{			
			$dataHere = $car["datas"][$dataHereKey]->value;
			if(!empty($dataHere)) 
			{ 
				switch($dataHere)
				{
					case "Érvényes magyar okmányokkal":
						$return["datas"]["dokumentumok-jellege"] = "Magyar";
						$return["datas"]["dokumentumok-ervenyessege"] = "Érvényes";
						break;
					case "Forgalomból ideiglenesen kivont, magyar okmányokkal":
						$return["datas"]["dokumentumok-jellege"] = "Magyar";
						$return["datas"]["dokumentumok-ervenyessege"] = "Forgalomból kivonva";
						break;
					case "Lejárt magyar okmányokkal":
						$return["datas"]["dokumentumok-jellege"] = "Magyar";
						$return["datas"]["dokumentumok-ervenyessege"] = "Lejárt";
						break;
					case "Okmányok nélkül":
						$return["datas"]["dokumentumok-jellege"] = "Hiányzik";
						$return["datas"]["dokumentumok-ervenyessege"] = NULL;
						break;
					default:
						$return["datas"]["dokumentumok-jellege"] = NULL;
						$return["datas"]["dokumentumok-ervenyessege"] = NULL;
						break;
				}
			}
			else 
			{ 
				$return["datas"]["dokumentumok-jellege"] = NULL; 
				$return["datas"]["dokumentumok-ervenyessege"] = NULL; 
			}
		}
		else
		{ 
			$return["datas"]["dokumentumok-jellege"] = NULL; 
			$return["datas"]["dokumentumok-ervenyessege"] = NULL; 
		}
		
		#(!) Alvázszám
		$return["datas"]["alvazszam"] = NULL;
		
		#(!) Rendszám
		$return["datas"]["rendszam"] = NULL;
		
		#Műszaki vizsga
		$dataHereKey = "muszaki";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->value;
			$dataThere = "muszaki";
			if(!empty($dataHere)) { $return["datas"][$dataThere] = str_replace("/", ".", $dataHere); }
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#Ár
		$dataHere = $car["priceGrossUnFormatted"];
		$dataThere = "ar";
		if(!empty($dataHere)) { $return["datas"][$dataThere] = $dataHere; }
		else { $return["datas"][$dataThere] = NULL; }
		
		#Leírás
		$dataThere = "leiras";
		$return["datas"][$dataThere] = "";
		$i = 0;
		foreach($this->datasDetailsTexts AS $key)
		{
			if(isset($car["datas"][$key]))
			{
				if($i > 0) { $return["datas"][$dataThere] .= ", "; }
				$return["datas"][$dataThere] .= $car["datas"][$key]->name.": ".$car["datas"][$key]->value;
				/*$return["datas"][$dataThere] .= "
					<h2>".$car["datas"][$key]->name."</h2>
					<p>".$car["datas"][$key]->value."</p>
				";*/
				$i++;
			}
		}
		if(empty($return["datas"][$dataThere])) {  $return["datas"][$dataThere] = NULL; }
		
		#Email
		$dataHereKey = "emailcim";
		$dataThere = "email";		
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->value;
			if(!empty($dataHere)) { $return["datas"][$dataThere] = $dataHere; }
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		if($return["datas"][$dataThere] === NULL OR $car["type"]->url == "infiniti-keszlet" OR $car["type"]->url == "infiniti-hasznalt")
		{
			switch($car["type"]->url)
			{
				case "keszlet-nissan": $dataHere = "vas.zoltan@gablini.hu"; break;
				case "keszlet-kia": $dataHere = "toth.bence@gablini.hu"; break;
				case "ajanlatok-hyundai": $dataHere = "szekeres.zoltan@gablini.hu"; break;
				case "tesztautok-kiemelt-peugeot": $dataHere = "varga.tamas@gablini.hu"; break;
				case "tesztautok-peugeot": $dataHere = "vermes.sandor@gablini.hu"; break;
				case "infiniti-keszlet": $dataHere = "rozman.arpad@gablini.hu"; break;
				case "infiniti-hasznalt": $dataHere = "csizmazia.gabor@gablini.hu"; break;
				default: $dataHere = "safar.balazs@gablini.hu"; break;
			}
			$return["datas"][$dataThere] = $dataHere;
		}
		
		#Állapot
		$dataHereKey = "allapot";
		if(isset($car["datas"][$dataHereKey]))
		{			
			$dataHere = $car["datas"][$dataHereKey]->value;
			$dataThere = "allapot";
			if(!empty($dataHere)) 
			{ 
				switch($dataHere)
				{
					case "Kitűnő":
					case "Normál":
					case "Újszerű":
						$return["datas"][$dataThere] = "Normál állapot";
						break;
					default:
						$return["datas"][$dataThere] = NULL;
						break;
				}
			}
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#Kivitel
		$dataHereKey = "kivitel";
		if(isset($car["datas"][$dataHereKey]))
		{			
			$dataHere = $car["datas"][$dataHereKey]->value;
			$dataThere = "kivitel";
			if(!empty($dataHere)) 
			{ 
				switch($dataHere)
				{
					case "Cabrio":
						$return["datas"][$dataThere] = "Kabrió";
						break;
					case "Coupe":
						$return["datas"][$dataThere] = "Kupé";
						break;
					case "Egyterű":
						$return["datas"][$dataThere] = $dataHere;
						break;	
					case "Ferdehátú":
						$return["datas"][$dataThere] = $dataHere;
						break;
					case "Kombi":
						$return["datas"][$dataThere] = $dataHere;
						break;
					case "Terepjáró":
						$return["datas"][$dataThere] = $dataHere;
						break;
					case "Városi terepjáró (crossover)":
						$return["datas"][$dataThere] = $dataHere;
						break;	
					case "Alváz szimpla kabinnal":
					case "Duplakabinos pickup":
					case "Duplakabinos platós":
					case "Sedan":
					case "Sport":
					case "Zárt":
					default:
						$return["datas"][$dataThere] = "Egyéb kivitel";
						break;
				}
			}
			else { $return["datas"][$dataThere] = "Egyéb kivitel"; }
		}
		else { $return["datas"][$dataThere] = "Egyéb kivitel"; }
		
		#Személyek
		$dataHereKey = "szallithato_szemelyek";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->value;
			$dataThere = "szallithato-szemelyek-szama";
			if(!empty($dataHere)) { $return["datas"][$dataThere] = mb_substr($dataHere, 0, 1, "utf-8"); }
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#Ajtók
		$dataHereKey = "ajtok";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->value;
			$dataThere = "ajtok-szama";
			if(!empty($dataHere)) 
			{ 
				if($dataHere > 2 AND $dataHere < 5) { $return["datas"][$dataThere] = $dataHere." ajtós"; }
				else { $return["datas"][$dataThere] = NULL; }
			}
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#Saját tömeg
		$dataHereKey = "sajat_tomeg";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->baseValue;
			$dataThere = "sajat-tomeg";
			if(!empty($dataHere)) { $return["datas"][$dataThere] = $dataHere; }
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#Össztömeg
		$dataHereKey = "ossztomeg";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->baseValue;
			$dataThere = "ossztomeg";
			if(!empty($dataHere)) { $return["datas"][$dataThere] = $dataHere; }
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#Csomagtér
		$dataHereKey = "csomagtarto";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->baseValue;
			$dataThere = "csomagter";
			if(!empty($dataHere)) { $return["datas"][$dataThere] = $dataHere; }
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#Üzemanyag
		$dataHereKey = "uzemanyag";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->baseValue;
			$dataThere = "uzemanyag-tipus";
			if(!empty($dataHere)) 
			{ 
				switch($dataHere)
				{
					case "Benzin":
					case "Dízel":
					case "Elektromos":
					case "Hibrid":
						$return["datas"][$dataThere] = $dataHere;
						break;
					case "Benzin/elektromos":
					case "Dízel/elektromos":
						$return["datas"][$dataThere] = "Hibrid";
						break;	
					default:
						$return["datas"][$dataThere] = "Egyéb üzemanyag";
						break;
				}
			}
			else { $return["datas"][$dataThere] = "Egyéb üzemanyag"; }
		}
		else { $return["datas"][$dataThere] = "Egyéb üzemanyag"; }
		
		#(!) Motor jellege
		$return["datas"]["motor-jellege"] = NULL;
		
		#Hengerűrtartalom
		$dataHereKey = "hengerurtartalom";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->baseValue;
			$dataThere = "hengerurtartalom";
			if(!empty($dataHere)) { $return["datas"][$dataThere] = $dataHere; }
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#Teljesítmény (LE, kW)
		$dataHereKey = "teljesitmeny";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->baseValue;
			if(!empty($dataHere)) 
			{ 
				$return["datas"]["teljesitmeny-kw"] = $dataHere; 
				$return["datas"]["teljesitmeny-loero"] = round($dataHere * 1.36); 
			}
			else 
			{ 
				$return["datas"]["teljesitmeny-kw"] = NULL; 
				$return["datas"]["teljesitmeny-loero"] = NULL; 
			}
		}
		else 
		{ 
			$return["datas"]["teljesitmeny-kw"] = NULL; 
			$return["datas"]["teljesitmeny-loero"] = NULL; 
		}
		
		#Hajtás
		$dataHereKey = "hajtas";
		if(isset($car["datas"][$dataHereKey]))
		{			
			$dataHere = $car["datas"][$dataHereKey]->value;
			$dataThere = "hajtas-tipus";
			if(!empty($dataHere)) 
			{ 
				switch($dataHere)
				{
					case "Első kerék":
						$return["datas"][$dataThere] = "Elsőkerék hajtás";
						break;
					case "Hátsó kerék":
						$return["datas"][$dataThere] = "Hátsókerék hajtás";
						break;
					case "Összkerék":
						$return["datas"][$dataThere] = "Összkerékhajtás";
						break;
					default:
						$return["datas"][$dataThere] = NULL;
						break;
				}
			}
			else { $return["datas"][$dataThere] = NULL; }
		}
		else { $return["datas"][$dataThere] = NULL; }
		
		#(!) Környezetvédelmi osztály
		$return["datas"]["kornyezetvedelmi-osztaly"] = NULL;
		
		#Sebességváltó: fajta, fokozatok
		$dataHereKey = "sebessegvalto";
		if(isset($car["datas"][$dataHereKey]))
		{
			$dataHere = $car["datas"][$dataHereKey]->baseValue;
			if(!empty($dataHere)) 
			{ 
				switch($dataHere)
				{
					case "A0":
						$return["datas"]["sebessegvalto-fajtaja"] = "Automata"; 
						$return["datas"]["sebessegfokozatok-szama"] = NULL; 
						break;
					case "A4":
						$return["datas"]["sebessegvalto-fajtaja"] = "Automata"; 
						$return["datas"]["sebessegfokozatok-szama"] = "4 sebességes"; 
						break;
					case "A5":
						$return["datas"]["sebessegvalto-fajtaja"] = "Automata"; 
						$return["datas"]["sebessegfokozatok-szama"] = "5 sebességes"; 
						break;	
					case "A6":
						$return["datas"]["sebessegvalto-fajtaja"] = "Automata"; 
						$return["datas"]["sebessegfokozatok-szama"] = "6 sebességes"; 
						break;
					case "A7":
						$return["datas"]["sebessegvalto-fajtaja"] = "Automata"; 
						$return["datas"]["sebessegfokozatok-szama"] = "7 sebességes"; 
						break;
					case "M5":
						$return["datas"]["sebessegvalto-fajtaja"] = "Manuális"; 
						$return["datas"]["sebessegfokozatok-szama"] = "5 sebességes"; 
						break;
					case "M6":
						$return["datas"]["sebessegvalto-fajtaja"] = "Manuális"; 
						$return["datas"]["sebessegfokozatok-szama"] = "6 sebességes"; 
						break;
					case "S6": # (!)
						$return["datas"]["sebessegvalto-fajtaja"] = "Automatizált"; 
						$return["datas"]["sebessegfokozatok-szama"] = "6 sebességes"; 
						break;
					case "T5":
						$return["datas"]["sebessegvalto-fajtaja"] = "Automata - Tiptronic"; 
						$return["datas"]["sebessegfokozatok-szama"] = "5 sebességes"; 
						break;
					case "T6":
						$return["datas"]["sebessegvalto-fajtaja"] = "Automata - Tiptronic"; 
						$return["datas"]["sebessegfokozatok-szama"] = "6 sebességes"; 
						break;
					case "T7":
						$return["datas"]["sebessegvalto-fajtaja"] = "Automata - Tiptronic"; 
						$return["datas"]["sebessegfokozatok-szama"] = "7 sebességes"; 
						break;
					case "T8":
						$return["datas"]["sebessegvalto-fajtaja"] = "Automata - Tiptronic"; 
						$return["datas"]["sebessegfokozatok-szama"] = "8 sebességes"; 
						break;
					case "V0":
						$return["datas"]["sebessegvalto-fajtaja"] = "Automata - Fokozatmentes"; 
						$return["datas"]["sebessegfokozatok-szama"] = "Fokozatmentes"; 
						break;
					default:
						$return["datas"]["sebessegvalto-fajtaja"] = NULL; 
						$return["datas"]["sebessegfokozatok-szama"] = NULL; 
						break;
				}
			}
			else 
			{ 
				$return["datas"]["sebessegvalto-fajtaja"] = NULL; 
				$return["datas"]["sebessegfokozatok-szama"] = NULL; 
			}
		}
		else 
		{ 
			$return["datas"]["sebessegvalto-fajtaja"] = NULL; 
			$return["datas"]["sebessegfokozatok-szama"] = NULL; 
		}
		
		#(!) Felezőfokozat
		$return["datas"]["felezofokozatok-szama"] = NULL;
		
		#(!) Első gumi
		$return["datas"]["elso-gumi-meret"] = NULL;
		
		#(!) Hátsó gumi
		$return["datas"]["hatso-gumi-meret"] = NULL;
		
		#(!) Belső kényelmi felszereltségek
		$dataThere = "belso-kenyelmi-felszereltsegek";
		$return["datas"][$dataThere] = [];
		
			#Klíma
			$dataHereKey = "klima";
			if(isset($car["datas"][$dataHereKey]))
			{
				$dataHere = $car["datas"][$dataHereKey]->value;
				switch($dataHere)
				{
					case "Digitális klíma":
						$return["datas"][$dataThere][] = $dataHere;
						break;
					case "Digitális kétzónás klíma":
						$return["datas"][$dataThere][] = "2 zónás digitális klíma";
						break;
					case "Digitális többzónás klíma":
						$return["datas"][$dataThere][] = "3 vagy többzónás digitális klíma";
						break;
					case "Manuális klíma":
						$return["datas"][$dataThere][] = $dataHere;
						break;
					case "Automata klíma": # (!)
						$return["datas"][$dataThere][] = "Digitális klíma";
						break;
				}
			}
			
			#(!) Kesztyűtartó, Elektromos ablak, Kárpit, Kormány, Ülések, ...
		
		#(!) Külső kényelmi felszereltségek	
		$dataThere = "kulso-kenyelmi-felszereltsegek";
		$return["datas"][$dataThere] = [];
			
			#Tető
			$dataHereKey = "teto";
			if(isset($car["datas"][$dataHereKey]))
			{
				$dataHere = $car["datas"][$dataHereKey]->value;
				switch($dataHere)
				{
					case "Fix napfénytető":
						$return["datas"][$dataThere][] = $dataHere;
						break;
					case "Panoráma tető":
						$return["datas"][$dataThere][] = "Panorámatető";
						break;		
					case "Fix üvegtető":
					case "Lemeztető":
					case "Nyitható keménytető":
					case "Nyitható napfénytető":
						break;
				}
			}
			
			#(!) Esőérzékelő, Visszapillantók, Kp-i zár, ...
			
		#(!) Biztonság
		$dataThere = "passziv-es-mechanikus-biztonsag";
		$return["datas"][$dataThere] = [];			
			#(!) Légzsák, Öv, Fejtámlák, Fényszóró, ...	
			
		#(!) Vezetéstámogatás
		$dataThere = "vezetestamogato-rendszerek";
		$return["datas"][$dataThere] = [];			
			#(!) ABS, ASR, Asszisztensek, Tempomat, Navigáció, ...
			
		#(!) Felfüggesztés
		$dataThere = "motor-hajtas-felfuggesztes";
		$return["datas"][$dataThere] = [];			
			#(!) Rugózás, Futómű, ...
			
		#(!) Multimédia
		$dataThere = "multimedia-rendszerek";
		$return["datas"][$dataThere] = [];		
			#(!) Rádió, hangszórók, Csatlakozók, ...
			
		#(!) Szállítás / Rakodás
		$dataThere = "szallitas-rakodas";
		$return["datas"][$dataThere] = [];
			#(!) Csomagtér, dönthető ülések, vonóhorog, ...	
			
		#Képek
		$dataThere = "kepek";
		$return["datas"][$dataThere] = [];
		foreach($car["pics"] AS $pic)
		{
			if(!empty($pic["row"]->big)) { $link = $pic["row"]->big; }
			else { $link = $pic["row"]->basic; }
			$return["datas"][$dataThere][] = [
				"pozicio" => "egyéb kép",
				"url" => $link,
			];
			
		}
		
		#(!) Dokumentumok
		$dataThere = "dokumentumok";
		$return["datas"][$dataThere] = [];			
			#(!) Állapotfelmérés, KM óra állás, ...
			
		#(!) Események
		$dataThere = "esemenyek";
		$return["datas"][$dataThere] = [];			
			#(!) Vizsga, Tulajdonos váltás, ...
		
		#RETURN --------------------
		return $return;
	}
}
