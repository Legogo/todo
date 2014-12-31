<?php

	//########## SET GLOBAL VARIABLES
	
	define("TDL_VERSION", "0.4");
	define("TDL_FILEPATH", ""); // default is empty for next to index.php
	
	//########## FUNCTIONS
	
	function generateListsOptions($defaultListName){
		
		$projs = getListsNames();
		
		//echo "ListS ".print_r($projs);
		
		foreach($projs as $p){
			echo "<option value=\"".$p."\" ";
			if(!strcmp($p, $defaultListName))	echo "SELECTED";
			echo ">".$p."</option>";
		}

	}
	
	/* get file at path.name and return parsed as XML */
	function getXML($filename){
		
		$xmlFilePath = TDL_FILEPATH.$filename;
		// echo "Opening ".$xmlFilePath;
		
		if(!file_exists($xmlFilePath)){
			//Si le fichier existe pas !!
			echo "ACCESS >> File does not exist (".$filename." / ".$xmlFilePath.")";
			//createMissingXml($xmlFileName);
			//redirect();
		}else{
			//Si le fichier existe déjà
			//echo $xmlFileName;
			$xml = simplexml_load_file($xmlFilePath); //This line will load the XML file.
			// print_r($xml);
			//print_r($xml);
			$sxe = new SimpleXMLElement($xml->asXML()); //In this line it create a SimpleXMLElement object with the source of the XML file. 
			// print_r($sxe);
			return $sxe;
		}
		
		die("<br /><br /> --- <br />ACCESS >> Error loading xml for filename : ".$filename);
		
		return null;
	}
	
	function getFilesList(){
		$files = scandir(TDL_FILEPATH);
		$names = [];
		foreach($files as $f){
			$infos = pathinfo($f);
			$names[] = $infos["basename"];
		}
		return $names;
	}

	function save($name, $sxe){
		$sxe->asXML(TDL_FILEPATH.getListFilePath($name));
	}
	
	function getLastId($name){
		$sxe = getListXML($name);
		//echo count($sxe->children());
		
		//choppe le dernier
		if(count($sxe) > 0){
			foreach ($sxe->children() as $elmt) {}
			return $elmt["id"];
		}
		
		return -1;
	}
	
	function addTask($name, $title, $content){
		
		//récup l'objet xml
		$sxe = getListXML($name);
		
		//$count = count($sxe);
		$count = getLastId($name)+1;
		echo "Count ".$count;
		
		//The following lines will add a new child and others child inside the previous child created. 
		$task = $sxe->addChild("task");
		$task->addAttribute("id", $count);
		$task->addAttribute("state", "0");
		$task->addChild("title", treatToXml($title));
		$task->addChild("content", treatToXml($content));
		$task->addChild("added", date("Y-m-d h:i:s")); 
		save($name, $sxe);
		
		return 1;
	}
	
	function removeTask($name, $id){
		echo "Removing #".$id." in List : ".$name;
		
		$sxe = getListXML($name);
		$found = false;
		
		foreach ($sxe->children() as $elmt) {
			if($elmt["id"] == $id){
				$dom = dom_import_simplexml($elmt);
				$dom->parentNode->removeChild($dom);
				$found = true;
				save($name, $sxe);
			}
		}
		
		if(!$found)	echo "ERROR no id match for #".$id;
		else echo "Done";
		
		return $found;
	}
	
	function setState($name, $id, $state){
		$sxe = getListXML($name);
		foreach ($sxe->children() as $elmt) {
			if($elmt["id"] == $id){
				$elmt["state"] = $state;
				save($name, $sxe);
				return 1;
			}	
		}
		
		echo "ERROR no id match";
		return 0;
	}
	
	function treatToXml($str){
		$str = str_replace('\\', '', $str);
		return $str;
	}
	
	function treatOutXml($str){
		//vire les \
		$str = str_replace('\\', '', $str);
		return $str;
	}
	
	function order($in){
		$xml = array();
		
		$todo = array();
		$wait = array();
		$complete = array();
		$depre = array();
		
		//in array
		foreach ($in->children() as $elmt) {
			switch($elmt["state"]){
				case 0 : array_push($todo, $elmt);break;
				case 1 : array_push($wait, $elmt);break;
				case 2 : array_push($complete, $elmt);break;
				case 3 : array_push($depre, $elmt);break;
			}
		}
		/*
		for($i = count($todo); $i > 0; $i--){
			$row = $todo[$i];
			//print_r($row);
			array_push($xml, $row);
		}*/
		
		foreach($todo as $row)	array_push($xml, $row);
		foreach($wait as $row)	array_push($xml, $row);
		foreach($complete as $row)	array_push($xml, $row);
		foreach($depre as $row)	array_push($xml, $row);
		
		return $xml;
	}
	
	function listAllOfList($name){
		$sxe = getListXML($name);
		
		$xml = order($sxe);
		
		foreach ($xml as $elmt) {
			echo "<div class=\"list_line\" >";
				
				$class = "";
				switch($elmt["state"]){
					case 0 : $class = "todo";break;
					case 1 : $class = "wait";break;
					case 2 : $class = "complete";break;
					case 3 : $class = "deprecated";break;
				}
				
				echo "<div class=\"list_gt\"></div>";
				echo "<div class=\"list_state ".$class."\" >".strtoupper($class)."</div>";
				
				echo "<div class=\"list_title ".$class."\" >".treatOutXml($elmt->title)."</div>";
				echo "<div class=\"list_content\" >".treatOutXml("".$elmt->content)."</div>";
				
				echo "<div class=\"list_info\" >".getTheDate($elmt->added)." ".getTime($elmt->added)."</div>";
				echo "<div class=\"list_admin\" >";
					
					if($elmt["state"] != 2)	echo "<a href=\"?action=com&proj=".$name."&id=".$elmt["id"]."&state=2\">done</a> - ";
					if($elmt["state"] != 0) echo "<a href=\"?action=com&proj=".$name."&id=".$elmt["id"]."&state=0\">todo</a> - ";
					if($elmt["state"] != 1)	echo "<a href=\"?action=com&proj=".$name."&id=".$elmt["id"]."&state=1\">wait</a> - ";
					if($elmt["state"] != 3)	echo "<a href=\"?action=com&proj=".$name."&id=".$elmt["id"]."&state=3\">depr</a> - ";
					
					echo "<a href=\"index.php?action=rem&proj=".$name."&id=".$elmt["id"]."\">del</a>";
				echo "</div>";
				
				echo "<div class=\"clear\"></div>";
			echo "</div>";
		}
	}
	
