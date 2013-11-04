<?php

$apikey = get_string('apikey', 'enricher_'.$enrichername);
$api = $name.'_apikey';
$setting = new admin_setting_configtext($api, $apikey, null, null);
$settings->add($setting);

$apikey = get_string('secretkey', 'enricher_'.$enrichername);
$api = $name.'_secretkey';
$setting = new admin_setting_configtext($api, $apikey, null, null);
$settings->add($setting);

$apikey = get_string('associatetag', 'enricher_'.$enrichername);
$api = $name.'_associatetag';
$setting = new admin_setting_configtext($api, $apikey, null, null);
$settings->add($setting);
