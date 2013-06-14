<?php

function local_litnav_extends_navigation(global_navigation $navigation) {
	
	global $USER;
	
	if(isloggedin()) {
	    $mainnode = $navigation->add(
	    		get_string('literature', 'local_litnav'),
				null,
				navigation_node::TYPE_CONTAINER,
	    		'literature',
	    		'literature'
				);
	    
	    $mainnode->add(
	    		get_string('managelists', 'local_litnav'),
	    		new moodle_url('/mod/literature/list/index.php'),
	    		navigation_node::TYPE_CONTAINER,
	    		'literature_managelists',
	    		'literature_managelists'
	    		);
	    
	    $mainnode->add(
	    		get_string('searchlit', 'local_litnav'),
	    		new moodle_url('/mod/literature/lit/search.php?search=false'),
	    		navigation_node::TYPE_ACTIVITY
	    		);
	    
	    $mainnode->add(
	    		get_string('importlists', 'local_litnav'),
	    		new moodle_url('/mod/literature/list/import.php'),
	    		navigation_node::TYPE_ACTIVITY,
	    		'literature_importlists',
	    		'literature_importlists'
	    );
	}
	
	// Check if admin
 	$admins = get_admins();
    $isadmin = false;
    foreach ($admins as $admin) {
        if ($USER->id == $admin->id) {
            $isadmin = true;
            break;
        }
    }
    if ($isadmin) {
        $mainnode->add(
				get_string('managesource', 'local_litnav'),
				new moodle_url('/mod/literature/searchsource/index.php'),
				navigation_node::TYPE_CONTAINER,
        		'literature_managesource',
        		'literature_managesource'
				);
    } 

}
