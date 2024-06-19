<?php

require(__DIR__."/simulator.php");
if($_SERVER['REQUEST_METHOD'] === "OPTIONS") {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: X-Requested-With, Origin, X-Csrftoken, Content-Type, Accept");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, CONNECT, OPTIONS, TRACE, PATCH, HEAD");
    exit;
}

$body = file_get_contents("php://input");
$json = json_decode($body, true);
$options = $json["data"] ?? [];

$x = new Simulator(
    isCli: (php_sapi_name() === "cli"),
);
$result = [];

foreach($options as $option){
    $name=$option["name"];
    $result[] = $x->cal(
        json: true,
        name: $option["name"],
        asset:$option["asset_start"],
        start_year: $option["start_year"],
        stop_age: intval($option["end_age"]),
        pay_per_year: $option["pay_per_month"]*12,
        withdraw_per_month:$option["withdraw"],
        year_change_rate:$option["year_change_rate"],
        option: $option,
    );
}

$x->dump_sum_draw();

if(php_sapi_name() !== "cli") {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: X-Requested-With,Authorization,Content-Type');
    header("Content-type: application/json");

    echo json_encode($result);
}
