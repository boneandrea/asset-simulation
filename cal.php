<?php

require(__DIR__."/S.php");

$x=new S();

$rate_rakuten=(float)($options["r"] ?? 1.1);
$rate_sony=(float)($options["s"] ?? 1.1);
$deposit_rakuten=(float)($options["d"] ?? 250);
$stop_year=(int)($options["y"] ?? 57);

$withdraw_rakuten=(float)($options["R"] ?? 50);
$withdraw_sony=(float)($options["S"] ?? 28);

$paid_sum=0;

$sum_draw_per_year=[];
$x=new S(isCli: (php_sapi_name() === "cli"));
$result=[];
$result["sony"]=$x->cal(
    $rate_sony,
    json: true,
    name: "ソニー",
    asset:0,
    pay_per_year: 18.66,
    start_year: 2007,
    stop_age: 65,
    withdraw_per_month: $withdraw_sony
);


$result["rakuten"]=$x->cal(
    $rate_rakuten,
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

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With,Authorization,Content-Type');
header("Content-type: application/json");
echo json_encode($result);
