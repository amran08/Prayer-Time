<?php
require_once __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Asia/Dhaka');
$resource = "All Timing 2023 V2.xlsx";
$jsonFileName = "test_prayer_time.json";
$jsonFileBnName = "test_prayer_time_bn.json";

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', -1);


$xlsx = SimpleXLSX::parse($resource);
$sheetNames = $xlsx->sheetNames();

$dd = [];
$i = 0;


unset($sheetNames[64]);
unset($sheetNames[65]);
unset($sheetNames[66]);

$sheetNames = array_values($sheetNames);
$sheetNames = array_map(function ($item) {
    return rtrim($item);
}, $sheetNames);

$sheetNames = array_map(function ($item) {
    return strtolower($item);
}, $sheetNames);


try {
    $allOthers = $xlsx->rows(1);
    $districtWiseDiff = [];
    $waqts = array_map(function ($item) {
        return str_replace(' ', '', strtolower($item));
    }, $allOthers[0]);
    $waqts = array_map(function ($item) {
        return rtrim($item);
    }, $waqts);

    $waqts = array_map(function ($item) {
        return str_replace(' - dhaka', '', strtolower($item));
    }, $waqts);

    $waqts = array_map(function ($item) {
        return str_replace('-dhaka', '', strtolower($item));
    }, $waqts);

    $waqts = array_filter($waqts, function ($item) {
        return $item != "";
    });

    unset($allOthers[0]);

    $districtMap = [
        "moulvibazar" => "মৌলভীবাজার",
        "meherpur" => "মেহেরপুর",
        "manikganj" => "মানিকগঞ্জ",
        "gopalganj" => "গোপালগঞ্জ",
        "barguna" => "বরগুনা",
        "satkhira" => "সাতক্ষীরা",
        "thakurgaon" => "ঠাকুরগাঁও",
        "tangail" => "টাঙ্গাইল",
        "sylhet" => "সিলেট",
        "sunamganj" => "সুনামগঞ্জ",
        "sirajganj" => "সিরাজগঞ্জ",
        "sherpur" => "শেরপুর",
        "shariatpur" => "শরীয়তপুর",
        "rangpur" => "রংপুর",
        "rangamati" => "রাঙ্গামাটি",
        "rajshahi" => "রাজশাহী",
        "rajbari" => "রাজবাড়ী",
        "pirojpur" => "পিরোজপুর",
        "patuakhali" => "পটুয়াখালী",
        "panchagarh" => "পঞ্চগড়",
        "pabna" => "পাবনা",
        "noakhali" => "নোয়াখালী",
        "nilphamari" => "নীলফামারী",
        "netrokona" => "নেত্রকোনা",
        "natore" => "নাটোর",
        "narsingdi" => "নরসিংদী",
        "narayanganj" => "নারায়ণগঞ্জ",
        "narail" => "নড়াইল",
        "naogaon" => "নওগাঁ",
        "mymensingh" => "ময়মনসিংহ",
        "munshiganj" => "মুন্সিগঞ্জ",
        "magura" => "মাগুরা",
        "madaripur" => "মাদারীপুর",
        "lakshmipur" => "লক্ষ্মীপুর",
        "lalmonirhat" => "লালমনিরহাট",
        "kushtia" => "কুষ্টিয়া",
        "jashore" => "যশোর",
        "kurigram" => "কুড়িগ্রাম",
        "kishoreganj" => "কিশোরগঞ্জ",
        "khulna" => "খুলনা",
        "khagrachhari" => "খাগড়াছড়ি",
        "joypurhat" => "জয়পুরহাট",
        "jhenaidah" => "ঝিনাইদহ",
        "jhalokati" => "ঝালকাঠি",
        "jamalpur" => "জামালপুর",
        "habiganj" => "হবিগঞ্জ",
        "gazipur" => "গাজীপুর",
        "gaibandha" => "গাইবান্ধা",
        "dhaka" => "ঢাকা",
        "feni" => "ফেনী",
        "bandarban" => "বান্দরবান",
        "barishal" => "বরিশাল",
        "brahmanbaria" => "ব্রাহ্মণবাড়িয়া",
        "chandpur" => "চাঁদপুর",
        "bogura" => "বগুড়া",
        "bhola" => "ভোলা",
        "chapai nawabganj" => "চাঁপাইনবাবগঞ্জ",
        "chittagong" => "চট্টগ্রাম",
        "chuadanga" => "চুয়াডাঙ্গা",
        "cumilla" => "কুমিল্লা",
        "coxsbazar" => "কক্সবাজার",
        "dinajpur" => "দিনাজপুর",
        "faridpur" => "ফরিদপুর",
        "bagerhat" => "বাগেরহাট",
    ];

    $final = [];
    echo count($sheetNames);
    for ($q = 0; $q < count($sheetNames); $q++) {
        $results = dod($q, "", $xlsx, [], $i);
        if ($results != "NONE") {
            if (!isset($districtMap[$sheetNames[$q]])) {
                echo $districtMap[$sheetNames[$q]] . PHP_EOL;
            }
            array_splice($results, 0, 16);
            $final[$districtMap[$sheetNames[$q]]] = $results;
        }
    }

    //print_r($final);die;

} catch (Exception $exception) {
    print $exception->getMessage();
    die;
}


function dod(int $sheetIndex, string $name, $xlsx, $map, &$kk)
{
    $dd = [];
    $rows = $xlsx->rows($sheetIndex);
    if (empty($rows) || !is_array($rows)) {
        return "NONE";
    }
    $waqts = array_map(function ($item) {
        return str_replace(' - dhaka', '', strtolower($item));
    }, $rows[0]);

    $waqts = array_map(function ($item) {
        return str_replace('-dhaka', '', strtolower($item));
    }, $waqts);


    $waqts = array_map(function ($item) {
        return rtrim($item);
    }, $waqts);

    array_shift($rows);
    $waqts = array_filter($waqts);

    foreach ($waqts as &$waqt) {
        if ($waqt == "zohor") {
            $waqt = "zohar";
        } elseif ($waqt == "zohor") {
            $waqt = "zohar";
        } elseif ($waqt == "magrib") {
            $waqt = "maghrib";
        }
    }
    $numbers = [
        1 => "১",
        2 => "২",
        3 => "৩",
        4 => "৪",
        5 => "৫",
        6 => "৬",
        7 => "৭",
        8 => "৮",
        9 => "৯",
        0 => "০",
    ];
    foreach ($rows as $i => $row) {
        $row = array_filter($row);
        foreach ($row as $item => $value) {
            if (!isset($waqts[$item])) {
                continue;
            }
            if ($item == 0) {
                $dd[$i][$waqts[$item]] = date('Y-m-d', strtotime($value));
            } else {

                $dd[$i][$waqts[$item]] = empty($value) ? "NO" : date('g:i A', strtotime($value));

                $dd[$i][$waqts[$item]] = str_ireplace(array_keys($numbers), array_values($numbers), $dd[$i][$waqts[$item]]);

                /*if (strstr($dd[$i][$waqts[$item]], 'AM'))
                    $dd[$i][$waqts[$item]] = str_ireplace('AM', 'মি.', $dd[$i][$waqts[$item]]);

                if (strstr($dd[$i][$waqts[$item]], 'PM'))
                    $dd[$i][$waqts[$item]] = str_ireplace('PM', 'মি.', $dd[$i][$waqts[$item]]);*/
            }
        }
    }
    return $dd;
}

file_put_contents($jsonFileName, json_encode($final));
