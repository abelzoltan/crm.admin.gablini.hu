<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
use App\File;

class FileController extends BaseController
{
	public $model;
	public $dirInner = "files/";
	public $dirWeb = "files/";
	public $watchURL = "file/watch-url/";
	
	#Constructor
	public function __construct($connectionData = ["name" => MYSQL_CONNECTION_NAME_G])
	{
		$this->modelName = "File";
		$this->model = new \App\File($connectionData);
		
		if(defined("CDN") AND CDN)
		{
			$this->dirInner = CDN_PATH;
			$this->dirWeb = CDN_PATH_WEB;
		}
		elseif(defined("CDN_APP") AND CDN_APP)
		{
			$this->dirInner = CDN_APP_PATH;
			$this->dirWeb = CDN_APP_PATH_WEB;
		}
		else
		{
			if(defined("DIR_PUBLIC")) { $this->dirInner = DIR_PUBLIC.$this->dirInner; }
			else { $this->dirInner = public_path()."/".$this->dirInner; }
			
			if(defined("DIR_PUBLIC_WEB")) { $this->dirWeb = DIR_PUBLIC_WEB.$this->dirWeb; }
			else { $this->dirWeb = env("PATH_ROOT_WEB").$this->dirWeb; }
		}
		
		if(defined("PATH_WEB")) { $this->watchURL = PATH_WEB.$this->watchURL; }
		else { $this->watchURL = env("PATH_ROOT_WEB").$this->watchURL; }
	}
	
	#Datas
	public function datas($arrayName = "", $data = "")
	{
		$return = [];
		
		$return["dirs"] = [
			"inner" => $this->dirInner,
			"web" => $this->dirWeb,
		];
		
		$uploadMax = ini_get("upload_max_filesize");
		$postMax = ini_get("post_max_size");
		$memoryLimit = ini_get("memory_limit");
		$maxFileSize = min((int)$uploadMax, (int)$postMax, (int)$memoryLimit);
		
		$return["maxSize"] = [
			"upload" => $uploadMax,
			"post" => $postMax,
			"memoryLimit" => $memoryLimit,
			"B" => $maxFileSize * 1024 * 1024,
			"KB" => $maxFileSize * 1024,
			"MB" => $maxFileSize,
			"GB" => $maxFileSize / 1024,
		];
		
		if(empty($arrayName)) { return $return; }
		else
		{
			if(empty($data)) { return $return[$arrayName];  }
			else { return $return[$arrayName][$data]; }
		}
	}
	
	#Get file
	public function getFile($id, $folderVars = [])
	{
		$return = [];
		
		#Basic data
		$return["file"] = $file = $this->model->getFile($id);
		$return["id"] = $file->id;
		$return["type"] = $this->model->getType($file->type);
		$return["extension"] = $this->model->getExtension($file->extension);
		
		#Path data
		$dirs = $this->datas("dirs");
		$return["path"] = [];
		$return["path"]["base"] = $return["type"]->directory."/".$file->nameFile.".".$return["extension"]->name;
		$return["path"]["inner"] = $dirs["inner"].$return["path"]["base"];
		$return["path"]["web"] = $dirs["web"].$return["path"]["base"];
		
		#Name data
		if(!empty($file->name)) { $return["name"] = $file->name; }
		elseif(!empty($file->nameOriginal)) { $return["name"] = $file->nameOriginal; }
		else { $return["name"] = $file->nameFile; }
		$return["fullName"] = $return["name"].".".$return["extension"]->name;
		
		#URL data
		$return["urlName"] = $file->token."-".self::generateUrl($return["name"]).".".$return["extension"]->name;
		$return["url"] = $this->watchURL.$return["urlName"];
		
		$return["path"]["urlBase"] = $return["type"]->directory."/".$return["urlName"];
		$return["path"]["urlInner"] = $dirs["inner"].$return["path"]["urlBase"];
		$return["path"]["urlWeb"] = $dirs["web"].$return["path"]["urlBase"];
		
		return $return;
	}
	
