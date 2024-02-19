<?php
require(__DIR__."/S.php");
$x=new S();

$body=file_get_contents("php://input");
$json=json_decode($body,true);
error_log(print_r($json,true));

$options=$json["data"] ?? [];

error_log(print_r($options,true));

$rate_rakuten=(float)($options["r"] ?? 1.1);
$rate_sony=(float)($options["s"] ?? 1.1);
$deposit_rakuten=(float)($options["d"] ?? 250);
$stop_year=(int)($options["year"] ?? 57);

$withdraw_rakuten=(float)($options["R"] ?? 50);
$withdraw_sony=(float)($options["S"] ?? 28);
$rate_later=$options["rate_later"] ?? [
    "sony"=>1.06,
    "rakuten"=>1.06,
];
$year_change_rate=$options["year_change_rate"] ?? 0;

$sum_draw_per_year=[];
$x=new S(isCli: (php_sapi_name() === "cli"),
         rate_sony:$rate_sony,
         rate_rakuten:$rate_rakuten,
         rate_later:$rate_later,
         year_change_rate:$year_change_rate
);
$result=[];
$result["sony"]=$x->cal(
    json: true,
    name: "ソニー",
    asset:0,
    pay_per_year: 18.66,
    start_year: 2007,
    stop_age: 65,
    withdraw_per_month: $withdraw_sony
);


$result["rakuten"]=$x->cal(
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

if(php_sapi_name() !== "cli"){
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: X-Requested-With,Authorization,Content-Type');
    header("Content-type: application/json");

    echo json_encode($result);
}
