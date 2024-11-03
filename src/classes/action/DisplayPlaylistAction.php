<?php

namespace iutnc\deefy\action;

use Exception;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;

/**
 * Classe DisplayPlaylistAction.
 * Elle permet d'afficher une playlist d'une manière plus détaillée et avec des actions possibles.
 * Elle vérifie les permissions pour afficher la playlist.
 * Elle stocke la playlist dans la session pour les actions suivantes.
 */
class DisplayPlaylistAction extends Action
{
    /**
     * @throws Exception
     */
    public function execute(): string
    {
        if (empty($_GET['id'])) {
            return "Veuillez choisir une playlist.";
        }
        //echo "<br><br>1: " . var_dump($_GET['id']);
        $id = filter_var((int)$_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        //echo "<br><br>2: " . var_dump($id);
        $user = AuthnProvider::getSignedInUser();
        $authz = new Authz($user);

        try {
            $playlist = DeefyRepository::getInstance()->findPlaylistById($id);
        } catch (Exception $e) {
            return $e->getMessage();
        }  // si la playlist n'existe pas

        if ($playlist->isPrivate) {

            try {
                $authz->checkRole(Authz::USER);
            } catch (Exception) {
                return "Sans compte, veuillez vous satisfaire des playlists publiques.";
            }

            try {
                $authz->checkPlaylistOwner($id);
            } catch (Exception) {
                return "Ce n'est pas bien de regarder les playlists des autres.";
            }

            $_SESSION['playlist'] = $playlist;
            $rend = new AudioListRenderer($playlist);
            $rend = $rend->render(1);

            $_SERVER['REQUEST_METHOD'] = "GET";  // pr demander les form (et oui pas betos)
            $form1 = new AddPodcastTrackAction();
            $form1 = $form1->execute();

            $form2 = new AddAlbumTrackAction();
            $form2 = $form2->execute();

            $supp = <<<HTML
            <a href="?action=delete-playlist">
                <button>Supprimer la playlist</button>
            </a>
HTML;
            $style = "";

        } else {
            $rend = new AudioListRenderer($playlist);
            $rend = $rend->render(1);
            $form1 = "";
            $form2 = "";
            $supp = "";
            $style = <<<HTML
            <style>
            .form-actions {
                flex: 1;
                max-width: 30%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-width: 355px;
                margin-right: 3%;
            }
            
            .content{
                max-width: 40%;
                display: block;
            }
            
            #playlist-content {
                margin-right: 0;
                max-width: 100%;
            }
            
            @media (max-width: 1700px) {
                .content {
                    max-width: 45%;
                }
            }
            
            @media (max-width: 1400px) {
                .content {
                    max-width: 50%;
                }
            }
            
            @media (max-width: 1100px) {
                .content {
                    max-width: 60%;
                }
            }
            @media (max-width: 1000px) {
                .content {
                    max-width: 70%;
                }
            }
            @media (max-width: 800px) {
                .content {
                    max-width: 80%;
                }
            }
            @media (max-width: 700px) {
                .content {
                    max-width: 90%;
                }
            }
            
            
            
            
            
            </style>
HTML;

        }

        return <<<HTML
        <style>
        .content {
            display: flex;
            width: 100%;
            max-width: 90%;
            gap: 20px;
            margin: 2% auto;
        }

        </style>
        
        $style
        
        <div id="playlist-content">
            $rend
        </div>

        <div class="form-actions">

            <div class="ajout-musique">

                $form1

            </div>

            <div class="ajout-podcast">

                $form2

            </div>


            <div class="supp-button">
            
                $supp
            
            </div>
            
        </div>
        HTML;
    }



//    public function execute(): string
//    {
//        $playlist = $_SESSION['playlist'];
//        $rend = new AudioListRenderer($playlist);
//        $html = $rend->render(10);
//        return $html;
//    }

    /**
     * public function execute(): string
     * {
     * $playlist = $_SESSION['playlist'];
     * $tracks = $playlist->getTracks();
     * $html = "<h1>Playlist : " . $playlist->getName() . "</h1>";
     * $html .= "<ul>";
     * foreach ($tracks as $track) {
     * $html .= "<li>" . $track->getTitle() . "</li>";
     * }
     * $html .= "</ul>";
     * return $html;
     * }
     */
}
