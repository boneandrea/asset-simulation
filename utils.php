<?php

function shotokuzei($s)
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
