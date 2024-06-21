<?php

function eecho($s)
{
    if(php_sapi_name() === "cli") {
        echo $s;
    }
}

class Simulator
{

    public function __construct(
        $bone_at
    ) {
        error_log($this->bone_at=$bone_at);
    }

    public function cal(
        $json = false,
        $name = "",
        $paid_sum = 0,
        $asset = 0,
        $range = 200, // ありえない値
        $withdraw_per_month = 0,
        $pay_per_year = 0,
        $start_year = 0,
        $stop_age = 99999, // ありえない値
        $year_change_rate=57,
        $option=[],
    ) {
        $this->sum_draw_per_year = [];
        $this->year_change_rate = $year_change_rate;
        $asset = intval($asset);
        $result = [
            "data" => []
        ];
        $result["name"] = $name;
        $current_year = intval(date("Y"));
        eecho("age year total_pay asset benefit\n");
        $sum_withdraw = 0;

        for($i = 0;$i < $range;$i++) {
            $age = $i + $start_year - $this->bone_at;
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

            // 取り崩し計算
            if($age > $stop_age) {
                $withdraw_per_year=$withdraw_per_month * 12;
                $asset -= $withdraw_per_year;
                if(!isset($this->sum_draw_per_year[$age])) {
                    $this->sum_draw_per_year[$age] = 0;
                }
                $this->sum_draw_per_year[$age] += $withdraw_per_year;
                $sum_withdraw += $withdraw_per_year;
            } else {
                $asset = $asset + $pay_per_year;
            }
            $asset *= $this->change_rate_simulation($age, $name,$option);

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
                $asset = 0;
            }
            $result["data"][] = [
                "age" => $age,
                "year" => $start_year + $i,
                "total_pay" => (int)$paid_sum,
                "asset" => (int)$asset,
                "benefit" => (int)$benefit,
                "sep" => $sep,
            ];
            if($asset <= 0) {

                break;
            }
            if($age > 150) {
                break;
            }
        }

        return $result;
    }

    public function shotokuzei($s)
    {
        if($s < 195 * 10000) {
            return $s * 0.05;
        }
        if($s < 330 * 10000) {
            return $s * 0.1 - 97500;
        }
        if($s < 695 * 10000) {
            return $s * 0.2 - 427500;
        }
        if($s < 900 * 10000) {
            return $s * 0.23 - 636000;
        }
        if($s < 1800 * 10000) {
            return $s * 0.33 - 1536000;
        }
    }

    public function change_rate_simulation($age, $name, $option)
    {
        $rate= $age > $this->year_change_rate ? $option["rate2"] :$option["rate1"];
        return 1+(float)$rate /100;
    }

    public function dump_sum_draw()
    {
        ksort($this->sum_draw_per_year);
        eecho("age pay/year\n");
        foreach($this->sum_draw_per_year as $y => $money) {

            if($y >= 65) {
                $money += 9;
            } // 年金

            $tax = $this->shotokuzei($money);
            $tedori = $money - $tax;

            eecho(sprintf("%3d:%4d [tax:%3d][%4d]\n", $y, $money, $tax, $tedori / 12));
        }
    }
}

if(php_sapi_name() === "cli") {
    error_log("CLICLI");
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
}

//error_log(print_r($_SERVER,true));
