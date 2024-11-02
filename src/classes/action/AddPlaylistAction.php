<?php

/**
 * CREER 1 PLAYLIST EN SESSION
 */
namespace iutnc\deefy\action;

use Exception;
use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\repository\DeefyRepository;

class AddPlaylistAction extends Action
{
    /**
     * @throws Exception
     */
    public function execute(): string
    {
        unset($_SESSION['playlist']);

        $user = AuthnProvider::getSignedInUser();
        $verif = new Authz($user);
        try {
            $verif->checkRole(Authz::USER);
        } catch (Exception) {
            return "Vous devez être connecté pour créer une playlist.";
        }

        if ($this->http_method == "POST") {

            $this->sanitize();

            $playlist = new Playlist($_POST['title']);

            DeefyRepository::getInstance()->saveEmptyPlaylist($playlist);
            // on redirige vers la page de la playlist
            header("Location: index.php?action=display-playlist&id={$_SESSION['playlist']->id_bdd}");
            return "";

        } else if ($this->http_method == "GET") {
            return <<<HTML
                    <style>
                        @media (max-width: 465px) {
                            .content h2{
                                font-size: 1.43em;
                            }
                        }
                    </style>

                    
                    <h2>Ajouter une nouvelle playlist</h2><br>
                    <form action="index.php?action=add-playlist" method="POST">
                
                        <label for="title">Titre de la playlist : </label>
                        <input type="text" id="title" name="title" required> <br><br>

                        <input type="submit" value="Créer la playlist"> <br>
                        
                    </form >
HTML;
        }
        return "";
    }

    public function sanitize(): void
    {
        $_POST['title'] = filter_var($_POST['title'], FILTER_SANITIZE_SPECIAL_CHARS);

    }

}

