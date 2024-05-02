<?php

require("utils.php");

function cal(
    $r,
    $name = "",
    $paid_sum = 0,
    $asset = 0,
    $range = 200,
    $withdraw_per_month = 0,
    $pay_per_year = 0,
    $start_year = 0,
    $stop_age = 3000
) {
    global $sum_draw_per_year;

    echo "[$name]\n";
    $current_year = intval(date("Y"));
    echo "age year total_pay asset benefit\n";
    $sum_withdraw = 0;

    for($i = 0;$i < $range;$i++) {
        $age = $i + $start_year - 1973;
        $sep = ($i + $start_year === $current_year) ? "+++ now" : "";
        if(($i - 1) % 5 === 0) {
            $sep = $age."歳";
        }

        if($age === $stop_age) {
            $sep = "{$age}歳 *** 払い終了 pay:({$withdraw_per_month}/month)";
        }

        if($age > $stop_age) {
            $pay_per_year = 0;
        }

        $paid_sum += $pay_per_year;

        if($age > $stop_age) {
            $asset -= $withdraw_per_month * 12;
            if(!isset($sum_draw_per_year[$age])) {
                $sum_draw_per_year[$age] = 0;
            }
            $sum_draw_per_year[$age] += $withdraw_per_month * 12;
            $sum_withdraw += $withdraw_per_month * 12;
        } else {
            $asset = $asset + $pay_per_year;
        }

        $asset *= change_rate_simulation($age, $name);

        // cal tax
        // 購入額を上回るまでは非課税
        $benefit = $asset - $paid_sum;
        if($sum_withdraw < $paid_sum) {
            $kazei = 0;
        } else {
            $_kazei = $sum_withdraw - $paid_sum;
            $kazei = $_kazei < $withdraw_per_month * 12 ? $sum_withdraw - $paid_sum : $withdraw_per_month * 12 ;
        }
        // $tax=shotokuzei($kazei);
        // $tedori=$withdraw_per_month*12-$tax; // 総合課税なので計算しない

        if($asset < 0) {
            $sep = "DEAD";
        }
        echo sprintf("%3s ", $age).($start_year + $i)." ",(int)$paid_sum." ".(int)$asset." ".(int)$benefit." $sep\n";
        if($asset < 0) {
            break;
        }
        if($age > 150) {
            break;
        }
    }
}

function change_rate_simulation($age, $name)
{
    global $rate_sony,$rate_rakuten;

    if($name === "ソニー") {
        return $age > 65 ? 1.08 : $rate_sony;
    }

    if($name === "楽天") {
        return $age > 65 ? 1.06 : $rate_rakuten;
    }
}

function dump_sum_draw($sum_draw_per_year)
{
    ksort($sum_draw_per_year);
    echo "age pay/year\n";
    foreach($sum_draw_per_year as $y => $money) {

        if($y >= 65) {
            $money += 9;
        } // 年金

        $tax = shotokuzei($money);
        $tedori = $money - $tax;

        echo sprintf("%3d:%4d [tax:%3d][%4d]\n", $y, $money, $tax, $tedori / 12);
    }
}

$shortopts = "";
$shortopts .= 's:';//:は値を必須で受け取る
$shortopts .= 'r:';//:は値を必須で受け取る
$shortopts .= 'S:';//:は値を必須で受け取る
$shortopts .= 'R:';//:は値を必須で受け取る
$shortopts .= 'd:';//:は値を必須で受け取る
$shortopts .= 'y:';//:は値を必須で受け取る

$options = getopt($shortopts);
if(empty($options)) {
    fputs(STDERR, "There was a problem reading in the options.\n" . print_r($argv, true));

    $output = <<<'EOL'
-h [--help]  helpこのコマンド
-s value ソニー利回り(eg: 1.11)
-r value 楽天利回り(eg: 1.11)
-d value 楽天年間積立(eg: 200)
-S value ソニー月取り崩し(eg: 20)
-R value 楽天ー月取り崩し(eg: 58)

EOL;
    echo $output;
    exit(1);
}

$rate_rakuten = (float)($options["r"] ?? 1.1);
$rate_sony = (float)($options["s"] ?? 1.1);
$deposit_rakuten = (float)($options["d"] ?? 250);
$stop_year = (int)($options["y"] ?? 57);

$withdraw_rakuten = (float)($options["R"] ?? 50);
$withdraw_sony = (float)($options["S"] ?? 28);

$paid_sum = 0;

$sum_draw_per_year = [];

cal(
    $rate_sony,
    name: "ソニー",
    asset:0,
    pay_per_year: 18.66,
    start_year: 2007,
    stop_age: 65,
    withdraw_per_month: $withdraw_sony
);


cal(
    $rate_rakuten,
    paid_sum:1400,
    name: "楽天",
    asset:2400,
    start_year:2024,
    stop_age: $stop_year,
    pay_per_year: $deposit_rakuten,
    withdraw_per_month: $withdraw_rakuten
);

dump_sum_draw($sum_draw_per_year);
echo "{$rate_rakuten} {$rate_sony}",PHP_EOL;
