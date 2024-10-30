<?php

namespace iutnc\deefy\render;

use iutnc\deefy\render as R;
use iutnc\deefy\audio\tracks as T;

class AudioListRenderer implements Renderer
{
    private \iutnc\deefy\audio\lists\AudioList $audioList;

    public function __construct(\iutnc\deefy\audio\lists\AudioList $al)
    {
        $this->audioList = $al;
    }


    public function render(int $inutile): string
    {
        if (sizeof($this->audioList->liste) === 0) {
            return "La playlist {$this->audioList->nom} est vide";
        } else {
            $cont = "<br> <b>{$this->audioList->nom} </b><br>";
            for ($i = 0; $i < sizeof($this->audioList->liste); $i++) {
                if ($this->audioList->liste[$i] instanceof T\AlbumTrack) {
                    $rend = new R\AlbumTrackRenderer($this->audioList->liste[$i]);
                } else {
                    if ($this->audioList->liste[$i] instanceof T\PodcastTrack) {
                        $rend = new R\PodcastRenderer($this->audioList->liste[$i]);
                    }
                }
                $cont .= $rend->render(Renderer::COMPACT);
            }
        }
        $cont .= "<br>nb pistes: " . $this->audioList->nbpiste;

        $duree_seconds = $this->audioList->duree;  // Formate en MM:SS
        $minutes = floor($duree_seconds / 60);
        $seconds = $duree_seconds % 60;
        $cont .= "<br>durée: " . $minutes . ":" . $seconds;
        return $cont;
    }
}