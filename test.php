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
$monTestHeader = array("shortname", "premierchamp", "secondchamp");
$monTest[] = array("TEST", "valeur1", "valeur2");
$monTest[] = array("NOEXIST", "valeur5", "valeur6");
$monTest[] = array("TEST2", "valeur3", false);

$PAGE->set_url($CFG->wwwroot.'/local/moodle-local_massinsertmetadata/test.php', ['contextlevel' => $contextlevel]);
GLOBAL $OUTPUT;
echo $OUTPUT->header();

echo " PAGE de TEST";


 
 
// sinon on marque la ligne comme importable 
// on renvoit le jeu de donnée

$test =  Utils::testDataSet($monTestHeader,$monTest);
 echo "<hr>";
var_dump($test);
 echo $OUTPUT->footer();
