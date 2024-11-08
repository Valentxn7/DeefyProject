<?php

/**
 * AJOUTER 1 TRACK DANS LA PLAYLIST
 */

namespace iutnc\deefy\action;

use Exception;
use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\AlbumTrack;
use getID3;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\repository\DeefyRepository;

class AddAlbumTrackAction extends Action
{
    /**
     * @throws Exception
     */
    public function execute(): string
    {
        if (empty($_SESSION['playlist'])) {
            return "Veuillez selectionner une playlist.";
        }

        if ($this->http_method == "POST") {

            $this->sanitize();
            $user = AuthnProvider::getSignedInUser();
            $verif = new Authz($user);
            try {
                $verif->checkRole(Authz::USER);
            } catch (Exception) {
                return "Vous devez être connecté pour ajouter une musique à une playlist.";
            }

            try {
                $verif->checkPlaylistOwner($_SESSION['playlist']->id_bdd);
            } catch (Exception) {
                return "Vous n'êtes pas autorisé à fouiller dans les affaires des autres.";
            }


            $base_sys = realpath($_SERVER['DOCUMENT_ROOT']);  // C:\xampp\htdocs pour STOCKER LES FICHIERS
            $upload_dir = $base_sys . "\dewweb\Deefy\sound\\";  // C:\xampp\htdocs\DevWebS3\DeefyProject\audio\  pour STOCK

            $autorise = ['audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/aac'];

            if (!isset($_FILES['inputfile']))
                return "Fichier manquant ou type de fichier non autorisé.<br>";

            if ($_FILES['inputfile']['error'] === UPLOAD_ERR_OK) {
                if (in_array($_FILES['inputfile']['type'], $autorise)) {
                    $extension = pathinfo($_FILES['inputfile']['name'], PATHINFO_EXTENSION);
                    $filename = uniqid();
                    $dest = $upload_dir . $filename . '.' . $extension; // pour stocker le fichier

                    if (move_uploaded_file($_FILES['inputfile']['tmp_name'], $dest)) {  // upload_dir pour stocker le fichier
                        $track = new AlbumTrack(pathinfo($_POST['title'], PATHINFO_FILENAME), $filename . '.' . $extension, $_POST['album'], $_POST['numero']);

                        $track->genre = $_POST['genre'];
                        $track->annee = $_POST['date'];
                        $track->duree = $_POST['duree'];
                        $track->artiste = $_POST['artist'];
                        $track->album = $_POST['album'];
                        $track->numero = $_POST['numero'];

                        // ça l'ajoute a la fois dans la base pod et dans la playlist
                        DeefyRepository::getInstance()->addMusiqueToPlaylist($track, $_SESSION['playlist']);

                        $_SESSION['playlist']->ajouter($track);
                        header("Location: index.php?action=display-playlist&id={$_SESSION['playlist']->id_bdd}");
                        return "";
                    } else
                        return "Hum, hum il y a un cheveu dans la soupe.<br>Le serveur n'a pas put traiter votre requête.<br>";

                } else { // type de fichier non autorisé
                    return "Type de fichier non autorisé.<br>";
                }
            } else { // échec du téléchargement
                return "Echec du téléchargement du fichier.<br>";
            }

        } else if ($this->http_method == "GET") {
            return <<<HTML
                    <h2>Ajouter une musique à la playlist</h2><br>
                    <form id="form-add-track" action="?action=add-Albumtrack" method="POST" enctype="multipart/form-data">
               
                        <label for="inputfile">Fichier : </label>
                        <input type="file" id="inputfile" name="inputfile" required aria-label="Ajouter un fichier audio" accept=".mp3, .wav, .ogg, .aac"> <br><br>
                        
                        <label for="album">Album : </label>
                        <input type="text" id="album" name="album" required pattern=".{3,}" title="L'album doit comporter au moins 3 caractères"> <br>
                    
                        <label for="numero">n° de piste : </label>
                        <input type="text" id="numero" name="numero" required> <br>
                        
                        <details>
                        
                            <summary>Personnaliser votre épisode</summary><br>
                            
                                <label for="title">Titre : </label>
                                <input type="text" id="title" name="title" pattern=".{3,}" title="Le titre doit comporter au moins 3 caractères"> <br>
                            
                                <label for="artist">Artiste : </label>
                                <input type="text" id="artist" name="artist"> <br>
                            
                                <label for="date">Année : </label>
                                <input type="number" id="date" name="date" pattern="\d{4}" title="Entrez une année au format AAAA"> <br>
                                
                                <label for="genre">Genre : </label>
                                <input type="text" id="genre" name="genre"> <br>
                            
                        </details> <br>
                        
                        <input id="inputTrack" type="submit" value="Ajouter l'épisode à la playlist" > <br>
                        
                    </form > <br><br>               

HTML;
        }
        return "Error 418 : I'm a teapot.";  // si jamais on arrive ici, c'est qu'il y a un problème, quelqu'un a touché à quelque chose niveau HTML, tant pis pour lui, il aura une erreur 418
    }


