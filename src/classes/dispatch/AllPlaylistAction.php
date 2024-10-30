<?php

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action\Action;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\repository\DeefyRepository;

class AllPlaylistAction extends Action
{
    public function execute(): string
    {
        $id_listes = DeefyRepository::getInstance()->allPlaylistID();
        $html = "";
        foreach ($id_listes as $id){
            $html .= "<a href='TD12.php?action=display-playlist&id=$id'><div class='content'>";
            try {$playlist = DeefyRepository::getInstance()->findPlaylistById($id);}
            catch (\Exception $e) {return $e->getMessage();}
            $rend = new AudioListRenderer($playlist);
            $html .= $rend->render(10);
            $html .= "</div class='content'> </a> <br>";
        }
        return $html;
    }

}
