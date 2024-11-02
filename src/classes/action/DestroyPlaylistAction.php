<?php

namespace iutnc\deefy\action;

use Exception;
use iutnc\deefy\repository\DeefyRepository;

class DestroyPlaylistAction extends Action
{
    /**
     * @throws Exception
     */
    public function execute(): string
    {
        if (empty($_SESSION['playlist'])) {
            return "Veuillez selectionner une playlist.";
        }

        try {
            DeefyRepository::getInstance()->deletePlaylist($_SESSION['playlist']->id_bdd);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        header("Location: index.php?action=playlists");
        return "";
    }
}