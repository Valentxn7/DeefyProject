<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\render as R;
use iutnc\deefy\audio\tracks as T;

/**
 * Classe AudioListRenderer.
 * Elle permet de représenter un rendu d'une liste audio.
 */
class AudioListRenderer implements Renderer
{
    private AudioList $audioList;

    public function __construct(AudioList $al)
    {
        $this->audioList = $al;
    }


    /**
     * Rendu de la liste audio.
     * @param int $selector, 1 for long, 2 for preview
     * @param bool $isPrivate, vrai si la playlist appartient à un user
     * @param null $index, index de la piste (pour la suppression)
     * @return string le rendu
     */
    public function render(int $selector, bool $isPrivate, $index = null): string
    {
        if (sizeof($this->audioList->liste) === 0) {
            return "La playlist {$this->audioList->nom} est vide.";
        } else {
            $cont = "<br> <b>{$this->audioList->nom} </b><br>";

            if ($selector != 2) {
                $rend = "";
                for ($i = 0; $i < sizeof($this->audioList->liste); $i++) {
                    if ($this->audioList->liste[$i] instanceof T\AlbumTrack) {
                        $rend = new R\AlbumTrackRenderer($this->audioList->liste[$i]);
                    } else {
                        if ($this->audioList->liste[$i] instanceof T\PodcastTrack) {
                            $rend = new R\PodcastRenderer($this->audioList->liste[$i]);
                        }
                    }
                    if ($selector == 1) {
                        $index = $i + 1;
                        $cont .= $rend->render(Renderer::LONG, $isPrivate, $index);
                    } else
                        $cont .= $rend->render(Renderer::COMPACT, $isPrivate);
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
        $cont .= "<br>nb pistes: " . $this->audioList->nbPiste;

        $duree_seconds = $this->audioList->duree;  // Formate en MM:SS
        $minutes = floor($duree_seconds / 60);
        $seconds = $duree_seconds % 60;
        $cont .= "<br>durée: " . $minutes . ":" . $seconds;
        return $cont;
    }

}