<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	require_once($CFG->dirroot.'/mod/literature/enricher/lib.php');
        print_r("HALLO");
	$enrichers = literature_enricher_get_folders();
	foreach ($enrichers as $enrichername) {
		$name = 'literature_enricher_' . $enrichername;
		$dirname = $enrichername;

		if(!isset($CFG->$name)) {
			$CFG->$name = 1;
		}
		$enricherstring = get_string('pluginname', 'enricher_'.$enrichername);
		$setting = new admin_setting_configcheckbox($name, $enricherstring, null, 0);
		$settings->add($setting);

    // if enricher contains further settings include them
		if (literature_enricher_check_settings($dirname)) {
			$settingsfile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'enricher' . DIRECTORY_SEPARATOR . $dirname . DIRECTORY_SEPARATOR . 'settings.php';
			include $settingsfile;
		}

	}



}