?>

<?php

	// ===== TREAT SENDING

	if (isset($_POST["action"]) || isset($_GET["action"])) {
			
		$todo = "";
		
		$projName = "";
		if(isset($_GET["proj"]))	$projName = $_GET["proj"];
		else if(isset($_POST["proj"]))	$projName = $_POST["proj"];
		
		if(!empty($_POST["action"])){
			$todo = $_POST["action"];
		}else if(!empty($_GET["action"])){
			$todo = $_GET["action"];
			
			if(isset($_GET["id"]))	$id = $_GET["id"];
			if(isset($_GET["state"]))	$state = $_GET["state"];
		}

		echo "TODO > ".$todo;
		
		switch($todo){
			case "add" :
				echo ">> Adding task to List : ".$projName." <br />";
				if(addTask($projName, $_POST["title"], $_POST["content"]))	redirect("proj=".$projName);
				break;
			case "rem" :
				echo ">> Removing entry #".$id."<br />";
				if(removeTask($projName, $id))	redirect("proj=".$projName);
				break;
			case "com" :
				echo ">> Setting state:".$state." to entry #".$id;
				if(setState($projName, $id, $state))	redirect("proj=".$projName);
				break;
			case "switch" :
				echo ">> SWITCH to ".$_POST["List"];
				//redirect("proj=".$_POST["List"]);
				break;
			default : 
				echo ">> ERROR >>> no action";
				break;
		}
		
		
		echo "<br/><br/><a href=\"index.php?proj=$projName\">Back</a>";
		redirect();
		
		die();
	}
	
?>

