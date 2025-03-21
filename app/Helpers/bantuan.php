<?php

if (!function_exists('rupiah')) {
    function rupiah($nominal) {
        return "Rp " . number_format($nominal, 0, ',', '.');
    }
}
