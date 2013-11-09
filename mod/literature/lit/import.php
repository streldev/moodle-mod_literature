

// --------------------------------------------------------------------------------------
// Process formadata

if ($mform->is_submitted()) {

    // Form submitted, process formdata
    $content = $mform->get_file_content('mod_literature_import');
    $filename = $mform->get_new_filename('mod_literature_import');
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    if (!$content) {
        print_error('error:file:emptycontent', 'literature', $PAGE->url, $filename);
    }

    // Load importer
    $importer = literature_converter_load_importer_by_extension($extension);
    if (!$importer) {
        $a = new stdClass();
        $extensions = literature_converter_get_import_extensions();
        $a->extensions = '';
        foreach ($extensions as $ext) {
            $a->extensions .= $ext . ' ';
        }
        $a->yourextension = $extension;
        print_error('error:importer:extensionnotsupported', 'literature', $PAGE->url, $a);
    }


    $literatures = $importer->import($content);
    if (!$literatures) {
        print_error('error:importer:import', 'literature', $PAGE->url, $filename);
    }

    $failedtoinsert = array();
    foreach ($literatures as $literature) {
        $litid = $literature->insert();
        if ($litid) {
            if (!literature_dbobject_literaturelist::add_literature($listid, $litid)) {
                $literature->delete();
                $failedtoinsert[] = $literature;
            }
        } else {
            $failedtoinsert[] = $literature;
        }
    }

    if (count($failedtoinsert) > 0) {
        $titles = '';
        foreach ($failedtoinsert as $item) {
            $titles .= $item->title . ', ';
        }
        print_error('error:lit:insertmultiple', 'literature', $PAGE->url, $titles);
    }

    $url = new moodle_url('/mod/literature/list/view.php');
    $url->param('id', $listid);
    redirect($url);
}


// Extend navigation
$node = $PAGE->navigation->find('literature_managelists', navigation_node::TYPE_CONTAINER);
if ($node) {
    $listinfos = literature_dbobject_listinfo::load_by_userid($USER->id);
    foreach ($listinfos as $listinfo) {
        $url = new moodle_url($CFG->wwwroot . '/mod/literature/list/view.php');
        $url->param('id', $listinfo->id);

        $listnode = $node->add(
                $listinfo->name, $url, navigation_node::TYPE_ACTIVITY
        );

        if ($listid == $listinfo->id) {
            $importnode = $listnode->add(
                    get_string('import', 'literature'), $PAGE->url, navigation_node::TYPE_ACTIVITY
            );
            $importnode->make_active();
        }
    }
}

$data = new stdClass();
$data->listid = $listid;

$PAGE->set_title(get_string('importlit', 'literature'));
$PAGE->set_heading(get_string('import', 'literature'));
$PAGE->set_pagelayout('standard');

// Output page
echo $OUTPUT->header();

$mform->set_data($data);
$mform->display();

// Finish the page
echo $OUTPUT->footer();
