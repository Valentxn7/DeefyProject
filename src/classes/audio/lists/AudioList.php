<?php

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\lists\Exception;

abstract class AudioList
{
    public const NO_AUTEUR = "Inconnue";
    public const NO_GENRE = "N/A";
    public const NO_DATE = "N/A";
    protected string $nom;
    protected int $nbpiste, $duree;
    protected array $liste;

    public function __construct(string $name, array $arr = [])
    {
        $this->nom = $name;
        $this->liste = $arr;
        $this->duree = 0;
        $this->nbpiste = 0;
        for ($i = 1; $i <= sizeof($arr); $i++) {
            $this->duree = $this->duree + $arr[$i]->duree;
            $this->nbpiste = $this->nbpiste +1;
        }
    }

    /**
     * @throws Exception
     */
    public function __get(string $name): mixed
    {
        if (property_exists($this, $name)) return $this->$name;
        else
            throw new Exception("invalid property : " . $name);
    }

    public function MAJ_liste_duree_nb(){
        $this->duree = 0;
        $this->nbpiste = 0;
        for ($i = 0; $i < count($this->liste); $i++) {
            $this->duree = $this->duree + $this->liste[$i]->duree;
            $this->nbpiste = $this->nbpiste +1;
        }
    }
/**
 * Une liste audio (classe AudioList) est décrite par un nom, un nombre de pistes, une durée totale
 * et un tableau contenant les pistes constituant la liste.
 * • créer la classe AudioList en programmant un constructeur qui reçoit en paramètre le
 * nom de la liste et un tableau optionnel de piste qui prendra la valeur [ ] s'il est omis. Le
 * constructeur initialise les propriétés, notamment en calculant le nombre de pistes et la durée
 * totale. La classe implante le getter magique __get() pour accéder au valeur des propriétés
 * mais ne permet pas leur modification.
 */
}