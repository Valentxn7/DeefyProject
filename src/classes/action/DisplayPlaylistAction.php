<?php
/**
 * AFFICHER LA PLAYLIST EN SESSION
 */

namespace iutnc\deefy\action;

use iutnc\deefy\render\AudioListRenderer;

class DisplayPlaylistAction extends Action
{
    public function execute(): string
    {
        $playlist = $_SESSION['playlist'];
        $rend = new AudioListRenderer($playlist);
        $html = $rend->render(10);
        return $html;
    }

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
