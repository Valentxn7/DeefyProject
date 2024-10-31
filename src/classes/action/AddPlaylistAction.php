<?php

/**
 * CREER 1 PLAYLIST EN SESSION
 */
namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\repository\DeefyRepository;

class AddPlaylistAction extends Action
{
    public function execute(): string
    {
        if ($this->http_method == "POST") {

            $this->sanitize();
            $playlist = new Playlist($_POST['title']);
            $_SESSION['playlist'] = $playlist;
            DeefyRepository::getInstance()->saveEmptyPlaylist($_SESSION['playlist']);
            // on redirige vers la page de la playlist
            header("Location: TD12.php?action=display-playlist&id={$playlist->id_bdd}");
            //return "Playlist {$_POST['title']} créée";

        } else if ($this->http_method == "GET") {
            $ret = <<<HTML
                    <h2>Ajouter une nouvelle playlist</h2><br>
                    <form action="TD12.php?action=add-playlist" method="POST">
                
                        <label for="title">Titre de la playlist : </label>
                        <input type="text" id="title" name="title" required> <br><br>

                        <input type="submit" value="Créer la playlist"> <br>
                        
                    </form >
HTML;
            return $ret;
        }
    }

    public function sanitize(): void
    {
        $_POST['title'] = filter_var($_POST['title'], FILTER_SANITIZE_SPECIAL_CHARS);

    }

}

