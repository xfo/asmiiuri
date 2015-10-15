<?php
header('Content-Type: text/html; charset=utf-8');

ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");

function ArrToJson($str){
	$str = json_encode($str);
	//$str = preg_replace_callback('/\\\u([a-f0-9]{4})/i', create_function('$m', 'return chr(hexdec($m[1])-1072+224);'), $str);
	return $str;//iconv('cp1251', 'utf-8', $str);
}

function SQLSelect($sql){
	$host='localhost';
	$database='cp571579_asmiiuri';
	$user='cp571579_ramil';
	$pswd=']2I.2qUs~tz}';
	$dbh = mysql_connect($host, $user, $pswd) or die("no connect");
	mysql_set_charset('utf8',$dbh);
	mysql_select_db($database) or die("error");
	$query = $sql;
	$res = mysql_query($query);
	while($row = mysql_fetch_array($res)){
		$result[] = $row;
	}
	return $result;
}

function SQLInsert($sql){
	$host='localhost';
	$database='cp571579_asmiiuri';
	$user='cp571579_ramil';
	$pswd=']2I.2qUs~tz}';
	$dbh = mysql_connect($host, $user, $pswd) or die("no connect");
	//mysql_set_charset('utf8',$dbh);
	mysql_select_db($database) or die("error");
	$query = $sql;
	$res = mysql_query($query);
	echo (json_encode($res));
	return $res;
}

function SQLMultiquery($sql){
	$mysqli = new mysqli('localhost', 'cp571579_ramil', ']2I.2qUs~tz}', 'cp571579_asmiiuri');
	mysqli_set_charset($mysqli, "utf8");
	/* проверка соединения */
	if (mysqli_connect_errno()) {
		printf("Не удалось подключиться: %s\n", mysqli_connect_error());
		exit();
	}
	$query  = $sql;
	/* запускаем мультизапрос */
	if ($mysqli->multi_query($query)) {
		do {
			/* получаем первый результирующий набор */
			if ($result = $mysqli->store_result()) {
				while ($row = $result->fetch_row()) {
					//Отладка
					printf("%s\n", $row[0]);
				}
				$result->free();
			}
			/* печатаем разделитель */
			if ($mysqli->more_results()) {
				//Отладка
				printf("-----------------\n");
			}
		} while ($mysqli->next_result());
	}
	var_dump($mysqli);
	$mysqli->close();
}
class Instrument{
	private $all_info_to_add_new = array();

	function GetAllTypes(){
		return SQLSelect("SELECT * FROM `tool_types`");
	}

	function GetAllManufacturers(){
		return SQLSelect("SELECT * FROM `manufacturers`");
	}

	function GetAllSuppliers(){
		return SQLSelect("SELECT * FROM `suppliers`");
	}

