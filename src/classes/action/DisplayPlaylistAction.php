<?php
/**
 * AFFICHER LA PLAYLIST EN SESSION
 */

namespace iutnc\deefy\action;

use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;

class DisplayPlaylistAction extends Action
{
    public function execute(): string
    {
        if (empty($_GET['id'])) {
            return "Veuillez choisir une playlist";
        }
        //echo "<br><br>1: " . var_dump($_GET['id']);
        $id = filter_var((int) $_GET['id'], FILTER_SANITIZE_NUMBER_INT);
        //echo "<br><br>2: " . var_dump($id);
        try {$playlist = DeefyRepository::getInstance()->findPlaylistById($id);}
        catch (\Exception $e) {return $e->getMessage();}
        $_SESSION['playlist'] = $playlist;
        $rend = new AudioListRenderer($playlist);
        $rend = $rend->render(1);

        $_SERVER['REQUEST_METHOD'] = "GET";  // pr demander les form
        $form1 = new AddPodcastTrackAction();
        $form1 = $form1->execute();

        $form2 = new AddAlbumTrackAction();
        $form2 = $form2->execute();


        $html = <<<HTML
        <style>
        .content {
            display: flex;
            width: 100%;
            max-width: 90%;
            max-width: 90%;
            gap: 20px;
            margin: 2% auto;
        }

        </style>
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
                <button>Supprimer la playlist</button>
            </div>
            
        </div>
        HTML;
        return $html;
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
