<?php

function cal(
             $r,
             $name="",
             $paid_sum=0,
             $asset=0,
             $range=200,
             $withdraw_per_month=0,
             $pay_per_year=0,
             $start_year=0,
             $stop_age=3000){
    global $sum_draw_per_year;

    echo "[$name]\n";
    $current_year=intval(date("Y"));
    echo "age year total_pay asset benefit\n";
    $sum_withdraw=0;

    for($i=0;$i<$range;$i++){
        $age=$i+$start_year-1973;
        $sep=($i+$start_year === $current_year) ? "+++ now" : "";
        if(($i-1)%5===0){ $sep=$age."歳"; }

        if($age===$stop_age){ $sep="{$age}歳 *** 払い終了 pay:({$withdraw_per_month}/month)"; }

        if($age > $stop_age){
            $pay_per_year=0;
        }

        $paid_sum+=$pay_per_year;

        if($age > $stop_age){
            $asset-=$withdraw_per_month*12;
            if(!isset($sum_draw_per_year[$age])){
                $sum_draw_per_year[$age]=0;
            }
            $sum_draw_per_year[$age]+=$withdraw_per_month*12;
            $sum_withdraw+=$withdraw_per_month*12;
        }else{
            $asset=$asset+$pay_per_year;
        }

        $asset*=change_rate_simulation($age, $name);

        // cal tax
        $benefit=$asset-$paid_sum;
        if($sum_withdraw < $paid_sum){
            $kazei=0;
        }else{
            $_kazei=$sum_withdraw - $paid_sum;
            $kazei= $_kazei < $withdraw_per_month*12 ? $sum_withdraw - $paid_sum : $withdraw_per_month*12 ;
        }
        // $tax=shotokuzei($kazei);
        // $tedori=$withdraw_per_month*12-$tax; // 総合課税なので計算しない

        if($asset < 0){
            $sep="DEAD";
        }
        echo sprintf("%3s ", $age).($start_year+$i)." ",(int)$paid_sum." ".(int)$asset." ".(int)$benefit." $sep\n";
        if($asset < 0){break;}
        if($age > 150){break;}
    }
}

function shotokuzei($s){
    if($s<195*10000){
        return $s*0.05;
    }
    if($s<330*10000){
        return $s*0.1-97500;
    }
    if($s<695*10000){
        return $s*0.2-427500;
    }
    if($s<900*10000){
        return $s*0.23-636000;
    }
    if($s<1800*10000){
        return $s*0.33-1536000;
    }
}

function change_rate_simulation($age, $name){
    global $r;

    if($name==="ソニー"){
        if($age > 65) return 1.08;
        return $r;
    }

    if($name==="楽天"){
        if($age > 65) return 1.06;
        return 1.11;
    }
}

function dump_sum_draw($sum_draw_per_year){
    ksort($sum_draw_per_year);
    echo "age pay/year\n";
    foreach($sum_draw_per_year as $y=>$money){

        if($y >= 65)
            $money+=9; // 年金

        $tax=shotokuzei($money);
        $tedori=$money-$tax;

        echo sprintf("%3d:%4d [tax:%3d][%4d]\n", $y,$money,$tax, $tedori/12);
    }
}

$r=$argv[1] ?? 1.1;
$paid_sum=0;

$sum_draw_per_year=[];

cal(
    $r,
    name: "ソニー",
    asset:0,
    pay_per_year: 18.66,
    start_year: 2007,
    stop_age: 65,
    withdraw_per_month: 28
);


cal(
    $r,
    paid_sum:1400,
    name: "楽天",
    asset:2400,
    start_year:2024,
    stop_age: 57,
    pay_per_year: 250,
    withdraw_per_month:50
);

dump_sum_draw($sum_draw_per_year);
echo "[[[ $r ]]]\n";
