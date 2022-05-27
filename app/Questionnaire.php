<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Base;

class Questionnaire extends Base
{
	public $fromWhereQuestion = "Kérjük jelölje meg, honnan értesült aktuális ajánlatunkról, készletünkről, kereskedésünkről!";
	public $fromWhereQuestionPlaceholder = "Kérem válasszon!";
	public $fromWhereTextQuestion = "Amennyiben egyéb?";
	public $fromWhereTextQuestionPlaceholder = "Ide írhatja válaszát.";
	
	#Tables
	public function tables($name = "")
	{
		$return = [
			"questionnaires" => $this->dbPrefix."questionnaires",
			"answers" => $this->dbPrefix."questionnaires_answers",
			"answerComments" => $this->dbPrefix."questionnaires_answers_comments",
			"inputWatches" => $this->dbPrefix."questionnaires_inputWatches",
			"inputTypes" => $this->dbPrefix."questionnaires_inputTypes",
			"questions" => $this->dbPrefix."questionnaires_questions",
			"types" => $this->dbPrefix."questionnaires_types",
			"log" => $this->dbPrefix."questionnaires_log",
			"logTypes" => $this->dbPrefix."questionnaires_logTypes",
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#From where
	public function fromWheres($name = "")
	{
		$return = [
			"facebook-hirdetes" => ["name" => "Facebook hirdetés", "active" => 1],
			"google-hirdetes" => ["name" => "Google hirdetés", "active" => 1],
			"youtube-csatorna" => ["name" => "Youtube csatorna", "active" => 1],
			"edm" => ["name" => "Hírlevél/Edm", "active" => 1],
			"radio-egyeb" => ["name" => "Rádió egyéb", "active" => 1],
			"sajto-hirdetes" => ["name" => "Sajtó hirdetés", "active" => 1],
			"oriasplakat" => ["name" => "Óriásplakát", "active" => 1],
			"ajanlas" => ["name" => "Ajánlás", "active" => 1],
			"regi-ugyfel" => ["name" => "Régi/Törzs ügyfél", "active" => 1],
			"lakohely-kozel" => ["name" => "Lakóhelye közelében található", "active" => 1],
			"itt-vasarolt" => ["name" => "Itt vásárolta az autóját", "active" => 1],
			"automento" => ["name" => "Autómentő szállította be", "active" => 1],
			"sajat-munkatars" => ["name" => "Saját munkatárs", "active" => 1],
            "munkatars" => ["name" => "Munkatársunkat ismeri/Munkatársunk ajánlotta", "active" => 1],
            "ismeri" => ["name" => "Ismeri a cégünket", "active" => 1],
            "aruhazi-kiallitas" => ["name" => "Áruházi kiállítás", "active" => 1],
            "rendezveny" => ["name" => "Rendezvény", "active" => 1],
            "fotaxi" => ["name" => "Főtaxi hirdetés", "active" => 1],
            "egyeb" => ["name" => "Egyéb", "active" => 1],
		];

		if(empty($name)) { return $return; }
		else { return $return[$name]; }
	}
	
	#Get questionnaire
	public function getQuestionnaire($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("questionnaires"), "id", $id, $field, $delCheck);
	}
	
	public function getQuestionnaireByCode($code, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("questionnaires"), "code", $code, $field, $delCheck);
	}
	
