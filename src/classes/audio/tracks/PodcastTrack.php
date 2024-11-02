<?php

namespace iutnc\deefy\audio\tracks;

use Exception;

class PodcastTrack extends AudioTrack
{
    private string $date, $auteur;

    public function __construct(string $titre, string $path)
    {
        parent::__construct($titre, $path);
        /**
        $this->titre = $titre_piste;
        $this->nom_fich = $path;
         **/
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    /**
     * @throws Exception
     */
    public function __get(string $name): string
    {
        if (property_exists ($this, $name)) return $this->$name;
        else
            throw new Exception("Introuvable : " . $name);
    }

    /**
     * @throws Exception
     */
    public function __set($name, $value)
    {
        $valFixe = ["titre", "nom_fich"];
        if (!in_array($name, $valFixe)) {
            $this->$name = $value;
        } else {
            throw new Exception("Impossible de changer des valeurs fixes.");
        }
    }
}