<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\AlbumTrack;


class AlbumTrackRenderer implements Renderer
{
    private AlbumTrack $albumTrack;

    public function __construct(AlbumTrack $at)
    {
        $this->albumTrack = $at;
    }

    /**
     * ex3
     * @param int $selector
     * @return string
     */
    public function render(int $selector): string
    {
        $duree_seconds = $this->albumTrack->duree;  // Formate en MM:SS
        $minutes = floor($duree_seconds / 60);
        $seconds = $duree_seconds % 60;

        switch ($selector) {
            case Renderer::COMPACT:  // le minimum certes, mais on teste déjà si on a le minimum…
                $ret = "<br> {$this->albumTrack->titre}";
                $ret .= ($this->albumTrack->artiste == AudioList::NO_AUTEUR) ? "" : " - {$this->albumTrack->artiste}";  // s'il n'y a pas d'auteur on affiche rien sinon on affiche l'auteur
                $ret .= " - " . sprintf("%02d:%02d", $minutes, $seconds) . "<br> <br> 
                        <audio id='audioPlayer' controls src='{$this->albumTrack->nom_fich}'> </audio> <br>";
                return $ret;

            case Renderer::LONG:
                $ret = "<br> {$this->albumTrack->titre}";
                $ret .= ($this->albumTrack->album == AudioList::NO_ALBUM) ? "" : " - {$this->albumTrack->album}";
                $ret .= ($this->albumTrack->numero == AudioList::NO_NUMERO) ? "" : " - {$this->albumTrack->numero}";
                $ret .= ($this->albumTrack->artiste == AudioList::NO_AUTEUR) ? "" : " - {$this->albumTrack->artiste}";  // s'il n'y a pas d'auteur on affiche rien sinon on affiche l'auteur
                $ret .= ($this->albumTrack->genre == AudioList::NO_GENRE) ? "" : " - {$this->albumTrack->genre}";
                $ret .= ($this->albumTrack->annee == AudioList::NO_ANNEE) ? "" : " - {$this->albumTrack->annee}";
                $ret .= " - " . sprintf("%02d:%02d", $minutes, $seconds) . "<br> <br> 
                        <audio id='audioPlayer' controls src='{$this->albumTrack->nom_fich}'> </audio> <br>";
                return $ret;

            default:
                return "g pas Kanpri";
        }
    }

}