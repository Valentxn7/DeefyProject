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

            case Renderer::INTER:
                return "<br> {$this->podcast->date} - {$this->podcast->titre} - {$this->podcast->auteur} - {$this->podcast->duree}m <br> <br>  
                        <audio id='audioPlayer' controls src='{$this->podcast->nom_fich}'> </audio> <br>";

            case Renderer::LONG:
                return "<br> {$this->podcast->date} - {$this->podcast->titre} - {$this->podcast->auteur} - {$this->podcast->genre} - {$this->podcast->duree}m <br><br> 
                        <audio id='audioPlayer' controls src='{$this->podcast->nom_fich}'> </audio> <br>";

            default:
                return "g pas Kanpri";
        }
    }

}