	public function getFileList($typeName, $foreignKey, $deleted = 0)
	{
		$return = [];
		$files = $this->model->getFileList($typeName, $foreignKey, $deleted);

		foreach($files AS $file)
		{
			$return[$file->id] = $this->getFile($file->id);
		}
		
		return $return;
	}
	
	#Delete file
	public function delFile($id, $delFiles = 1, $really = 0)
	{
		$table = $this->model->tables("files");
		$file = $this->getFile($id);
		
		$this->model->myDelete($table, $id, $really);
		$this->model->reOrderFiles($file["type"]->id, $file["file"]->foreignKey);
		
		if($delFiles AND file_exists($file["path"]["inner"])) { unlink($file["path"]["inner"]); }
		
		if(isset($GLOBALS["log"]))
		{
			$GLOBALS["log"]->log("files-del", ["int1" => $id, "int2" => $delFiles, "int3" => $really, "text1" => $GLOBALS["log"]->json($file)]);
		}
	}
	
	#Edit file
	public function editFile($id, $params)
	{
		$this->model->myUpdate($this->model->tables("files"), $params, $id);
	}
	
	#Order files
	public function reOrderFiles($type, $foreignKey)
	{
		return $this->model->reOrderFiles($type, $foreignKey);
	}
	
	public function newOrderFiles($orderType, $currentID, $type, $foreignKey)
	{
		return $this->model->newOrderFiles($orderType, $currentID, $type, $foreignKey);
	}
	
	#Set file token, random string
	public function randomize($length = 10) 
	{
		#Arrays
		$numbers = range(0, 9);
		$smallLetters = range("a", "z");
		$capitalLetters = range("A", "Z");
		
		$chars = array_merge($numbers, $smallLetters, $capitalLetters);
		
		#Generate and return
		$string = "";
		for($i = 0; $i < $length; $i++) { $string .= $chars[mt_rand(0, count($chars) - 1)]; }
		
		return $string;
	}
	
	public function setFileToken()
	{
		while(true)
		{
			$string = $this->randomize(10);
			$token = $string;
			
			$file = $this->model->getFileByToken($token, "", 0);
			if(!isset($file->id) OR empty($file->id)) { break; }
		}
		return $token;
	}
	
	public function hashFileToken($token)
	{
		$after = "r26Mqrn3";
		return sha1($token.$after);
	}
	
