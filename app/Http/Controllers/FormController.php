<?php 
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\BaseController;
// use App\Form;

class FormController extends BaseController
{
	public $recaptchaSiteKey = "6LfFKCYTAAAAAJK6yyiAlRm7pHg4C5ocfNq_Bfco";
	public $recaptchaSecretKey = "6LfFKCYTAAAAABXWLnTfB6totSIcooYI1Hbv3Z2_";
	
	public function recaptchaResponse($data = "success")
	{
		// $recaptchaResponse = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$this->recaptchaSecretKey."&response=".$_POST["g-recaptcha-response"]."&remoteip=".$_SERVER["REMOTE_ADDR"]), true);
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify?secret=".$recaptchaSecretKey."&response=".$_POST["g-recaptcha-response"]."&remoteip=".$_SERVER["REMOTE_ADDR"]);
		curl_setopt($curl, CURLOPT_HEADER, 0);  
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$recaptchaResponse = curl_exec($curl);
		$recaptchaResponse = json_decode($recaptchaResponse, true);
		curl_close($curl);
		
		if(empty($data)) { return $recaptchaResponse; }
		else { return $recaptchaResponse[$data]; }
	}
	
	public function createForm($details, $panels)
	{
		$return = '<form class="form-horizontal form-label-left"';		
		foreach($details AS $tagName => $val) { $return .= ' '.$tagName.'="'.$val.'"'; }
			
		$return .= '>'.csrf_field();
		
		foreach($panels AS $panel)
		{
			$return .= $this->createPanel($panel["name"], $panel["inputs"], $panel["buttons"]);
		}
		
		$return .= '</form>';
		
		return $return;
	}
	
	public function createPanel($name, $inputs, $buttons)
	{
		$return = '
		<div class="x_panel">
			<div class="x_title">
				<div class="row">
					<div class="col-xs-12">
					<h2 class="font-bold">'.$name.'</h2>
						<ul class="nav navbar-right panel_toolbox">
							<li><a class="close-link"><i class="fa fa-close"></i></a></li>
							<li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
						</ul>
					</div>
				</div>
			</div>
			<div class="x_content">
		';
		
		foreach($inputs AS $input)
		{
			$return .= $this->createInput($input);
		}
		
		$return .= '
			<div class="ln_solid"></div>
			<div class="form-group">
				<div class="col-xs-12 text-center">
		';
		
		foreach($buttons AS $i => $button)
		{
			if($i > 0) { $return .= '<div class="visible-xs height-30"></div>'; }
			$return .= '<span class="hidden-xs">&nbsp;&nbsp;&nbsp;</span>';
			
			if($button["type"] == "submit")
			{
				$return .= '<button type="submit" class="btn btn-lg btn-success"><i class="fa fa-floppy-o"></i> &nbsp;Mentés</button>';
			}
			elseif($button["type"] == "back")
			{
				$return .= '<a class="btn btn-lg btn-danger" href="'.$button["href"].'"><i class="fa fa-arrow-left"></i> &nbsp;Vissza</a>';
			}
			else
			{
				$return .= '<a class="btn btn-lg btn-'.$button["class"].'" href="'.$button["href"].'"><i class="fa fa-'.$button["icon"].'"></i> &nbsp;'.$button["name"].'</a>';
			}
		}
		
		$return .= '
					<span class="hidden-xs">&nbsp;&nbsp;&nbsp;</span>
				</div>
			</div>
			</div>
		</div>
		';
		
		return $return;
	}
	
