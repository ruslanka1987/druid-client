<?php

use Carbon\Carbon;
use Level23\Druid\DruidClient;

require __DIR__ . '/vendor/autoload.php';

$client = new DruidClient([
    'broker_url' => 'http://10.82.1.23:8082',
    'router_url' => 'http://10.82.1.23:8888'
]);

print_r($client->query('dmart_meas_prod_oil_month_v0016')
    ->interval(
        '2025-01-01',
        '2025-01-02'
    )
    ->select('uwi')->toJson()); ;

