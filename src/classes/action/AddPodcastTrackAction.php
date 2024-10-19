<?php

/**
 * AJOUTER 1 TRACK DANS LA PLAYLIST
 */

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\PodcastTrack;
use getID3;

class AddPodcastTrackAction extends Action
{
    public function execute(): string
    {
        if ($this->http_method == "POST") {
            $this->sanitize();
            $base_sys = realpath($_SERVER['DOCUMENT_ROOT']);  // C:\xampp\htdocs pour STOCKER LES FICHIERS
            $base_access = "http://" . $_SERVER['HTTP_HOST'];  // http://localhost/ pour ACCEDER AUX FICHIERS COTE CLIENT

            $upload_dir = $base_sys . "\DevWebS3\DeefyProject\audio\\";  // C:\xampp\htdocs\DevWebS3\DeefyProject\audio\  pour STOCK
            $access_dir = $base_access . "\DevWebS3\DeefyProject\audio\\";  // http://localhost/DevWebS3\DeefyProject\audio\  pour ACCES
            $filename = uniqid();

            $autorise = ['audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/aac'];

            if (!isset($_FILES['inputfile']))
                return "Type de fichier non autorisé<br>";

            if ($_FILES['inputfile']['error'] === UPLOAD_ERR_OK) {
                if (in_array($_FILES['inputfile']['type'], $autorise)) {
                    $extension = pathinfo($_FILES['inputfile']['name'], PATHINFO_EXTENSION);
                    $dest = $upload_dir . $filename . '.' . $extension; // pour stocker le fichier
                    $access = $access_dir . $filename . '.' . $extension;  // pour accéder au fichier
                    //echo $dest;
                    if (move_uploaded_file($_FILES['inputfile']['tmp_name'], $dest)) {  // upload_dir pour stocker le fichier
                        $track = new PodcastTrack($_FILES['inputfile']['name'], $dest);

                        if (isset($_POST['title']))  // si l'utilisateur a précisé un titre précis
                            $track = new PodcastTrack($_POST['title'], $access);  // access pour accéder au fichier cote client
                        else
                            $track = new PodcastTrack($_FILES['inputfile']['name'], $access); // sinon le nom du fichier




                        if (isset($_POST['genre']))
                            $track->genre = $_POST['genre'];
                        if (isset($_POST['date']))
                            $track->date = $_POST['date'];
                        if (isset($_POST['duree']))
                            $track->duree = $_POST['duree'];
                        if (isset($_POST['artist']))
                            $track->auteur = $_POST['artist'];

                        $_SESSION['playlist']->ajouter($track);
                        return "Votre podcast a été ajouté à la playlist.<br>";
                    } else
                        return "Hum, hum il y a un cheveu dans la soupe.<br>Le serveur n'a pas put traiter votre requête.<br>";;

                } else { // type de fichier non autorisé
                    return "Type de fichier non autorisé<br>";
                }
            } else { // échec du téléchargement
                return "Echec du téléchargement du fichier<br>";
            }

        } else if ($this->http_method == "GET") {
            $ret = <<<HTML
                    <h2>Ajouter un épisode de podcast à la playlist</h2><br>
                    <form id="form-add-track" action="TD12.php?action=add-track" method="POST" enctype="multipart/form-data">
                
                    <description>
                        <summary>
                            <label for="title">Titre : </label>
                            <input type="text" id="title" name="title"> <br>
                        
                            <label for="artist">Créateur : </label>
                            <input type="text" id="artist" name="artist"> <br>
                        
                            <label for="date">Date : </label>
                            <input type="text" id="date" name="date"> <br>
                            
                            <label for="genre">Genre : </label>
                            <input type="text" id="genre" name="genre"> <br>
                            
                            </summary> <br>
                        </description> <br>
                        
                    
                        <label for="inputfile">Fichier : </label>
                        <input type="file" id="inputfile" name="inputfile" required> <br><br>
                
                        <input type="submit" value="Ajouter épisode"> <br>
                        
                    </form > <br><br>               

HTML;
            return $ret;
        }
    }


    public function sanitize(): void
    {
        // pour les méta données
        $getID3 = new getID3();
        $fileInfo = $getID3->analyze($_FILES['inputfile']['tmp_name']);
        //var_dump($fileInfo);

        // Pure merveille d'un cerveau encore réveillé à 23h14 :
        // Si $_POST['genre'] a été définie spécialement, on l'assainit.
        // Sinon, on prend le genre du fichier audio.
        // Si le genre du fichier audio n'est pas défini, on prend la constante NO_GENRE de la classe AudioList
        // Tout les angles sont vérifiés, client satisfait, serveur bouillant par les tests, la vie est belle.

        // ne pas confondre isset et is_null : isset sera tjr vrai car post envoyé !! is_null sera vrai si pas de valeurs

        $_POST['genre'] = is_null($_POST['genre'])  || isset($_POST['genre']) ? filter_var($_POST['genre'], FILTER_SANITIZE_STRING) : filter_var($fileInfo['tags']['id3v2']['genre'][0], FILTER_SANITIZE_STRING) ?? AudioList::NO_GENRE;

        $_POST['artist'] = is_null($_POST['artist']) ? filter_var($_POST['artist'], FILTER_SANITIZE_STRING) :filter_var($fileInfo['tags']['id3v2']['artist'][0], FILTER_SANITIZE_STRING) ?? AudioList::NO_AUTEUR;

        $_POST['date'] = is_null($_POST['date']) || isset($_POST['date'])? filter_var($_POST['date'], FILTER_SANITIZE_STRING) : filter_var($fileInfo['tags']['id3v2']['year'][0], FILTER_SANITIZE_STRING)  ?? AudioList::NO_DATE;
        // TODO TT SANETISER
        $_POST['duree'] = (int) filter_var((float) $fileInfo['playtime_seconds'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);  // FILTER_FLAG_ALLOW_FRACTION sinon enleve le . des MS et fait un nombre a 6 chiffres

        $_POST['title'] = is_null($_POST['title']) ? filter_var($_POST['date'], FILTER_SANITIZE_STRING) : filter_var($fileInfo['tags']['id3v2']['title'][0], FILTER_SANITIZE_STRING); // pas de sinon, on prendra plus tard le nom du fichier si nécessaire lors de la création de l'objet PodcastTrack

    }
}

?>



