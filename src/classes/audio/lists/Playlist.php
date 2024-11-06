<?php

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\tracks\AudioTrack;

/**
 * Classe Playlist.
 * Elle permet de représenter une playlist.
 */
class Playlist extends AudioList
{
    public function ajouter(AudioTrack $audio): void
    {
        /**
         * $this->nbpiste = $this->nbpiste + 1;
         * $this->duree = $this->duree + $audio->duree;
         **/
        array_push($this->liste, $audio);
        $this->maj_liste_duree_nb();
    }

    public function supprimer(int $ind): void
    {
        array_splice($this->liste, $ind, 1);
        $this->maj_liste_duree_nb();
    }

    // ajouter table array diff
    public function ajouter_liste(AudioList $list): void
    {
        $add = array_diff($this->liste, $list->liste);
        $this->liste = array_combine($this->liste, $add);
        $this->maj_liste_duree_nb();
    }

}