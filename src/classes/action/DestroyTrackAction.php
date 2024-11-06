<?php

namespace iutnc\deefy\action;

use Exception;
use iutnc\deefy\repository\DeefyRepository;

/**
 * Classe DestroyTrackAction.
 * Elle permet de supprimer un track d'une playlist.
 */
class DestroyTrackAction extends Action
{
    /**
     * @throws Exception
     */
    public function execute(): string
    {
        // pas besoin de verif les perms, il ne peut pas avoir de playlist en sessions sans être connecté
        // de plus, on vérifie la propriété de la playlist quand on la met en session (display)
        if (empty($_SESSION['playlist'])) {
            return "Veuillez selectionner une playlist qui vous appartient.";
        }
        if (empty($_GET['pos'])) {
            return "Veuillez selectionner présent dans votre playlist.";
        }

        try {
            DeefyRepository::getInstance()->supprimerTrack($_SESSION['playlist']->id_bdd, $_GET['pos']);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        header("Location: index.php?action=display-playlist&id=" . $_SESSION['playlist']->id_bdd);
        return "";
    }
}