<html>

	<head>
		<title>Todo list (<?php echo TDL_VERSION; ?>)</title>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js" type="text/javascript"></script>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<meta name="robots" content="noindex">
			
		<script type="text/javascript">
			$(function(){
				$("#switch").change(function(){
					var $select = $(this);
					window.location = "index.php?proj="+$select.val();
				});
			});
		</script>
		
		<style>
			body{
				color:#000;
				font-family:"Courier";
				font-size:0.8em;
			}

			hr{
				width:800px;
				color:#000;
				margin:50px 0px;
				text-align:center;
			}

			input{
				font-family:"Courier";
			}

			.title{
				font-size:2em;
				padding-bottom:10px;
			}

			.list_title, .list_content, .list_info, .list_admin{
				float:left;
				padding:0px 20px 0px 20px;
			}

			.list_state{
				padding-left:10px;
				float:left;
				width:70px;
			}

			.list_line{
				padding:0px 0px 10px 40px;
			}
			.list_line:hover{
				background-color:#CCC;
			}

			.list_title{
				width:150px;
			}

			.list_content{
				color:#000;
				width:500px;
				font-size:0.9em;
			}

			.list_gt{
				float:left;
			}

			.list_info{
				text-align:left;
				min-width:100px;
				font-size:0.8em;
			}

			.list_admin{
				width:200px;
				font-size:.8em;
			}

			.todo{
				color:#F00;
			}
			.wait{
				color:#F70;
			}
			.complete{
				color:#0F0;
			}
			.deprecated{
				color:#111;
			}

			.form{
				margin-bottom:100px;
			}

			.form_label{
				color:#F00;
			}

			.form_line{
				color:#000;
			}

			.line_label, .line_input{
				float:left;
			}

			.line_label{
				color:#000;
				text-align:right;
				min-width:100px;
				padding-right:20px;
			}

			.line_input{
				color:#00F;
			}

			.clear{
				clear:both;
			}
		</style>
		
	</head>

	<body>
		
		<?php
			$ListName = "";
			if(isset($_GET["listId"]))	$ListName = $_GET["listId"];
			else {
				$names = getListsNameList();
				$ListName = $names[0];
			}
		?>
		
		<!-- List SWITCHER ! -->
		<div class="form_line" method="POST" action="?action=switch">
			<div class="line_label">Switch to List</div>
			<div class="line_input">
				<!-- onChange="switchList(document.getElementById('switch').value);" -->
				<select name="proj" id="switch">
					<?php generateListsOptions($ListName); ?>
				</select>
			</div>
			<div class="clear"></div>
		</div>

		<div class="title">LIST OF <?php echo strtoupper($ListName); ?></div>


		<?php

			// LIST DISPLAY

			if(strlen($ListName) > 1){
				listAllOfList($ListName);
			}
		?>

		<hr />

		<div class="form_label">Add new list</div>
		<div class="form">
			<form method="POST" action="index.php">
				<input name="action" value="newList" type="hidden" />
				<input name="title" value="type list name here" />
				<input type="submit" value="Create new list" />
			</form>
		</div>

		<div class="form_label">Add new task</div>

		<form method="POST" action="index.php">
		<input name="action" value="add" type="hidden" />

		<input name="list" value="<?php echo $ListName; ?>" type="hidden" />

		<div class="form">
			<div class="form_label">Add new task</div>
			
			<div class="form_line">
				<div class="line_label">List</div>
				<div class="line_input">
					<select name="proj" id="add">
						<?php generateListsOptions($ListName); ?>
					</select>
				</div>
				<div class="clear"></div>
			</div>
			
			<div class="form_line">
				<div class="line_label">CATEGORY</div>
				<div class="line_input"><input size="30" name="title" /></div>
				<div class="clear"></div>
			</div>

			<div class="form_line">
				<div class="line_label">CONTENT</div>
				<div class="line_input"><input size="30" name="content" /></div>
				<div class="clear"></div>
			</div>
			
			<div class="form_line">
				<div class="line_input"><input type="submit" value="Send" /></div>
			</div>

		</div>

		</form>
	</body>

</html>


<?php
	
	/* WEB TOOLS */

	function redirect($args = ""){
		echo "<script>";
			if (strlen($args) > 0)	echo "window.location = \"index.php?".$args."\"";
			else echo "window.location = \"index.php?\"";
		echo "</script>";
	}

?>


<?php

	/* TIME/DATE RELATED TOOLS */
	
	function getTime($stamp, $seconds = false){
		$result = explode(" ",$stamp);
		if($seconds)	return $result[1];
		$time = explode(":", $result[1]);
		return $time[0].":".$time[1];
	}
	
	function getTheDate($stamp){
		$result = explode(" ",$stamp);
		return $result[0];
	}
	
	function splitDate($stamp, $cat){
		
		$result = explode(" ",$stamp);
		$date = explode("-", $result[0]);
		$time = explode(":", $result[1]);
		
		switch($cat){
			case "h" : return $time[0];break;
			case "min" : return $time[1];break;
			case "s" : return $time[2];break;
			case "y" :  return $date[0];break;
			case "month" : return $date[1];break;
			case "d" : return $date[2];break;
		}
		
		return "";
	}
?>