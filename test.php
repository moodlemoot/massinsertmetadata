<?php
require('../../config.php');
require('./classes/utils.class.php');
require_once('./classes/step1_form.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
 //require('../../lib/datalib.php');
// TEst de manipulation des csv
// tentative ouverture


/*
 * 
 *
  shortname,premierchamp,secondchamp
  TEST,valeur1,valeur2
 */
$monTestHeader = array("course_shortname", "premierchamp", "secondchamp");
$monTest[] = array("TEST", "valeur1", "valeur2", "valeur6", "valeur6");
$monTest[] = array("NOEXIST", "valeur5", "valeur6");
$monTest[] = array("TEST2", "valeur3", "ok");
// todo utiliser le csv pour obtenir le header et les data

$PAGE->set_url($CFG->wwwroot.'/local/moodle-local_massinsertmetadata/test.php', ['contextlevel' => $contextlevel]);
GLOBAL $OUTPUT;

$context = context_system::instance();
$PAGE->set_context($context);

// Test chargement de CSV
$importid = optional_param('importid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);
$returnurl = new moodle_url($CFG->wwwroot . '/local/moodle-local_massinsertmetadata/test.php');
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
    
// Appel de la fonction de ludo envoyant $cir
    
// On affiche l'erreur s'il y en a une
// Si pas d'erreur, on affiche le tableau des données avec statut et erreur s'il y a pour la ligne affichée
// Envoyer le forumulaire, réutiliser le form 1 ?

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadcoursespreview', 'tool_uploadcourse'));
//Appel de fonction preview
local_metadata_utils::previewTable($cir->get_columns(), $analysereport->TabAffichage);
local_metadata_utils::write($analysereport->Triplets);                      

echo $OUTPUT->notification('Création des metadatas réussies','notifysuccess');
echo $OUTPUT->continue_button($returnurl);

echo $OUTPUT->footer();
// Fin test chargement

// echo $OUTPUT->header();

echo " PAGE de TEST du parser de chargement en masse de metadonnées sur un type d'objet";



 
// on passe nos datas dans le parser
//$test =  local_metadata_utils::testCourseDataSet($monTestHeader,$monTest);
// echo "<hr>";
 // le parser nous retourne un objet avec les attributs
// 'Erreur' => false si pas d'erreur bloquante, true sinon
// 'ErreurLibelle' => le détail de l'erreur fatale
// 'TabAffichage' => le tableau pour affichage
// 'Triplets' => les triplets pour la persistance en base
 
//var_dump($test);
 echo $OUTPUT->footer();
