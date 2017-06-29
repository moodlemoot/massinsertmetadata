<?php

class Utils {

    public static function get_metadata_by_shortname($shortname) {
        global $DB;
        return $DB->get_records_sql("SELECT id,datatype
                                 FROM {local_metadata_field}
                                  WHERE shortname = '$shortname'");
    }

    public static function get_courseId_by_shortname($shortname) {
        global $DB;
        return $DB->get_records_sql("SELECT id
                                 FROM {course}
                                  WHERE shortname = '$shortname'");
    }

    public static function testDataSet($header, $data) {
        //fonction qui pour une entete $header donnée et un jeu de donnée $data 
        //// va tester si les metadonnées existent via leur shortname, 
        // si les cours existent et si le type de valeur est bon
        // si metadata manquante => erreur critique on sort avec FALSE
        // sinon on sort en retournant un tableau associatif pour affichage
// verification des entetes


        $tabTypeMetadata = array();
        for ($cpt = 1; $cpt < sizeof($header); $cpt++) {
            // on teste si la metadata existe via son shortname
            $recupmetadata = Utils::get_metadata_by_shortname($header[$cpt]);
            // WIP pour recuperer les types
            //var_dump($recupmetadata);
            if (sizeof($recupmetadata) > 0) {
                // ok on continue
                // on stocke le type dans un tableau dédié
                /* WIP problème faire un pop du tableau pour récuperer la valeur ?
                  foreach ($tabTypeMetadata as $value) {
                  echo "un sous tableau";
                  // WIP a finir var_dump($value);
                  $tabTypeMetadata[] = $value->datatype;
                  }
                 * 
                 */
                // $tabTypeMetadata[]=$recupmetadata[0]->datatype;
            } else {
                // si metadata manquante => erreur critique on sort en retournant FALSE
                // echo "$header[$cpt] n'existe pas";
                // WIP voir le type de retour pour renvoyer un libellé également ?
                return FALSE;
            }
        }
        // on construit le résultat
        // ligne entete 
        // ligne + code erreur + lib (si besoin)
        $result[] = $header;

// pour chaque ligne de donnée, vérification de l'existance du cours
// si une ligne contient une erreur (cours inexistant, metadata requis, mauvais type,...
// alors on met la ligne en erreur en précisant l'erreur
        foreach ($data as $ligne) {
            // pour chaque ligne, on a un sous tableau
            // on va tester l'existence du cours
           // echo "<br><br>nouvelle ligne de data  pour le cours: $ligne[0]<br>\n";
            // test de l'existence de l'objet
            //$verifcours = get_course(1);
            //var_dump($verifcours);
            // on essaye de récupérer les infos du cours
            $recupcours = Utils::get_courseId_by_shortname("$ligne[0]");
            if (sizeof($recupcours) > 0) {
                //echo "<br>\n OK  $ligne[0] existe !<br>\n";
                // on a trouvé un cours on 
                //constinue on va tester les autres champs
                for ($cpt = 1; $cpt < sizeof($ligne); $cpt++) {
                    //echo 'on va tester : ' . $ligne[$cpt] . " pour le type $tabTypeMetadata[cpt]<br>\n";
                    // si c'est bien un string
                    // tODO lancer les test en fonction des types 
                    if (is_string($ligne[$cpt])) {
                        //ok continue
                       // echo "type string ok";
                    } else {
                        //echo "<b>ERREUR moyenne</b> type pas bon...";
                        $ligne['ERROR'] = 2;
                        $ligne['ERROR_DESCR'] = "Le type de la metadata $ligne[$cpt] ne correspond pas";
                        // on ne va pas plus loin pour cette ligne puisque l'on vient de lui ajouter une erreur
                        breack;
                    }
                }
            } else {
                //echo "<b>ERREUR GRAVE</b> COURS :( $ligne[0] n'existe pas...";
                $ligne['ERROR'] = 1;
                $ligne['ERROR_DESCR'] = "Le cours $ligne[0] n'existe pas";
            }
            //on concatene notre ligne et les eventuelles erreur au résultat
            $result[] = $ligne;
        }
        return $result;
    }

}
