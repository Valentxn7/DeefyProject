<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\AudioTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\render\Renderer;

class PodcastRenderer implements Renderer
{
    private PodcastTrack $podcast;

    public function __construct(PodcastTrack $pt)
    {
        $this->podcast = $pt;
    }

    /**
     * ex5
     * @param int $selector
     * @return string
     */
    public function render(int $selector): string
    {
        $duree_seconds = $this->podcast->duree;  // Formate en MM:SS
        $minutes = floor($duree_seconds / 60);
        $seconds = $duree_seconds % 60;
        switch ($selector) {
            case Renderer::COMPACT:  // le minimum certes, mais on teste déjà si on a le minimum…
                $ret = "<br> {$this->podcast->titre}";
                $ret .= ($this->podcast->auteur === AudioList::NO_AUTEUR) ? "" : " - {$this->podcast->auteur}";  // s'il n'y a pas d'auteur on affiche rien sinon on affiche l'auteur
                $ret .= " - " . sprintf("%02d:%02d", $minutes, $seconds) . "<br> <br> 
                        <audio id='audioPlayer' controls src='{$this->podcast->nom_fich}'> </audio> <br>";
                return $ret;

            case Renderer::LONG:
                $ret = "<br> {$this->podcast->titre}";
                $ret .= ($this->podcast->auteur === AudioList::NO_AUTEUR) ? "" : " - {$this->podcast->auteur}";  // s'il n'y a pas d'auteur on affiche rien sinon on affiche l'auteur
                $ret .= ($this->podcast->date === AudioList::NO_DATE) ? "" : " - {$this->podcast->date}";
                $ret .= ($this->podcast->genre === AudioList::NO_GENRE) ? "" : " - {$this->podcast->genre}";
                $ret .= " - " . sprintf("%02d:%02d", $minutes, $seconds) . "<br> <br> 
                        <audio id='audioPlayer' controls src='{$this->podcast->nom_fich}'> </audio> <br>";
                return $ret;

            default:
                return "g pas Kanpri";
        }
    }

}