	#File upload
	public function upload($inputName, $typeName, $foreignKey, $names = [])
	{
		$return = [];
		$i = 0;
		if(!empty($_FILES[$inputName]["name"]))
		{
			#Date
			$date = $this->model->now();
			$fileDate = date("YmdHis", strtotime($date));
			
			#Type and directory
			$type = $this->model->getTypeByName($typeName);
			if(isset($type->id) AND !empty($type->id))
			{
				#Datas
				$datas = $this->datas();
				$orderNumber = $this->model->reOrderFiles($type->id, $foreignKey);
				
				#Directory
				$dir = $datas["dirs"]["inner"];
				if(!is_dir($dir)) { \Illuminate\Support\Facades\File::makeDirectory($dir, 0777, true, true); }
				$dir .= "/".$type->directory;
				if(!is_dir($dir)) { \Illuminate\Support\Facades\File::makeDirectory($dir, 0777, true, true); }
				$dir .= "/";
				
				#Upload type: single OR multiple
				if($type->multiple) { $files = $_FILES[$inputName]; }
				else 
				{ 
					$files = [];
					foreach($_FILES[$inputName] AS $key => $val) 
					{ 
						$files[$key][0] = $val;
					} 
				}
				
				#Max filesize
				$maxFileSizeKB = $datas["maxSize"]["KB"];
				
				#Files work
				while (!empty($files["name"][$i]))
				{
					#Filename, temporary directory, size, extension
					$return[$i]["fileName"] = $fileName = $files["name"][$i];
					$return[$i]["tempDir"] = $tempDir = $files["tmp_name"][$i];
					$return[$i]["mime"] = $mime = $files["type"][$i];
					
					$fileSize = $files["size"][$i];
					$return[$i]["fileSize"] = $fileSizeKB = $fileSize / 1024;
					
					$originalExtension = explode(".", $fileName);
					$originalExtension = array_pop($originalExtension);
					$return[$i]["extension"] = $extension = strtolower($originalExtension);
					
					#Return pre-set
					$okay = true;
					$return[$i]["type"] = "success";
					
					#Extension check
					$ext = $this->model->getExtensionByName($extension);
					if($type->checkExtensions)
					{
						if(!isset($ext->id) OR empty($ext->id)) 
						{ 
							#Insert unknown extension ino database
							$extID = $this->model->myInsert($this->model->tables("extensions"), ["name" => $extension]);
							$okay = false; 
							$return[$i]["errors"][] = "unknown-extension"; 
						} 
						
						if(!empty($type->extensionCategories))
						{
							$extCategories = explode("|", $type->extensionCategories);
							if(!in_array($ext->category, $extCategories)) 
							{ 
								$okay = false; 
								$return[$i]["errors"][] = "banned-extension"; 
							} 
						}
					}
					
					#File size check
					if($fileSizeKB > $maxFileSizeKB) 
					{ 
						$okay = false; 
						$return[$i]["errors"][] = "max-file-size"; 
					}
					
					#If everything is OK
					if($okay)
					{
						#Original file name
						$originalFileName = pathinfo($fileName, PATHINFO_FILENAME);
						if(!isset($names[$i]) OR empty($names[$i])) { $names[$i] = NULL; }
						
						#Token and new filename
						$return[$i]["fileToken"] = $fileToken = $this->setFileToken();
						// $return[$i]["newFileName"] = $newFileName = $foreignKey."_".$fileDate."_".$fileToken;
						$return[$i]["newFileName"] = $newFileName = self::strMax(self::generateUrl($originalFileName), 235, "")."-".$fileToken;
						
						#Get order number
						$return[$i]["orderNumber"] = $orderNumber;
						
						#Get user
						if(defined("USERID")) { $user = USERID; }
						else { $user = NULL; }
						
						#Database insert
						$params = [
							"user" => $user,
							"date" => $date,
							"type" => $type->id,
							"foreignKey" => $foreignKey,
							"token" => $fileToken,
							"tokenHashed" => $this->hashFileToken($fileToken),
							"nameOriginal" => $originalFileName,
							"nameFile" => $newFileName,
							"name" => $names[$i],
							"extension" => $ext->id,
							"mime" => $mime,
							"sizeKB" => $fileSizeKB,
							"orderNumber" => $orderNumber,
						];
						$return[$i]["fileID"] = $this->model->myInsert($this->model->tables("files"), $params);
						$orderNumber++;
						
						#File moving
						$newFileNameFull = $newFileName.".".$extension;
						$return[$i]["fileExists"] = move_uploaded_file($tempDir, $dir.$newFileNameFull);
						if($return[$i]["fileExists"] == false) 
						{ 
							$return[$i]["type"] = "error"; 
							$return[$i]["errors"][] = "move-file"; 
						}
					}
					else { $return[$i]["type"] = "error"; }
					$this->log($inputName, $type->id, $date, $return[$i]["fileID"], $return[$i]);
					$i++;
				}	
			}
			else 
			{
				$return[$i]["type"] = "error";
				$return[$i]["errors"] = "unknown-type"; 
			}
		}
		else 
		{
			$return[$i]["type"] = "error";
			$return[$i]["errors"] = "no-file"; 
		}
		
		#Log
		if(isset($GLOBALS["log"]))
		{
			$GLOBALS["log"]->log("files-upload", ["text1" => $GLOBALS["log"]->json($return), "text2" => $GLOBALS["log"]->json($_FILES)]);
		}
		
		#Return
		return $return;
	}
	
