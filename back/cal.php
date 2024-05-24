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

error_log(print_r($options, true));

$rate_rakuten = 1 + (float)($options["r"] ?? 10) / 100;
$rate_sony = 1 + (float)($options["s"] ?? 10) / 100;

$deposit_rakuten = (float)($options["d"] ?? 250);
$stop_year = (int)($options["year"] ?? 57);

$withdraw_rakuten = (float)($options["R"] ?? 50);
$withdraw_sony = (float)($options["S"] ?? 28);

$rate_later = [
    "sony" => 1 + (float)$options["rate_later"]["sony"] / 100,
    "rakuten" => 1 + (float)$options["rate_later"]["rakuten"] / 100
];
error_log(print_r($rate_later, true));

$year_change_rate = $options["year_change_rate"] ?? 0;

$sum_draw_per_year = [];
$x = new Simulator(
    isCli: (php_sapi_name() === "cli"),
    rate_sony:$rate_sony,
    rate_rakuten:$rate_rakuten,
    rate_later:$rate_later,
    year_change_rate:$year_change_rate
);
$result = [];
$result["sony"] = $x->cal(
    json: true,
    name: "ソニー",
    asset:0,
    pay_per_year: 18.66,
    start_year: 2007,
    stop_age: 65,
    withdraw_per_month: $withdraw_sony
);


$result["rakuten"] = $x->cal(
    json: true,
    paid_sum:1400,
    name: "楽天",
    asset:2400,
    start_year:2024,
    stop_age: $stop_year,
    pay_per_year: $deposit_rakuten,
    withdraw_per_month: $withdraw_rakuten
);

$x->dump_sum_draw();

if(php_sapi_name() !== "cli") {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: X-Requested-With,Authorization,Content-Type');
    header("Content-type: application/json");

    echo json_encode($result);
}
