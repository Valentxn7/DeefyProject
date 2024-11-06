<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\AlbumTrack;

/**
 * Classe AlbumTrackRenderer.
 * Elle permet de représenter un rendu d'une piste d'album.
 */
class AlbumTrackRenderer implements Renderer
{
    private AlbumTrack $albumTrack;

    public function __construct(AlbumTrack $at)
    {
        $this->albumTrack = $at;
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

                // merci internet d'avoir créer des x plus petits et jolie
                if ($isPrivate) {
                    $supp = <<<HTML
                        <div class="audio-player">
                                    <a href='index.php?action=delete-track&pos=$index' class="track-delete-button">
                                    ×
                                    </a>
                        HTML;

                    $ferm = "</div>";
                } else {
                    $supp = "";
                    $ferm = "";
                }
                $path = "sound\\" . $this->albumTrack->nom_fich;

                $ret .= " - " . sprintf("%02d:%02d", $minutes, $seconds) . "<br> <br> 
                        $supp <audio id='audioPlayer' controls src='{$path}'> </audio> {$ferm} <br>";
                return $ret;

            default:
                return "g pas Kanpri";
        }
    }

}