	#File download
	public function download($id, $routes = [])
	{
		$file = $this->getFile($id);
		if(isset($file["file"]->id) AND !empty($file["file"]->id) AND file_exists($file["path"]["inner"]))
		{
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			header("Content-Disposition: attachment;filename=\".".$file["fullName"]."\"");
			header("Content-Transfer-Encoding: binary");
			readfile($file["path"]["inner"]);
			
			if(isset($GLOBALS["log"]))
			{
				$GLOBALS["log"]->log("files-download", ["int1" => $id, "text1" => $GLOBALS["log"]->json($file), "text2" => $GLOBALS["log"]->json($routes)]);
			}
			exit;
		}
		elseif(isset($GLOBALS["URL"]))
		{ 
			if(isset($GLOBALS["log"]))
			{
				$GLOBALS["log"]->log("files-download-error", ["int1" => $id, "int2" => 1, "text1" => $GLOBALS["log"]->json($routes)]);
			}
			$GLOBALS["URL"]->redirect($routes, ["error" => "file-download"]);
		}
		else
		{
			if(isset($GLOBALS["log"]))
			{
				$GLOBALS["log"]->log("files-download-error", ["int1" => $id, "int2" => 0, "text1" => $GLOBALS["log"]->json($routes)]);
			}
		}
	}
	
	public function downloadByToken($token, $routes = [])
	{
		$fileID = $this->model->getFileByToken($token, "id");
		$this->download($fileID, $routes);
	}
	
	public function downloadByHash($hash, $routes = [])
	{
		$fileID = $this->model->getFileByHash($hash, "id");
		$this->download($fileID, $routes);
	}
	
	#File watch
	public function watch($id, $routes = [], $url = "")
	{
		$okay = true;
		$file = $this->getFile($id);
		
		if(!empty($url))
		{
			if($file["urlName"] != $url) { $okay = false; }
		}
		
		if($okay AND isset($file["file"]->id) AND !empty($file["file"]->id) AND file_exists($file["path"]["inner"]))
		{
			header("Content-Type: ".$file["file"]->mime);
			header("Content-Disposition: filename=\"".$file["fullName"]."\"");
			readfile($file["path"]["inner"]);
			
			if(isset($GLOBALS["log"]))
			{
				$GLOBALS["log"]->log("files-watch", ["int1" => $id, "text1" => $GLOBALS["log"]->json($file), "text2" => $GLOBALS["log"]->json($routes)]);
			}
			exit;
		}
		elseif(isset($GLOBALS["URL"]))
		{ 
			if(isset($GLOBALS["log"]))
			{
				$GLOBALS["log"]->log("files-watch-error", ["int1" => $id, "int2" => 1, "text1" => $GLOBALS["log"]->json($routes)]);
			}
			$GLOBALS["URL"]->redirect($routes, ["error" => "file-download"]);
		}
		else
		{
			if(isset($GLOBALS["log"]))
			{
				$GLOBALS["log"]->log("files-watch-error", ["int1" => $id, "int2" => 0, "text1" => $GLOBALS["log"]->json($routes)]);
			}
		}
	}
	
	public function watchByToken($token, $routes = [], $url)
	{
		$fileID = $this->model->getFileByToken($token, "id");
		$this->watch($fileID, $routes, $url);
	}
	
	public function watchByHash($hash, $routes = [], $url)
	{
		$fileID = $this->model->getFileByHash($hash, "id");
		$this->watch($fileID, $routes, $url);
	}
	
	public function watchByURL($url, $routes = [])
	{
		$token = mb_substr($url, 0, 10, "utf-8");
		$this->watchByToken($token, $routes, $url);
	}
	
	#Log
	public function log($inputName, $type, $date, $fileID, $uploadReturn)
	{
		$params = [
			"inputName" => $inputName,
			"type" => $type,
			"date" => $date,
			"fileID" => $fileID,
			"uploadReturn" => $this->json($uploadReturn),
		];
		return $this->model->myInsert($this->model->tables("log"), $params);
	}
	
	public function json($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
}
?>