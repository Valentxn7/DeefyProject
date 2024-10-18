<?php

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\lists\AudioList;

class Playlist extends AudioList
{
    public function ajouter(\iutnc\deefy\audio\tracks\AudioTrack $audio)
    {
        /**
        $this->nbpiste = $this->nbpiste + 1;
        $this->duree = $this->duree + $audio->duree;
         **/
        array_push($this->liste, $audio);
        $this->MAJ_liste_duree_nb();
    }

    public function supprimer(int $ind)
    {
        array_splice($this->liste, $ind, 1);
        $this->MAJ_liste_duree_nb();
    }

    // ajouter table array diff
    public function ajouter_liste(AudioList $list){
        $add = array_diff($this->liste, $list->liste);
        $this->liste = array_combine($this->liste, $add);
        $this->MAJ_liste_duree_nb();
    }

}