    public function sanitize(): void
    {
        // pour les méta données
        $getID3 = new getID3();
        $fileInfo = $getID3->analyze($_FILES['inputfile']['tmp_name']);

        //var_dump($fileInfo);

        /*echo var_dump($_POST['title']);
        echo var_dump($_POST['artist']);
        echo var_dump($_POST['date']);
        echo var_dump($_POST['genre']);*/

        // Pure merveille d'un cerveau encore réveillé à 23h14 :
        // Si $_POST['genre'] a été définie spécialement, on l'assainit.
        // Sinon, on prend le genre du fichier audio.
        // Si le genre du fichier audio n'est pas défini, on prend la constante NO_GENRE de la classe AudioList
        // Tout les angles sont vérifiés, client satisfait, serveur bouillant par les tests, la vie est belle.

        // ne pas confondre isset et is_null : isset sera tjr vrai car post envoyé !! is_null sera vrai si pas de valeurs
        // FILTER_SANITIZE_STRING est devenue déprécier depuis PHP 8.1, pour des raisons de sécurité, on utilise maintenant filter_var avec FILTER_SANITIZE_SPECIAL_CHARS
        // mon gros test de la taille d'une montagne ( isset($_POST['genre']) && !(is_null($_POST['genre'])) && ($_POST['genre'] != "") ) peut simplement être remplacé par empty($_POST['genre']) qui vérifie si une variable est vide ou nulle y compris si la chaîne est vide.

        if (isset($fileInfo['tags']['id3v2'])) { // certain fichier ne précise rien malheureusement, cela entraine une erreur si on essaie d'aller chercher l'info donc on vérifie si elle existe

            $_POST['genre'] = !empty($_POST['genre']) ? filter_var($_POST['genre'], FILTER_SANITIZE_SPECIAL_CHARS)
                : (isset($fileInfo['tags']['id3v2']['genre'][0]) ? filter_var($fileInfo['tags']['id3v2']['genre'][0], FILTER_SANITIZE_SPECIAL_CHARS) : AudioList::NO_GENRE);

            $_POST['artist'] = !empty($_POST['artist']) ? filter_var($_POST['artist'], FILTER_SANITIZE_SPECIAL_CHARS) : (isset($fileInfo['tags']['id3v2']['artist'][0]) ? filter_var($fileInfo['tags']['id3v2']['artist'][0], FILTER_SANITIZE_SPECIAL_CHARS) : AudioList::NO_AUTEUR);

            $_POST['date'] = !empty($_POST['date']) ? filter_var($_POST['date'], FILTER_SANITIZE_SPECIAL_CHARS)
                : (isset($fileInfo['tags']['id3v2']['year'][0]) ? filter_var($fileInfo['tags']['id3v2']['year'][0], FILTER_SANITIZE_SPECIAL_CHARS) : AudioList::NO_DATE);

            $_POST['title'] = !empty($_POST['title']) ? filter_var($_POST['title'], FILTER_SANITIZE_SPECIAL_CHARS)
                : (isset($fileInfo['tags']['id3v2']['title'][0]) ? filter_var($fileInfo['tags']['id3v2']['title'][0], FILTER_SANITIZE_SPECIAL_CHARS) : filter_var($_FILES['inputfile']['name'], FILTER_SANITIZE_SPECIAL_CHARS));

            /*$_POST['genre'] = ( isset($_POST['genre']) && !(is_null($_POST['genre'])) && ($_POST['genre'] != "") ) ? filter_var($_POST['genre'], FILTER_SANITIZE_STRING) : (isset($fileInfo['tags']['id3v2']['genre'][0]) ? filter_var($fileInfo['tags']['id3v2']['genre'][0], FILTER_SANITIZE_STRING) : AudioList::NO_GENRE);
            $_POST['artist'] = ( isset($_POST['artist']) && !(is_null($_POST['artist'])) && ($_POST['artist'] != "") ) ? filter_var($_POST['artist'], FILTER_SANITIZE_STRING) : filter_var($fileInfo['tags']['id3v2']['artist'][0], FILTER_SANITIZE_STRING) ?? AudioList::NO_AUTEUR;
            $_POST['date'] = ( isset($_POST['date']) && !(is_null($_POST['date'])) && ($_POST['date'] != "") ) ? filter_var($_POST['date'], FILTER_SANITIZE_NUMBER_INT) : (int)filter_var((int)$fileInfo['tags']['id3v2']['year'][0], FILTER_SANITIZE_NUMBER_INT) ?? AudioList::NO_DATE;
            $_POST['title'] = ( isset($_POST['title']) && !(is_null($_POST['title'])) && ($_POST['title'] != "") ) ? filter_var($_POST['title'], FILTER_SANITIZE_STRING) : filter_var($fileInfo['tags']['id3v2']['title'][0], FILTER_SANITIZE_STRING);*/

        } else {  // dans le cas où le fichier ne précise aucune info complémentaire

            $_POST['genre'] = !empty($_POST['genre']) ? filter_var($_POST['genre'], FILTER_SANITIZE_SPECIAL_CHARS) : AudioList::NO_GENRE;
            $_POST['artist'] = !empty($_POST['artist']) ? filter_var($_POST['artist'], FILTER_SANITIZE_SPECIAL_CHARS) : AudioList::NO_AUTEUR;
            $_POST['date'] = !empty($_POST['date']) ? filter_var($_POST['date'], FILTER_SANITIZE_SPECIAL_CHARS) : AudioList::NO_DATE;
            $_POST['title'] = !empty($_POST['title']) ? filter_var($_POST['title'], FILTER_SANITIZE_SPECIAL_CHARS)
                : filter_var($_FILES['inputfile']['name'], FILTER_SANITIZE_SPECIAL_CHARS);

            /*$_POST['genre'] = ( isset($_POST['genre']) && !(is_null($_POST['genre'])) && ($_POST['genre'] != "") )  ? filter_var($_POST['genre'], FILTER_SANITIZE_STRING) : AudioList::NO_GENRE;
            $_POST['artist'] = ( isset($_POST['artist']) && !(is_null($_POST['artist'])) && ($_POST['artist'] != "")) ? filter_var($_POST['artist'], FILTER_SANITIZE_STRING) : AudioList::NO_AUTEUR;
            $_POST['date'] =  ( isset($_POST['date']) && !(is_null($_POST['date'])) && ($_POST['date'] != "")) ? filter_var($_POST['date'], FILTER_SANITIZE_NUMBER_INT) : AudioList::NO_DATE;
            if ( isset($_POST['title']) && !(is_null($_POST['title'])) && ($_POST['title'] != ""))
                $_POST['title'] = filter_var($_POST['title'], FILTER_SANITIZE_STRING);*/
        }

        $_POST['numero'] = !empty($_POST['numero']) ? filter_var($_POST['numero'], FILTER_SANITIZE_NUMBER_INT) : AudioList::NO_NUMERO;
        $_POST['album'] = !empty($_POST['album']) ? filter_var($_POST['album'], FILTER_SANITIZE_SPECIAL_CHARS) : AudioList::NO_ALBUM;

        // la durée est toujours présente, on peut donc la récupérer sans vérification
        $_POST['duree'] = (int)filter_var((float)$fileInfo['playtime_seconds'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);  // FILTER_FLAG_ALLOW_FRACTION sinon enleve le . des MS et fait un nombre a 6 chiffres
    }
}





