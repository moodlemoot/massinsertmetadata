<?php

require_once ($CFG->libdir . '/outputcomponents.php');

class local_metadata_utils {

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

    public static function testCourseDataSet($header, $data) {
        //fonction qui pour une entete $header donnée et un jeu de donnée $data 
        //// va tester si les metadonnées existent via leur shortname, 
        // si les cours existent et si le type de valeur est bon
        // si metadata manquante => erreur critique on sort avec FALSE
        // sinon on sort en retournant un tableau associatif pour affichage
        // 
        // init du retour
        $retourObject = new stdClass();
        // le code erreur si besoin
        $retourObject->Erreur = false;
        // le libelle de l'erreur
        $retourObject->ErreurLibelle = '';
        //le tableau header + data + [erreurs] pour affichage
        $retourObject->TabAffichage = array();
        //le tableau de triplets à importer (ceux sans erreur)
        $retourObject->Triplets = array();

        // test de la taille des paramètres doit etre un talbeau non vide
        if (sizeof($header) == 0 or ! is_array($header)) {
            $retourObject->Erreur = true;
            $retourObject->ErreurLibelle = 'Le paramètre header doit être un tableau non vide';
            return $retourObject;
        }

        // test de la taille des datas doit etre un talbeau non vide
        if (sizeof($data) == 0 or ! is_array($data)) {
            $retourObject->Erreur = true;
            $retourObject->ErreurLibelle = 'Le paramètre data doit être un tableau non vide';
            return $retourObject;
        }
        // liste des types d'identifiants qui seront utilisés comme identifiant
        $tabtypeimport = array('course_shortname', 'to_do');
        // il faudrait associer les types d'id avec les context level TODO

        $tabTypeMetadata = array();
        $typeImport = $header[0];
        if (in_array($typeImport, $tabtypeimport)) {
            // echo "$typeImport ok";
        } else {
            $retourObject->Erreur = true;
            $retourObject->ErreurLibelle = "le type d objet $typeImport n est pas dans la liste d objets importables";
            return $retourObject;
        }

        // si tout est bon alors on va procéder à la verification des entetes

        for ($cpt = 1; $cpt < sizeof($header); $cpt++) {
            // on teste si la metadata existe via son shortname
            $recupmetadata = local_metadata_utils::get_metadata_by_shortname($header[$cpt]);
            // WIP pour recuperer les types

            if (sizeof($recupmetadata) > 0) {
                // on va recupérer l'objet retourné pour stocker les id et les types
                $obj = array_pop($recupmetadata);
                $tabIdMetadata[] = $obj->id;
                $tabTypeMetadata[] = $obj->datatype;
            } else {
                // si metadata manquante => erreur critique on sort en retournant FALSE
                $retourObject->Erreur = true;
                $retourObject->ErreurLibelle = "la metadonnée $header[$cpt] n'existe pas n est pas dans la liste de metadonnées";
                return $retourObject;
            }
        }

        // on construit le résultat
        // ligne entete 
        // ligne + code erreur + lib (si besoin)
        $result[] = $header;

// pour chaque ligne de donnée, vérification de l'existance du cours
// si une ligne contient une erreur (cours inexistant, metadata requis, mauvais type,...
// alors on met la ligne en erreur en précisant l'erreur
        for ($cpt = 0; $cpt < sizeof($data); $cpt++) {
            $ligne = $data[$cpt];
            
            //si on est sur un cours alors
            // TODO gérer les autre types
            if ($typeImport == 'course_shortname') {
                // on essaye de récupérer les infos du cours
                $recupObject = local_metadata_utils::get_courseId_by_shortname("$ligne[0]");
            } else {
                // on ne sait pas gérer ce type d'import 
                $retourObject->Erreur = true;
                $retourObject->ErreurLibelle = "type $typeImport non géré...";
                return $retourObject;
            }
            
            // on test ici si on a pas trop de colonne
            if (sizeof($header) < sizeof($ligne)) {
                //echo "<b>ERREUR moyenne</b> type pas bon...";
                $ligne['ERROR'] = 3;
                $ligne['ERROR_DESCR'] = "Trop de valeur pour la  $ligne[$cpt]";
                // on ne va pas plus loin pour cette ligne puisque l'on vient de lui ajouter une erreur
                $retourObject->TabAffichage[] = $ligne;
                continue;
            }

            if (sizeof($recupObject) > 0) {
                // on a trouvé un cours on 
                $obj2 = array_pop($recupObject);
                $idObjet = $obj2->id;

                //constinue on va tester les autres champs
                for ($cpt2 = 1; $cpt2 < (sizeof($ligne)); $cpt2++) {
                    $triplet = NULL;
                    // si c'est bien un string
                    // tODO lancer les test en fonction des types 
                    // pour le moment que string
                    if (is_string($ligne[$cpt2])) {
                        //ok continue
                        // on peuple le triplet(objet,metadata,value)
                        $triplet = array('objectId' => $idObjet, 'metadataId' => $tabIdMetadata[$cpt], 'value' => $ligne[$cpt2]);
                    } else {
                        //ERREUR moyenne type d'une metadonnée pas bon";
                        $ligne['ERROR'] = 2;
                        $ligne['ERROR_DESCR'] = "Le type de la metadata ne correspond pas";
                        // on ne va pas plus loin pour cette ligne puisque l'on vient de lui ajouter une erreur
                        continue;
                    }
                    // on stocke tout cela dans l'objet
                    if (!empty($triplet)) {
                        $retourObject->Triplets[] = $triplet;
                    }
                }
            } else {
                //ERREUR GRAVE l'objet n'existe pas...";
                $ligne['ERROR'] = 1;
                $ligne['ERROR_DESCR'] = "L'objet $ligne[0] n'existe pas";
            }
            //on concatene notre ligne et les eventuelles erreur au résultat
            $retourObject->TabAffichage[] = $ligne;
        }
        return $retourObject;
    }

    /**
     *
     */
    public static function write(){
        //Création de l'objet contenant les données à écrire dans Moodle :
        $dataset = new stdClass();

        //Appel de la fonction du plugin local_metadata capable d'écrire ces données en base :


    }
    
    public static function previewTable($headers = [], $data = [])
    {
        $table = new html_table();
        $table->head = $headers;
        $data_to_display = [];
        foreach ($data as $line) {
            $line_to_display = $line;
            if (isset($line_to_display['ERROR'])) { // erreur dans la ligne
                $line_to_display[] = 'Invalide';
                $line_to_display[] = $line_to_display['ERROR_DESCR'];
                unset($line_to_display['ERROR']);
                unset($line_to_display['ERROR_DESCR']);
            } else {
                $line_to_display[] = 'Valide';
                $ligne_to_display[] = '';
            }
            $data_to_display[] = $line_to_display;
        }
        $table->data = $data_to_display;
        echo html_write::table($table);
    }
    
    /*
     * Read data from $cir object and create an array with it
     */
    public static function readFromCSV($cir)
    {
        $data = [];
        while ($line = $cir->next()) { //get the line
            $data[] = $line;
        }
        return $data;
    }

}
