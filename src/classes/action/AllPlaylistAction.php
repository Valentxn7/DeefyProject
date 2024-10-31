<?php

namespace iutnc\deefy\action;

use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;

class AllPlaylistAction extends Action
{
    public function execute(): string
    {
        // TODO: LE CSS DE LA PAGE + LES PERMS DE CHAQUE UTILISATEUR
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
        $html .= "<div class='playlists-container'>";
        foreach ($id_listes as $id){
            $html .= "<a href='TD12.php?action=display-playlist&id=$id'><div class='playlist'>";
            try {$playlist = DeefyRepository::getInstance()->findPlaylistById($id);}
            catch (\Exception $e) {return $e->getMessage();}
            $rend = new AudioListRenderer($playlist);
            $html .= $rend->render(2);
            $html .= "</div> </a> <br>";
        }
        $html .= "</div>";
        return $html;
    }

}
