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


    /**
     * @param int $alternative 1 for long, 2 for preview
     * @return string
     */
    public function render(int $alternative): string
    {
        if (sizeof($this->audioList->liste) === 0) {
            return "La playlist {$this->audioList->nom} est vide";
        } else {
            $cont = "<br> <b>{$this->audioList->nom} </b><br>";

            if ($alternative != 2) {
                for ($i = 0; $i < sizeof($this->audioList->liste); $i++) {
                    if ($this->audioList->liste[$i] instanceof T\AlbumTrack) {
                        $rend = new R\AlbumTrackRenderer($this->audioList->liste[$i]);
                    } else {
                        if ($this->audioList->liste[$i] instanceof T\PodcastTrack) {
                            $rend = new R\PodcastRenderer($this->audioList->liste[$i]);
                        }
                    }
                    if ($alternative == 1)
                        $cont .= $rend->render(Renderer::LONG);
                    else
                        $cont .= $rend->render(Renderer::COMPACT);
                }

            } else {  // preview
                $cont .= "<br>";
                for ($i = 0; $i < 3; $i++) {
                    if (isset($this->audioList->liste[$i]))
                        $cont .= $this->audioList->liste[$i]->titre . "<br>";
                }
                $cont .= "...<br>";
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