	public function createInput($data)
	{
		$return = '';
		if($data["type"] != "hidden" AND $data["type"] != "ln_solid")
		{
			$return .= '<div class="form-group';
			if(isset($data["class-form-group"])) { $return .= ' '.$data["class-form-group"]; }
			$return .= '"';
			if(isset($data["id-form-group"])) { $return .= ' "'.$data["id-form-group"].'"'; }
			$return .= '>';
			
			if(isset($data["input"]["required"]) AND $data["input"]["required"]) { $star = '<span class="required">*</span>'; }
			else { $star = ''; }
			
			$return .= '<label class="control-label col-md-3 col-sm-3 col-xs-12">'.$data["control-label"].$star.':</label>';
			
			if(isset($data["full-width"]) AND $data["full-width"]) { $return .= '<div class="col-xs-12 height-10"></div><div class="col-xs-12">'; }
			else { $return .= '<div class="col-md-9 col-sm-9 col-xs-12">'; }
		}
		
		switch($data["type"])
		{
			case "ln_solid":
				$return .= '<div class="ln_solid"></div>';
				break;
				
			case "hidden":
				$return .= '<input type="hidden" name="'.$data["input"]["name"].'" value="'.$data["input"]["value"].'">';
				break;
				
			case "text":
			case "number":
			case "date":
			case "email":
			case "password":
				if(isset($data["input-group-addon"]) AND !empty($data["input-group-addon"])) { $return .= '<div class="input-group">'; }
				
				if(isset($data["input-group-addon"]) AND !empty($data["input-group-addon"]) AND $data["input-group-addon-position"] == "left") { $return .= '<span class="input-group-addon">'.$data["input-group-addon"].'</span>'; }
				
				$return .= '<input type="'.$data["type"].'" name="'.$data["input"]["name"].'"';
				$return .= ' class="form-control';
				if(isset($data["class"])) { $return .= ' '.$data["class"]; }	
				$return .= '"';
				
				if(isset($data["input"]["value"])) { $return .= ' value="'.$data["input"]["value"].'"'; }	
				if(isset($data["input"]["min"])) { $return .= ' min="'.$data["input"]["min"].'"'; }
				if(isset($data["input"]["max"])) { $return .= ' max="'.$data["input"]["max"].'"'; }
				if(isset($data["input"]["maxlength"])) { $return .= ' maxlength="'.$data["input"]["maxlength"].'"'; }
				if(isset($data["input"]["step"])) { $return .= ' step="'.$data["input"]["step"].'"'; }
				if(isset($data["input"]["pattern"])) { $return .= ' pattern="'.$data["input"]["pattern"].'"'; }
				
				if(isset($data["input"]["placeholder"])) { $return .= ' placeholder="'.$data["input"]["placeholder"].'"'; }
				if(isset($data["input"]["required"]) AND $data["input"]["required"]) { $return .= ' required'; }
				if(isset($data["input"]["readonly"]) AND $data["input"]["readonly"]) { $return .= ' readonly'; }
				if(isset($data["input"]["disabled"]) AND $data["input"]["disabled"]) { $return .= ' disabled'; }
				if(isset($data["input"]["autofocus"]) AND $data["input"]["autofocus"]) { $return .= ' autofocus'; }
				
				if(isset($data["input"]["onclick"])) { $return .= ' onclick="'.$data["input"]["onclick"].'"'; }
				if(isset($data["input"]["onmouseenter"])) { $return .= ' onmouseenter="'.$data["input"]["onmouseenter"].'"'; }
				if(isset($data["input"]["onmouseover"])) { $return .= ' onmouseover="'.$data["input"]["onmouseover"].'"'; }
				if(isset($data["input"]["onmousemove"])) { $return .= ' onmousemove="'.$data["input"]["onmousemove"].'"'; }
				if(isset($data["input"]["onmouseout"])) { $return .= ' onmouseout="'.$data["input"]["onmouseout"].'"'; }
				if(isset($data["input"]["onchange"])) { $return .= ' onchange="'.$data["input"]["onchange"].'"'; }
				
				if(isset($data["id"])) { $return .= ' id="'.$data["id"].'"'; }	
				
				$return .= '>';
				
				if(isset($data["input-group-addon"]) AND !empty($data["input-group-addon"]) AND $data["input-group-addon-position"] != "left") { $return .= '<span class="input-group-addon">'.$data["input-group-addon"].'</span>'; }
				
				if(isset($data["input-group-addon"]) AND !empty($data["input-group-addon"])) { $return .= '</div>'; }
				
				break;
				
			case "textarea":
				$return .= '<'.$data["type"].' name="'.$data["input"]["name"].'"';
				$return .= ' class="form-control';
				if(isset($data["class"])) { $return .= ' '.$data["class"]; }	
				$return .= '"';
				
					
				if(isset($data["input"]["maxlength"])) { $return .= ' maxlength="'.$data["input"]["maxlength"].'"'; }
				if(isset($data["input"]["rows"])) { $return .= ' rows="'.$data["input"]["rows"].'"'; }
				if(isset($data["input"]["cols"])) { $return .= ' cols="'.$data["input"]["cols"].'"'; }
				
				if(isset($data["input"]["placeholder"])) { $return .= ' placeholder="'.$data["input"]["placeholder"].'"'; }
				if(isset($data["input"]["required"]) AND $data["input"]["required"]) { $return .= ' required'; }
				if(isset($data["input"]["readonly"]) AND $data["input"]["readonly"]) { $return .= ' readonly'; }
				if(isset($data["input"]["disabled"]) AND $data["input"]["disabled"]) { $return .= ' disabled'; }
				if(isset($data["input"]["autofocus"]) AND $data["input"]["autofocus"]) { $return .= ' autofocus'; }
				
				if(isset($data["input"]["onclick"])) { $return .= ' onclick="'.$data["input"]["onclick"].'"'; }
				if(isset($data["input"]["onmouseenter"])) { $return .= ' onmouseenter="'.$data["input"]["onmouseenter"].'"'; }
				if(isset($data["input"]["onmouseover"])) { $return .= ' onmouseover="'.$data["input"]["onmouseover"].'"'; }
				if(isset($data["input"]["onmousemove"])) { $return .= ' onmousemove="'.$data["input"]["onmousemove"].'"'; }
				if(isset($data["input"]["onmouseout"])) { $return .= ' onmouseout="'.$data["input"]["onmouseout"].'"'; }
				if(isset($data["input"]["onchange"])) { $return .= ' onchange="'.$data["input"]["onchange"].'"'; }
				
				if(isset($data["id"])) { $return .= ' id="'.$data["id"].'"'; }	
				
				$return .= '>';
				if(isset($data["input"]["value"])) { $return .= $data["input"]["value"]; }
				$return .= '</'.$data["type"].'>';
				break;	
				
			case "select":
				$return .= '<'.$data["type"].' name="'.$data["input"]["name"].'"';
				$return .= ' class="form-control';
				if(isset($data["class"])) { $return .= ' '.$data["class"]; }	
				$return .= '"';

				if(isset($data["input"]["required"]) AND $data["input"]["required"]) { $return .= ' required'; }
				if(isset($data["input"]["readonly"]) AND $data["input"]["readonly"]) { $return .= ' readonly'; }
				if(isset($data["input"]["disabled"]) AND $data["input"]["disabled"]) { $return .= ' disabled'; }
				if(isset($data["input"]["autofocus"]) AND $data["input"]["autofocus"]) { $return .= ' autofocus'; }
				if(isset($data["id"])) { $return .= ' id="'.$data["id"].'"'; }	
				
				if(isset($data["input"]["onclick"])) { $return .= ' onclick="'.$data["input"]["onclick"].'"'; }
				if(isset($data["input"]["onmouseenter"])) { $return .= ' onmouseenter="'.$data["input"]["onmouseenter"].'"'; }
				if(isset($data["input"]["onmouseover"])) { $return .= ' onmouseover="'.$data["input"]["onmouseover"].'"'; }
				if(isset($data["input"]["onmousemove"])) { $return .= ' onmousemove="'.$data["input"]["onmousemove"].'"'; }
				if(isset($data["input"]["onmouseout"])) { $return .= ' onmouseout="'.$data["input"]["onmouseout"].'"'; }
				if(isset($data["input"]["onchange"])) { $return .= ' onchange="'.$data["input"]["onchange"].'"'; }
				
				$return .= '>';
				
				if(isset($data["select-options"]))
				{
					foreach($data["select-options"] AS $val => $name)
					{
						$return .= '<option value="'.$val.'"';
						if(isset($data["input"]["value"]) AND $data["input"]["value"] == $val) { $return .= ' selected'; }
						$return .= '>'.$name.'</option>';
					}
				}
				
				$return .= '</'.$data["type"].'>';
				
				break;		
	
			case "checkbox":
				$return .= '
				<div class="checkbox-switchery-container">
					<label>
						<input type="'.$data["type"].'" name="'.$data["input"]["name"].'" class="js-switch
						';

				if(isset($data["class"])) { $return .= ' '.$data["class"]; }
				$return .= '"';
				
				if(isset($data["input"]["value"])) { $return .= ' value="'.$data["input"]["value"].'"'; }
				if(isset($data["input"]["checked"]) AND $data["input"]["checked"]) { $return .= ' checked'; }	

				if(isset($data["input"]["onclick"])) { $return .= ' onclick="'.$data["input"]["onclick"].'"'; }
				if(isset($data["input"]["onchange"])) { $return .= ' onchange="'.$data["input"]["onchange"].'"'; }				

				$return .= '		
						>
					</label>
				</div>
				';	
				break;
				
			case "checkbox-list":
				$return .= '<div class="my-checkbox-container">';
				
				if(isset($data["checkboxes"]))
				{
					if(isset($data["class"])) { $class = ' '.$data["class"]; } else { $class = ''; }
					foreach($data["checkboxes"] AS $val => $chbData)
					{
						if($chbData["checked"]) { $checked = ' checked'; } else { $checked = ''; }
						$return .= '
							<div class="my-checkbox'.$class.'">
								<label><input type="checkbox" name="'.$data["input"]["name"].'[]" value="'.$chbData["value"].'"'.$checked .'><span>'.$chbData["name"].'</span></label>
								<div class="clear"></div>
							</div>
						';
					}
				}
				$return .= '</div>';
				
				break;		
				
			case "file":
				if(!empty($data["file"]["id"]) AND !empty($data["file"]["row"]["file"]) AND isset($data["file"]["row"]["path"]["inner"]) AND !empty($data["file"]["row"]["path"]["inner"]) AND file_exists($data["file"]["row"]["path"]["inner"]))
				{
					$return .= '
					<a class="file-link btn btn-sm btn-info font-bold fancybox" href="'.$data["file"]["row"]["path"]["web"].'" data-fancybox-title="'.$data["file"]["row"]["fullName"].'" data-fancybox-group="single-picture">Megtekintés</a>
					<a class="file-link btn btn-sm btn-dark font-bold" href="'.$GLOBALS["URL"]->link(["file", "download", $data["file"]["id"]], ["from" => $data["file"]["from"]]).'">Letöltés</a>
					<button type="button" class="file-link btn btn-sm btn-danger font-bold" data-toggle="modal" data-target="#modal-file-del-'.$data["file"]["id"].'">Törlés</button>
					<div class="modal fade" id="modal-file-del-'.$data["file"]["id"].'" tabindex="-1" role="dialog" aria-labelledby="modal-file-del-'.$data["file"]["id"].'" aria-hidden="true">
						<div class="modal-dialog">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
									<h4 class="modal-title" id="myModalLabel-file-del-'.$data["file"]["id"].'">Feltöltött kép törlése</h4>
								</div>
								<div class="modal-body">
									Biztosan szeretné törölni a <strong>feltöltött képet</strong>?
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-default" data-dismiss="modal">Nem</button>
									<a href="'.$GLOBALS["URL"]->link(["file", "del", $data["file"]["id"]], ["from" => $data["file"]["from"]]).'" class="inline-block"><button type="button" class="btn btn-primary">Igen</button></a>
								</div>
							</div>
						</div>
					</div>
					';
				}
				else
				{
					$return .= '
					<div class="file-input-container">
						<div class="btn btn-primary file-button"><span class="fa fa-upload"></span></div>
						<input type="text" class="form-control file-text" name="'.$data["file"]["name"].'-text" id="'.$data["file"]["name"].'-text">
						<div class="clear"></div>
						<input type="file" class="form-control file-input" name="'.$data["file"]["name"].'" onchange="document.getElementById(\''.$data["file"]["name"].'-text\').value = this.value;">
					</div>
					';
				}
				break;	
			case "files":
				$return .= '
				<div class="file-input-container">
					<div class="btn btn-primary file-button"><span class="fa fa-upload"></span></div>
					<input type="text" class="form-control file-text" name="'.$data["file"]["name"].'-text" id="'.$data["file"]["name"].'-text">
					<div class="clear"></div>
					<input type="file" class="form-control file-input" name="'.$data["file"]["name"].'[]" multiple onchange="document.getElementById(\''.$data["file"]["name"].'-text\').value = this.value;">
				</div>
				';
				break;		
		}
		
		if($data["type"] != "hidden" AND $data["type"] != "ln_solid") 
		{ 
			if(isset($data["helpText"]) AND !empty($data["helpText"])) { $return .= '<p class="help-block">'.$data["helpText"].'</p>'; }
			$return .= '
				</div>
			</div>
			'; 
		}
		
		return $return;
	}
}
?>