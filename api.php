<?php
include_once 'classes.php';

$instrument = new Instrument();

switch ($_GET["action"]){
    case "GetAllInstrumentTypes":
		echo(ArrToJson($instrument->GetAllTypes()));
    break;
    case "GetAllInstrumentManufacturers":
		echo(ArrToJson($instrument->GetAllManufacturers()));
    break;
    case "GetAllInstrumentSuppliers":
		echo(ArrToJson($instrument->GetAllSuppliers()));
    break;
    case "GetAllInformationToAddNew":
		echo(ArrToJson($instrument->GetAllInformationToAddNew()));
    break;
    case "AddNewInstrument":
		$instrument->AddNewInstrument();
    break;
    case "GetAllTools":
		echo(ArrToJson($instrument->GetAllTools()));
    break;
    case "GetOneTool":
		echo(ArrToJson($instrument->GetOneTool()));
    break;
    case "AddNewRevision":
		echo(ArrToJson($instrument->AddNewRevision($_GET["tool_id"],$_POST["box_stock_num"],$_POST["parameter_value"])));
    break;
    case "ShowAllRevisions":
		echo(ArrToJson($instrument->ShowAllRevisions()));
    break;
    case "GetToolWearHistory":
		echo(ArrToJson($instrument->GetToolWearHistory()));
    break;
    case "GetToolResource":
		echo(ArrToJson($instrument->GetToolResource($_GET["tool_id"])));
    break;
    case "GetToolWorkHistory":
		echo(ArrToJson($instrument->GetToolWorkHistory()));
    break;
    case "AddNewToolWork":
		echo(ArrToJson($instrument->AddNewToolWork()));
    break;
    case "GetToolWorkWearRelation":
		echo(ArrToJson($instrument->GetToolWorkWearRelation()));
    break;
}
?>
