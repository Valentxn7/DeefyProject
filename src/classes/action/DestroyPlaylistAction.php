<?php

namespace iutnc\deefy\action;

use Exception;
use iutnc\deefy\repository\DeefyRepository;

/**
 * Classe DestroyPlaylistAction.
 * Elle permet de supprimer une playlist.
 */
class DestroyPlaylistAction extends Action
{
    /**
     * @throws Exception
     */
    public function execute(): string
    {
        // pas besoin de verif les perms, il ne peut pas avoir de playlist en sessions sans être connecté
        // de plus, on vérifie la propriété de la playlist quand on la met en session (display)
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