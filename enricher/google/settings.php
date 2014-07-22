<?php

$apikey = get_string('apikey', 'enricher_'.$enrichername);
$api = $name.'_apikey';
$setting = new admin_setting_configtext($api, $apikey, null, null);
$settings->add($setting);
