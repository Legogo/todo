<?php
	/*
		made by : andreberlemont.com
		version 0.4 on 2015-01-01
	*/





	//########## SET GLOBAL VARIABLES
	
	define("TDL_FILEPATH", "./"); // default is empty for next to index.php
	





	// ===== POST/GET FORM ACTION TREATMENT

	if (isset($_POST["action"]) || isset($_GET["action"])) {
		
		print_r($_POST);

		$action = "";
		
		$listName = "";
		if(isset($_GET["list"]))	$listName = $_GET["list"];
		else if(isset($_POST["list"]))	$listName = $_POST["list"];
		
		if(!empty($_POST["action"])){
			$action = $_POST["action"];
		}else if(!empty($_GET["action"])){
			$action = $_GET["action"];
			
			if(isset($_GET["id"]))	$id = $_GET["id"];
			if(isset($_GET["state"]))	$state = $_GET["state"];
		}

		if(strlen($action) <= 0) die("No action given by html form");
		if(strlen($listName) <= 0) die("No list name given by html form");

		echo "<br/>ACTION > ".$action;
		
		switch($action){
			case "add" :
				echo "> Adding task to List : ".$listName." <br />";
				$content = $_POST["content"];
				$cat = $_POST["title"];
				if(strlen($content) > 0 && strlen($cat) > 0){
					if(list_addTask($listName, $cat, $content))	redirectToIndex("listId=".$listName);
				}
				break;
			case "rem" :
				echo "> Removing entry #".$id."<br />";
				if(list_removeTask($listName, $id))	redirectToIndex("listId=".$listName);
				break;
			case "com" :
				echo "> Setting state:".$state." to entry #".$id;
				if(list_setTaskState($listName, $id, $state))	redirectToIndex("listId=".$listName);
				break;
			case "switch" :
				echo "> SWITCH to ".$listName;
				break;
			case "new" :
				$label = $_POST["label"];
				if(strlen($label) <= 0) die("no label given");
				echo "> added file ".$listName.".xml with label ".$label;
				if(xml_new($listName, $label)) redirectToIndex("listId=".$listName);
				break;
			default : 
				echo '> ERROR > this action is not defined';
				break;
		}
		
		echo "<br/><br/><a href=\"index.php?list=$listName\">Back</a>";
		redirectToIndex();
		
		die();
	}
	
?>

