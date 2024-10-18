<?php

namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\audio\tracks\AudioTrack;

class AlbumTrack extends AudioTrack
{
    /**
     * • prévoir un constructeur recevant uniquement le titre de la piste et le chemin du fichier audio,
     * • programmer une méthode pour transformer l'objet en une chaine de caractères. On appellera
     * la méthode __toString() et on utilise la fonction php json_encode pour produire une
     * chaine de caractère au format json.
     * • nommer le fichier AlbumTrack.php
     **/
    //pr chaque piste
    protected string $artiste, $album, $annee, $numero;

    public function __construct(string $titre_piste, string $path, string $alb_name, string $num_piste)
    {
        parent::__construct($titre_piste, $path);
        $this->album = $alb_name;
        $this->numero = $num_piste;
        /**
        $this->titre = $titre_piste;
        $this->nom_fich = $path;
         **/
    }


    public function __toString(): string
    {
        return "titre : " . $this->titre . " / " .
            "artiste : " . $this->artiste . " / " .
            "album : " . $this->album . " / " .
            "annee : " . $this->annee . " / " .
            "numero : " . $this->numero . " / " .
            "duree : " . $this->duree . " / " .
            "nom_fich : " . $this->nom_fich;
    }

}