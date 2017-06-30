<?php
require('../../config.php');
require('./classes/utils.class.php');
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
echo $OUTPUT->header();

echo " PAGE de TEST du parser de chargement en masse de metadonnées sur un type d'objet";


 
 
// on passe nos datas dans le parser
$test =  local_metadata_utils::testCourseDataSet($monTestHeader,$monTest);
 echo "<hr>";
 // le parser nous retourne un objet avec les attributs
// 'Erreur' => false si pas d'erreur bloquante, true sinon
// 'ErreurLibelle' => le détail de l'erreur fatale
// 'TabAffichage' => le tableau pour affichage
// 'Triplets' => les triplets pour la persistance en base
 
var_dump($test);
 echo $OUTPUT->footer();