<html>

	<head>
		<title>Todo list (0.4)</title>
		
		<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700' rel='stylesheet' type='text/css'>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js" type="text/javascript"></script>
		<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
		<meta name="robots" content="noindex">
		

		<script type="text/javascript">
			//on dropdown list menu change, change page
			$(function(){
				$("#switch").change(function(){
					var $select = $(this);
					window.location = "index.php?listId="+$select.val();
				});

				$("#form-new").hide();
				$("#btn-new").click(function(e){
					e.preventDefault();
					console.log("aa");
					$("#form-new").toggle(100);
				});
			});

		</script>
		
		<style>
			body{
				margin:0px;
				padding:0px;
				color:#000;
				font-family:"Open+Sans", "Courier";
				font-size:16px;
			}

			hr{
				width:100%;
				color:#000;
				margin:10px 0px;
				text-align:center;
			}
			a{ cursor:pointer;color:#333;}
			input{ margin:0px;padding:0px 5px;font-family:"Open Sans", "Courier"; }
			form{ margin:0px;}

			.clear{ clear:both; }
			.center{ margin: auto;}
			.float{ float:left;}

			#topbar{
				height:30px;
				line-height: 30px;
				background-color: #333;
				padding-left: 10px;
				color:#fefefe;
			}
			#topbar-select{
				padding-top:5px;
			}
			#topbar-select select{
				width:200px;
			}

			#addbar{
				overflow:hidden;
				padding-left:10px;
				height:30px;
				line-height: 30px;
				background-color: #333;
			}
			#addbar input{
				margin-top:3px;
				height:20px;
			}

			#add-input-new{ width:110px; }
			#add-input-cat{ width:180px; }
			#add-input-content{ width: 500px; }
			
			#btn-new{
				font-size:1.5em;
				margin-left:10px;
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
				width:100px;
			}

			.list_line{
				overflow:hidden;
				line-height: 30px;
				height:30px;
			}
			.list_line:hover{
				background-color:#333;
				color:#fefefe;
			}

			.list_title{
				width:150px;
			}

			.list_content{
				width: 50%;
				font-size:0.9em;
			}

			.list_info{
				text-align:left;
				font-size:0.6em;
			}

			.list_admin{
				width:200px;
				font-size:.6em;
			}

			.todo{ color:#F00; }
			.wait{ color:#F70; }
			.complete{ color:#0F0; }
			.deprecated{ color:#111; }

			#form-new{
				position:absolute;
				left:215px;
				top:36px;
				background-color: #fff;
				padding: 10px 10px;
				border: 1px solid;
    		border-radius: 3px;
    		color:#333;
    		text-align: center;
			}
			#btn-new-submit{
				margin:auto;
				width:150px;
				text-align: center;
				margin-top:10px;
			}

			.form_label{ 
				text-transform: uppercase;
				font-weight: bold;
				font-size: 1.2em;
			}

			.form_line{ color:#000; }

			.line_label, .line_input{ float:left; }

			.line_label{
				text-align:right;
				min-width:100px;
				padding-right:20px;
			}

			.line_input{ color:#00F; }

		</style>
		
	</head>

	<body>
		
		<?php
			$selection = "";
			if(isset($_GET["listId"]))	$selection = $_GET["listId"];
			else {
				$names = xml_getAllFiles();
				if(count($names) <= 0) echo "no xml files found";
				//print_r($names);
				$selection = $names[0];
			}

			//remove extension
			$selection = str_replace(".xml", "", $selection);

			if(strlen($selection) <= 0) die("selection is empty, should take first file as default");
		?>

		<div id="topbar">
			<div id="topbar-select" class="float">
				<form method="POST" action="?action=switch">
					<?php
						$lists = xml_getAllFiles();
				
						echo '<select id="switch" class="list">';
						foreach($lists as $p){
							$p = str_replace(".xml", "", $p); // remove extension
							echo "<option value=\"".$p."\" ";
							if(!strcmp($p, $selection))	echo "SELECTED";
							echo ">".list_getLabel($p)."</option>"; // display label
						}
						echo '</select>';
					?>
					
				</form>
			</div>
			<div id="topbar-add" class="float"><a id="btn-new">+</a></div>
			<div class="clear"></div>
		</div>
		
		<div id="form-new">

			<form method="POST" action="index.php">
				<input name="action" value="new" type="hidden" />
				<div class="form_line">
					<div class="line_label">File name</div>
					<div class="line_input"><input size="30" name="list" /></div>
					<div class="clear"></div>
				</div>
				<div class="form_line">
					<div class="line_label">List label</div>
					<div class="line_input"><input size="30" name="label" /></div>
					<div class="clear"></div>
				</div>
				
				<input id="btn-new-submit" type="submit" value="Create new list" />
				
			</form>
		</div>

		<?php
			if(strlen($selection) > 1){ xml_listToHtml($selection); }
		?>

		<div id="addbar">
			<div class="form">
				<form method="POST" action="index.php">
					<input name="action" value="add" type="hidden" />
					<input name="list" value="<?php echo $selection; ?>" type="hidden" />
					
					<input disabled id="add-input-new" value="NEW"/>
					<input size="23" id="add-input-cat" name="title" />
					<input size="40" id="add-input-content" name="content" />
					<input type="submit" value="ADD" />
				</form>
			</div>
		</div>

	</body>

</html>


<?php
	
	/* WEB TOOLS */

	function redirectToIndex($args = ""){
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


<?php
	
  // ############ [XML TOOLS]

	/* return list of all xml files names */
	function xml_getAllFiles(){
		$files = scandir(TDL_FILEPATH);
		$names = [];

		if(count($files) <= 0) echo "no xml files found";
		
		foreach($files as $f){
			if(is_dir($f)) continue;
			$infos = pathinfo($f);
			//print_r($infos);
			if($infos["extension"] != "xml") continue;
			$names[] = $infos["basename"];
		}
		return $names;
	}

	/* get file at path.name and return parsed as XML */
	function xml_getListAsXml($filename){
		if(strlen($filename) <= 0) die("xml_getListAsXml() :: no filename given");

		$xmlFilePath = TDL_FILEPATH.$filename.".xml";
		// echo "Opening ".$xmlFilePath;
		
		if(!file_exists($xmlFilePath)){
			//Si le fichier existe pas !!
			echo "xml_getListContent() >> File does not exist (path = '".$xmlFilePath."')";
			redirectToIndex("index.php");
			//createMissingXml($xmlFileName);
			//redirectToIndex();
		}else{
			//Si le fichier existe déjà
			//echo "[DEBUG]Opening : ".$xmlFilePath;
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
	
	function xml_save($name, $sxe){
		$sxe->asXML(TDL_FILEPATH.$name.".xml");
	}

	function xml_new($filename,$label){
		$sxe = new SimpleXMLElement('<data label="'.$label.'"></data>');
		xml_save($filename,$sxe);
		return true;
	}

	function xml_escapeIn($str){
		return $str;
	}
	function xml_escapeOut($str){
		return $str;
	}

	function xml_listToHtml($listName){
		$sxe = xml_getListAsXml($listName);
		
		$xml = list_order($sxe);
		
		foreach ($xml as $elmt) {
			echo "<div class=\"list_line\" >";
				
				$class = "";
				switch($elmt["state"]){
					case 0 : $class = "todo";break;
					case 1 : $class = "wait";break;
					case 2 : $class = "complete";break;
					case 3 : $class = "deprecated";break;
				}
				
				echo '<div class="list_state '.$class.'">'.strtoupper($class).'</div>';
				
				echo '<div class="list_title '.$class.'" >'.xml_escapeOut($elmt->title).'</div>';
				echo '<div class="list_content">'.xml_escapeOut("".$elmt->content).'</div>';
				
				echo "<div class=\"list_info\" >".getTheDate($elmt->added)." ".getTime($elmt->added)."</div>";
				echo '<div class="list_admin" >';
					
					if($elmt['state'] != 2)	echo '<a href="?action=com&list='.$listName.'&id='.$elmt['id'].'&state=2">DONE</a> | ';
					if($elmt['state'] != 0) echo '<a href="?action=com&list='.$listName.'&id='.$elmt['id'].'&state=0">TODO</a> | ';
					if($elmt['state'] != 1)	echo '<a href="?action=com&list='.$listName.'&id='.$elmt['id'].'&state=1">WAIT</a> | ';
					if($elmt['state'] != 3)	echo '<a href="?action=com&list='.$listName.'&id='.$elmt['id'].'&state=3">DEPR</a> | ';
					echo '<a href="index.php?action=rem&list='.$listName.'&id='.$elmt["id"].'">DEL</a>';

				echo "</div>";
				
				echo "<div class=\"clear\"></div>";
			echo "</div>";
		}
	}
	
	
?>

<?php
	
	/* ########## LIST API */

	function list_getLabel($filename){
		$sxe = xml_getListAsXml($filename);
		return $sxe["label"];
	}

	function list_getLastId($name){
		$sxe = xml_getListAsXml($name);
		//echo count($sxe->children());
		
		//choppe le dernier
		if(count($sxe) > 0){
			foreach ($sxe->children() as $elmt) {}
			return $elmt["id"];
		}
		
		return -1;
	}

	function list_addTask($name, $title, $content){
		
		//récup l'objet xml
		$sxe = xml_getListAsXml($name);
		
		//$count = count($sxe);
		$count = list_getLastId($name)+1;
		echo "Count ".$count;
		
		//The following lines will add a new child and others child inside the previous child created. 
		$task = $sxe->addChild("task");
		$task->addAttribute("id", $count);
		$task->addAttribute("state", "0");
		$task->addChild("title", xml_escapeIn($title));
		$task->addChild("content", xml_escapeIn($content));
		$task->addChild("added", date("Y-m-d h:i:s")); 
		xml_save($name, $sxe);
		
		return 1;
	}
	
	function list_removeTask($name, $id){
		echo "Removing #".$id." in List : ".$name;
		
		$sxe = xml_getListAsXml($name);
		$found = false;
		
		foreach ($sxe->children() as $elmt) {
			if($elmt["id"] == $id){
				$dom = dom_import_simplexml($elmt);
				$dom->parentNode->removeChild($dom);
				$found = true;
				xml_save($name, $sxe);
			}
		}
		
		if(!$found)	echo "ERROR no id match for #".$id;
		else echo "Done";
		
		return $found;
	}
	
	function list_setTaskState($listName, $taskId, $state){
		$sxe = xml_getListAsXml($listName);
		foreach ($sxe->children() as $elmt) {
			if($elmt["id"] == $taskId){
				$elmt["state"] = $state;
				xml_save($listName, $sxe);
				return 1;
			}	
		}
		
		echo "ERROR no id match";
		return 0;
	}
	
	/* to sort all tasks in an order based on state priority */
	function list_order($in){
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

		foreach($todo as $row)	array_push($xml, $row);
		foreach($wait as $row)	array_push($xml, $row);
		foreach($complete as $row)	array_push($xml, $row);
		foreach($depre as $row)	array_push($xml, $row);
		
		return $xml;
	}
	
?>
