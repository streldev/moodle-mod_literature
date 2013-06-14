<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	require_once($CFG->dirroot.'/mod/literature/enricher/lib.php');
	
	$enrichers = literature_enricher_get_folders();
	foreach ($enrichers as $enrichername) {
		$name = 'literature_enricher_' . $enrichername;
		
		if(!isset($CFG->$name)) {
			$CFG->$name = 1;
		}
		
		$enrichername = get_string('pluginname', 'enricher_'.$enrichername);
		$setting = new admin_setting_configcheckbox($name, $enrichername, null, 1);
		$settings->add($setting);
		
	}
	

	
}
