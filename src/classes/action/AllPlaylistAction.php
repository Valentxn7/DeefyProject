<?php

namespace iutnc\deefy\action;

use Exception;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;

/**
 * Classe AllPlaylistAction.
 * Elle permet d'afficher toutes les playlists d'une manière plus courte et concise.
 */
class AllPlaylistAction extends Action
{
    /**
     * @throws Exception
     */
    public function execute(): string
    {
        unset($_SESSION['playlist']);

        $user = AuthnProvider::getSignedInUser();
        $authz = new Authz($user);
        $id_listes = DeefyRepository::getInstance()->allPlaylistID();
        $html = "<style>
        .content {
            max-width: 1140px;
            margin: 1% auto;
            padding: 30px 10px 30px 30px;
        }
        
        @media (max-width: 1236px) {
            .content {
                max-width: 800px;
            }
        }
        
        @media (max-width: 866px) {
            .content {
                max-width: 350px;
                padding: 20px 30px;
            }
        }

         </style>";
        $pubList = [];
        $privList = [];
        // on trie les playlists par public et privée appartenant à l'utilisateur
        foreach ($id_listes as $id) {
            $pl = DeefyRepository::getInstance()->findPlaylistById($id);
            if (!($pl->isPrivate)) {
                array_push($pubList, ['pl' => $pl, 'id' => $id]);
            } else {
                try {
                    $authz->checkRole(Authz::USER);
                    $authz->checkPlaylistOwner($id);

                    array_push($privList, ['pl' => $pl, 'id' => $id]);
                } catch (Exception) {
                }  // on ne fait rien l'utilisateur n'a rien demandé, on trie juste les playlists
            }
        }

        $html .= "<h2> Conçu pour {$user['nom']} </h2>";
        $html .= "<div class='playlists-container'>";
        foreach ($pubList as $pl) {
            $html .= "<a href='index.php?action=display-playlist&id={$pl['id']}'><div class='playlist'>";
            $rend = new AudioListRenderer($pl['pl']);
            $html .= $rend->render(2, $pl['pl']->isPrivate);
            $html .= "</div> </a> <br>";
        }
        $html .= "</div>";

        $html .= "<h2> Vos playlists </h2>";
        $html .= "<div class='playlists-container'>";
        foreach ($privList as $pl) {
            $html .= "<a href='index.php?action=display-playlist&id={$pl['id']}'><div class='playlist'>";
            $rend = new AudioListRenderer($pl['pl']);
            $html .= $rend->render(2, $pl['pl']->isPrivate);
            $html .= "</div> </a> <br>";
        }
        $html .= "</div>";

        return $html;
    }

}