	function GetAllInformationToAddNew(){
		$all_info_to_add_new["types"] = $this->GetAllTypes();
		$all_info_to_add_new["manufacturers"] = $this->GetAllManufacturers();
		$all_info_to_add_new["suppliers"] = $this->GetAllSuppliers();
		return $all_info_to_add_new;
	}
	function AddNewInstrument(){
		$instrument_name = $_POST["instrument_name"];
		$tool_type_id = $_POST["instrument_type"];
		$material = $_POST["instrument_material"];
		$manufacturer_id = $_POST["instrument_manufacturer"];
		$supplier_id = $_POST["instrument_supplier"];
		$holding_method = $_POST["holding_method"];
		$shape_type = $_POST["shape_type"];
		$holder_type = $_POST["holder_type"];
		$back_angle = $_POST["back_angle"];
		$feed_direction = $_POST["feed_direction"];
		$holder_heigth = $_POST["holder_heigth"];
		$holder_width = $_POST["holder_width"];
		$tool_length = $_POST["tool_length"];
		$blade_edge_length = $_POST["blade_edge_length"];
		$max_back_edge_wear = $_POST["max_back_edge_wear"];
		$max_front_edge_wear_hl = $_POST["max_front_edge_wear_hl"];
		$max_front_edge_wear_ll = $_POST["max_front_edge_wear_ll"];
		$max_radius_edge_wear = $_POST["max_radius_edge_wear"];
		$max_length_wear = $_POST["max_length_wear"];

		$timestamp = new DateTime();
		$timestamp = $timestamp->getTimestamp();

		return SQLMultiquery("
			INSERT INTO  `tools` (
			`name`,
			`tool_type_id`,
			`material`,
			`manufacturer_id`,
			`supplier_id`,
			`holding_method`,
			`shape_type`,
			`holder_type`,
			`back_angle`,
			`feed_direction`,
			`holder_heigth`,
			`holder_width`,
			`tool_length`,
			`blade_edge_length`,
			`max_back_edge_wear`,
			`max_front_edge_wear_hl`,
			`max_front_edge_wear_ll`,
			`max_radius_edge_wear`,
			`max_length_wear`
			)
			VALUES (
			'".$instrument_name."',
			'".$tool_type_id."',
			'".$material."',
			'".$manufacturer_id."',
			'".$supplier_id."',
			'".$holding_method."',
			'".$shape_type."',
			'".$holder_type."',
			'".$back_angle."',
			'".$feed_direction."',
			'".$holder_heigth."',
			'".$holder_width."',
			'".$tool_length."',
			'".$blade_edge_length."',
			'".$max_back_edge_wear."',
			'".$max_front_edge_wear_hl."',
			'".$max_front_edge_wear_ll."',
			'".$max_radius_edge_wear."',
			'".$max_length_wear."'
			);
			SET @lastID1 := LAST_INSERT_ID();
			INSERT INTO
			`revisions`(
				`timestamp`,
				`user_id`,
				`box_stock_num`,
				`tool_id`
			)
			VALUES (
				".$timestamp.",
				1,
				0,
				@lastID1
			);
			SET @lastID := LAST_INSERT_ID();
			INSERT INTO
			`parameters_values`(
				`parameter_value`,
				`revision_id`,
				`tool_parameter_id`
			)
			VALUES (
				0,
				@lastID,
				1
			);
		");
	}
	function GetAllTools(){
		$raw_all_tools = SQLSelect("SELECT tools.tool_id, tools.name, tools.material, suppliers.name supplier_name , manufacturers.name manufacturer_name, tool_types.name tool_type_name
FROM tools
LEFT OUTER JOIN suppliers ON tools.supplier_id=suppliers.supplier_id
LEFT OUTER JOIN manufacturers ON tools.manufacturer_id=manufacturers.manufacturer_id
LEFT OUTER JOIN tool_types ON tools.tool_type_id=tool_types.tool_type_id");
		$i=0;
		foreach($raw_all_tools as $tool){
			$raw_all_tools[$i]["resource"] = $this->GetToolResource($tool["tool_id"]);
			$i++;
		}
		return $raw_all_tools;
	}
	function GetOneTool(){
		$tool_id = $_GET["tool_id"];
		return SQLSelect("
		SELECT
			tools.tool_id,
			tools.name,
			tools.material,
			tools.holding_method,
			tools.shape_type,
			tools.holder_type,
			tools.back_angle,
			tools.feed_direction,
			tools.holder_heigth,
			tools.holder_width,
			tools.tool_length,
			tools.blade_edge_length,
			tools.max_back_edge_wear,
			tools.max_front_edge_wear_hl,
			tools.max_front_edge_wear_ll,
			tools.max_radius_edge_wear,
			tools.max_length_wear,
			suppliers.name supplier_name,
			manufacturers.name manufacturer_name,
			tool_types.name tool_type_name
		FROM tools
		LEFT OUTER JOIN suppliers ON tools.supplier_id=suppliers.supplier_id
		LEFT OUTER JOIN manufacturers ON tools.manufacturer_id=manufacturers.manufacturer_id
		LEFT OUTER JOIN tool_types ON tools.tool_type_id=tool_types.tool_type_id
		WHERE tools.tool_id = ".$tool_id);
	}
	function AddNewRevision($tool_id,$box_stock_num,$parameter_value){
		$timestamp = new DateTime();
		$timestamp = $timestamp->getTimestamp();
		return SQLMultiquery(
			"INSERT INTO
				`revisions`(
				`timestamp`,
				`user_id`,
				`box_stock_num`,
				`tool_id`
			)
			VALUES (
				".$timestamp.",
				1,
				".$box_stock_num.",
				".$tool_id."
			);
			SET @lastID := LAST_INSERT_ID();
			INSERT INTO
				`parameters_values`(
				`parameter_value`,
				`revision_id`,
				`tool_parameter_id`
			)
			VALUES (
				".$parameter_value.",
				@lastID,
				1
			);
		");
	}
	function ShowAllRevisions(){
		$tool_id = $_GET["tool_id"];
		return SQLSelect("
			SELECT
				revisions.revision_id,
				revisions.timestamp,
				revisions.user_id,
				revisions.box_stock_num,
				revisions.tool_id,
				parameters_values.parameter_value_id,
				parameters_values.parameter_value
			FROM  `revisions`
			LEFT OUTER JOIN parameters_values ON revisions.revision_id=parameters_values.revision_id
			WHERE tool_id =".$tool_id);
	}
	function GetToolWearHistory(){
		$tool_id = $_GET["tool_id"];
		$tool_parameter_id = $_GET["tool_parameter_id"];
		$tool_wear_history = array("dates"=>array(), "values"=>array());
		$tool_wear_history1 = SQLSelect(
		"
		select
			revisions.timestamp,
			parameters_values.parameter_value
		from revisions
		LEFT OUTER JOIN
			parameters_values ON revisions.revision_id=parameters_values.revision_id
		where tool_id = ".$tool_id." and parameters_values.tool_parameter_id = ".$tool_parameter_id."
		order by revisions.timestamp
		");
		$max_tool_wear_value = SQLSelect(
		"
		select max_length_wear
		from tools
		where tool_id = ".$tool_id.";
		");
		$max_tool_wear_value = (double)$max_tool_wear_value*1000;
		foreach($tool_wear_history1 as $item){
			$current_value = (double)$item["parameter_value"];
			$tool_resource = (($current_value*100/$max_tool_wear_value));
			$tool_wear_history["dates"][] = date("d.m.Y H:i:s",$item["timestamp"]);
			$tool_wear_history["values"][] = (double)$tool_resource;
		}
		return $tool_wear_history;
	}
	function GetToolResource($tool_id){
		$last_tool_revision_id = SQLSelect(
		"
		select
		revision_id
		from revisions
		where tool_id = ".$tool_id."
		ORDER BY revision_id DESC LIMIT 1;
		");
		$last_tool_wear_value = SQLSelect(
		"
		select
		parameter_value
		from parameters_values
		where tool_parameter_id = 1 and revision_id = ".$last_tool_revision_id[0][0].";
		");
		$max_tool_wear_value = SQLSelect(
		"
		select max_length_wear
		from tools
		where tool_id = ".$tool_id.";
		");
		$last_tool_wear_value = $last_tool_wear_value[0][0];
		$max_tool_wear_value = ($max_tool_wear_value[0][0])*1000;
		$tool_resource = round((100-($last_tool_wear_value*100/$max_tool_wear_value)));
		$progress_bar_status = "success";
		if($tool_resource<50){
			$progress_bar_status = "warning";
		};
		if($tool_resource<20){
			$progress_bar_status = "danger";
		};
		return array("resource"=>$tool_resource, "status"=>$progress_bar_status);
	}
	function GetToolWorkHistory(){
		$tool_id = $_GET["tool_id"];
		$raw_tool_work_history = SQLSelect(
			"
			SELECT
			tools.name,
			tools_works.timestamp,
			tools_works.S,
			tools_works.R
			FROM
			tools
			LEFT OUTER JOIN
			tools_works
			ON
			tools.tool_id = tools_works.tool_statement_id
			WHERE
			tool_id = ".$tool_id." and tools_works.tool_statement_id = ".$tool_id."
			ORDER BY tools_works.timestamp ASC;
			"
		);
		$sum = 0;
		foreach($raw_tool_work_history as $item){
			$R = (double)$item["R"];
			$S = (double)$item["S"];
			$sum += $R*$S;
			$tool_work_history["work"][] = array(
				"timestamp"=> (double)$item["timestamp"],
				"work"=> $R*$S,
				"work_sum"=> $sum
			);
			$tool_work_history["chart"]["dates"][] = date("d.m.Y H:i:s",$item["timestamp"]);
			$tool_work_history["chart"]["values"][] = $sum;
		}
		$tool_work_history["name"] = $raw_tool_work_history[0]["name"];
		$tool_work_history["summary"] = $sum;
		return $tool_work_history;
	}

	function AddNewToolWork(){
		$timestamp = new DateTime();
		$timestamp = $timestamp->getTimestamp();
		$tool_id = $_GET["tool_id"];
		$s = $_POST["s"];
		$r = $_POST["r"];
		$t = 700;
		$v = 1000;

		return SQLMultiquery(
			"INSERT INTO
				`tools_works`
			(
				`timestamp`,
				`S`,
				`R`,
				`T`,
				`V`,
				`tool_statement_id`
			)
			VALUES (
				".$timestamp.",
				".$s.",
				".$r.",
				".$t.",
				".$v.",
				".$tool_id."
			)");
	}

	function GetToolWorkWearRelation(){
		$tool_id = $_GET["tool_id"];
		//Получаем данные о работе инструмента
		$raw_tool_work_history = SQLSelect(
			"
			SELECT
			tools.name,
			tools_works.timestamp,
			tools_works.S,
			tools_works.R
			FROM
			tools
			LEFT OUTER JOIN
			tools_works
			ON
			tools.tool_id = tools_works.tool_statement_id
			WHERE
			tool_id = ".$tool_id." and tools_works.tool_statement_id = ".$tool_id."
			ORDER BY tools_works.timestamp ASC;
			"
		);

		$sum = 0;
		foreach($raw_tool_work_history as $item){
			$R = (double)$item["R"];
			$S = (double)$item["S"];
			$sum += $R*$S;
			$tool_work_history["timestamp"][] = $item["timestamp"];
			$tool_work_history["work"][] = $sum;
		}
		$tool_name = $raw_tool_work_history[0]["name"];
		//Получаем данные о хронологии износа инструмента
		$tool_wear_history = array("timestamp"=>array(), "wear"=>array());
		$tool_wear_history1 = SQLSelect(
		"
		select
			revisions.timestamp,
			parameters_values.parameter_value
		from revisions
		LEFT OUTER JOIN
			parameters_values ON revisions.revision_id=parameters_values.revision_id
		where tool_id = ".$tool_id." and parameters_values.tool_parameter_id = 1
		order by revisions.timestamp
		");

		$max_tool_wear_value = SQLSelect(
		"
		select max_length_wear
		from tools
		where tool_id = ".$tool_id.";
		");

		$max_tool_wear_value = (double)$max_tool_wear_value*1000;
		foreach($tool_wear_history1 as $item){
			$current_value = (double)$item["parameter_value"];
			$tool_resource = (($current_value*100/$max_tool_wear_value));
			$tool_wear_history["timestamp"][] = $item["timestamp"];
			$tool_wear_history["wear"][] = (double)$tool_resource;
		}

		//В этом массиве объединим все значения износа и работы, отсортируем по дате и удалим ненужное.
		$twwr = array();
		for ($i = 0; $i < count($tool_wear_history["timestamp"]); $i++) {
			$twwr[] = array(
				"timestamp"=>$tool_wear_history["timestamp"][$i],
				"wear"=>$tool_wear_history["wear"][$i],
				"work"=>0,
				"flag"=>true
			);
		}
		for ($i = 0; $i < count($tool_work_history["timestamp"]); $i++) {
			$twwr[] = array(
				"timestamp"=>$tool_work_history["timestamp"][$i],
				"wear"=>0,
				"work"=>$tool_work_history["work"][$i],
				"flag"=>false
			);
		}

		function cmp($a, $b)
		{
			return strnatcmp($a["timestamp"], $b["timestamp"]);
		}
		usort($twwr, "cmp");

		$i = 0;
		foreach($twwr as $row){
			if($row["flag"] && $i>1){
				$twwr[$i]["work"] = $twwr[$i-1]["work"];
			}
			$i++;
		}
		foreach($twwr as $row){
			if($row["flag"]){
				$wear_work_relation["wear"][] = $row["wear"];
				$wear_work_relation["work"][] = $row["work"];
			}
		}
		$wear_work_relation["name"] = $tool_name;
		return $wear_work_relation;
	}

}
