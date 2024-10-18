<?php

namespace iutnc\deefy\render;

use iutnc\deefy\render\Renderer;
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
        switch ($selector) {
            case Renderer::COMPACT:
                return "<br> {$this->albumTrack->titre} - {$this->albumTrack->artiste} - {$this->albumTrack->album} <br> <br>
                        <audio id='audioPlayer' controls src='{$this->albumTrack->nom_fich}'> </audio> <br>";
                break;
            case Renderer::INTER:
                return "<br> {$this->albumTrack->numero} - {$this->albumTrack->titre} - {$this->albumTrack->artiste} - {$this->albumTrack->album} - {$this->albumTrack->duree} <br> <br> 
                        <audio id='audioPlayer' controls src='{$this->albumTrack->nom_fich}'> </audio> <br>";
                break;
            case Renderer::LONG:
                return "<br> {$this->albumTrack->numero} - {$this->albumTrack->titre} - {$this->albumTrack->artiste} - {$this->albumTrack->album} - {$this->albumTrack->duree} - {$this->albumTrack->annee} : {$this->albumTrack->genre} <br><br> 
                        <audio id='audioPlayer' controls src='{$this->albumTrack->nom_fich}'> </audio> <br>";
                break;
            default:
                return "g pas Kanpri";
        }

    }

}