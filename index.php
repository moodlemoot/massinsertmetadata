<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Moodle frontpage.
 *
 * @package    core
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once('./classes/step1_form.php');
require('./classes/utils.class.php');

admin_externalpage_setup('tooluploadcourse');

$importid = optional_param('importid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

$returnurl = new moodle_url($CFG->wwwroot . '/local/moodle-local_massinsertmetadata/index.php');

if (empty($importid)) {
    $mform1 = new tool_upload_metadata_step1();
    if ($form1data = $mform1->get_data()) {
        $importid = csv_import_reader::get_new_iid('uploadcourse');
        $cir = new csv_import_reader($importid, 'uploadcourse');
        $content = $mform1->get_file_content('coursefile');
        $readcount = $cir->load_csv_content($content, $form1data->encoding, $form1data->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvfileerror', 'tool_uploadcourse', $returnurl, $cir->get_error());
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl, $cir->get_error());
        }
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading_with_help(get_string('uploadcourses', 'tool_uploadcourse'), 'uploadcourses', 'tool_uploadcourse');
        $mform1->display();
        echo $OUTPUT->footer();
        die();
    }
} else {
    $cir = new csv_import_reader($importid, 'uploadcourse');
}

$context = context_system::instance();

$cir->init();
// Appel de la fonction de ludo envoyant $cir
$analysereport = local_metadata_utils::testCourseDataSet($cir->get_columns(),local_metadata_utils::readFromCSV($cir));

if($analysereport->Erreur == true) {
    // modif LS
 die($analysereport->ErreurLibelle);   
}
    
// On affiche l'erreur s'il y en a une
// Si pas d'erreur, on affiche le tableau des données avec statut et erreur s'il y a pour la ligne affichée
// Envoyer le forumulaire, réutiliser le form 1 ?

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadcoursespreview', 'tool_uploadcourse'));
//Appel de fonction preview
local_metadata_utils::previewTable($cir->get_columns(), $analysereport->TabAffichage);
                                   
local_metadata_utils::write($analysereport->Triplets);

echo $OUTPUT->notification('notifysuccess');
echo $OUTPUT->continue_button($returnurl);
    
echo $OUTPUT->footer();
?>