	public function getQuestionnaireByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("questionnaires"), "url", $url, $field, $delCheck);
	}
	
	#Get question
	public function getQuestion($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("questions"), "id", $id, $field, $delCheck);
	}
	
	public function getQuestions($questionnaire, $deleted = 0, $selectFields = "*", $orderBy = "orderNumber")
	{
		$params = ["questionnaire" => $questionnaire];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("questions")." WHERE questionnaire = :questionnaire";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}		
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get answer
	public function getAnswer($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("answers"), "id", $id, $field, $delCheck);
	}
	
	public function getAnswerByIdentifiers($questionnaire, $customer, $foreignKey = NULL, $user = NULL, $field = "", $deleted = NULL)
	{
		if(empty($field)) { $selectFields = "*"; }
		else { $selectFields = $field; }
		
		$query = "SELECT ".$selectFields." FROM ".$this->tables("answers")." WHERE questionnaire = :questionnaire AND customer = :customer";
		$params = [
			"questionnaire" => $questionnaire, 
			"customer" => $customer, 
		];
		if($foreignKey !== NULL)
		{
			$query .= " AND foreignKey = :foreignKey";
			$params["foreignKey"] = $foreignKey;
		}
		if($user !== NULL)
		{
			$query .= " AND user = :user";
			$params["user"] = $user;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		$query .= " ORDER BY date DESC";
		
		$rows = $this->select($query, $params);
		if(count($rows) > 0) { $returnRow = $rows[0]; }
		else { $returnRow = new \stdClass(); }
		
		if(!empty($field)) { $return = $returnRow->$field; }
		else { $return = $returnRow; }
		
		return $return;
	}
	
	public function getAnswers($questionnaire = NULL, $customer = NULL, $foreignKey = NULL, $user = NULL, $deleted = NULL, $selectFields = "*", $orderBy = "customer, date DESC", $limit = NULL)
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("answers")." WHERE id != '0'";
		$params = [];
		if($questionnaire !== NULL)
		{
			$query .= " AND questionnaire = :questionnaire";
			$params["questionnaire"] = $questionnaire;
		}
		if($customer !== NULL)
		{
			$query .= " AND customer = :customer";
			$params["customer"] = $customer;
		}
		if($foreignKey !== NULL)
		{
			$query .= " AND foreignKey = :foreignKey";
			$params["foreignKey"] = $foreignKey;
		}
		if($user !== NULL)
		{
			$query .= " AND user = :user";
			$params["user"] = $user;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		if(!empty($limit)) { $query .= " LIMIT ".$limit; }
		
		return $this->select($query, $params);
	}
	
	#Get input type
	public function getInputType($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("inputTypes"), "id", $id, $field, $delCheck);
	}
	
	public function getInputTypeByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("inputTypes"), "url", $url, $field, $delCheck);
	}
	
	public function getInputTypes($selectFields = "*", $deleted = NULL, $orderBy = "orderNumber")
	{
		$params = [];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("inputTypes")." WHERE id != '0'";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Get type
	public function getType($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("types"), "id", $id, $field, $delCheck);
	}
	
	public function getTypeByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("types"), "url", $url, $field, $delCheck);
	}
	
	public function getTypes($selectFields = "*", $deleted = NULL, $orderBy = "orderNumber")
	{
		$params = [];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("types")." WHERE id != '0'";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#Log - Get type
	public function getLogType($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("logTypes"), "id", $id, $field, $delCheck);
	}
	
	public function getLogTypeByURL($url, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("logTypes"), "url", $url, $field, $delCheck);
	}
	
	#Log
	public function log($typeName, $questionnaire, $customer, $foreignKey, $datas = [], $user = NULL)
	{
		$return = [
			"type" => "error",
			"info" => NULL,
			"id" => NULL,
		];
		
		$type = $this->getLogTypeByURL($typeName);
		if(isset($type->id) AND !empty($type->id))
		{
			if($type->active)
			{				
				#Basic datas				
				$params = [
					"ip" => $_SERVER["REMOTE_ADDR"],
					"browser" => $_SERVER["HTTP_USER_AGENT"],
					"referer" => $_SERVER["HTTP_REFERER"],
					"deviceType" => DEVICE_TYPE,
					"session" => session_id(), // \Session::getId()
					"type" => $type->id,
					"date" => date("Y-m-d H:i:s"),
					"questionnaire" => $questionnaire,
					"customer" => $customer,
					"foreignKey" => $foreignKey,
					"user" => $user,
				];
				
				#Dinamic datas
				if(!empty($datas))
				{
					if(isset($datas["requestedURL"])) { $params["requestedURL"] = $datas["requestedURL"]; }
					if(isset($datas["error"])) { $params["error"] = $datas["error"]; }
					if(isset($datas["json"])) { $params["json"] = $this->json($datas["json"]); }
					
					if(isset($datas["userImportant"])) { $params["user"] = $datas["userImportant"]; }
					if(isset($datas["dateImportant"])) { $params["date"] = $datas["dateImportant"]; }
				}
				
				#Insert and return
				$return["id"] = $this->myInsert($this->tables("log"), $params);
				$return["type"] = "success";
			}
			else { $return["info"] = "inactive-type"; }
		}
		else { $return["info"] = "unknown-type"; }
		
		return $return;
	}
	
	#Log - Get Row
	public function getLog($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("log"), "id", $id, $field, $delCheck);
	}
	
	public function getLogsByQuestionnaire($questionnaire, $selectFields = "*", $deleted = 0, $orderBy = "date DESC")
	{
		$params = ["questionnaire" => $questionnaire];
		$query = "SELECT ".$selectFields." FROM ".$this->tables("log")." WHERE questionnaire = :questionnaire";
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		$query .= " ORDER BY ".$orderBy;
		
		return $this->select($query, $params);
	}
	
	#Get input watches
	public function getInputWatch($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("inputWatches"), "id", $id, $field, $delCheck);
	}
	
	public function getInputWatchByQuestion($question, $field = "", $delCheck = 1)
	{
		return $this->selectByField($this->tables("inputWatches"), "question", $question, $field, $delCheck);
	}
	
	#Get answer comments
	public function getAnswerComment($id, $field = "", $delCheck = 0)
	{
		return $this->selectByField($this->tables("answerComments"), "id", $id, $field, $delCheck);
	}
	
	public function getAnswerComments($answer = NULL, $user = NULL, $deleted = NULL, $selectFields = "*", $orderBy = "date DESC")
	{
		$query = "SELECT ".$selectFields." FROM ".$this->tables("answerComments")." WHERE id != '0'";
		$params = [];
		if($answer !== NULL)
		{
			$query .= " AND answer = :answer";
			$params["answer"] = $answer;
		}
		if($user !== NULL)
		{
			$query .= " AND user = :user";
			$params["user"] = $user;
		}
		if($deleted !== NULL)
		{
			$query .= " AND del = :del";
			$params["del"] = $deleted;
		}
		
		if(!empty($orderBy)) { $query .= " ORDER BY ".$orderBy; }
		
		return $this->select($query, $params);
	}
	
	#JSON encode and decode
	public function json($array)
	{
		return json_encode($array, JSON_UNESCAPED_UNICODE);
	}
	
	public function jsonDecode($string)
	{
		return json_decode($string, JSON_UNESCAPED_UNICODE);
	}
}
