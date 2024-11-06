<?php

namespace iutnc\deefy\audio\lists;

use Exception;

/**
 * Classe AudioList.
 * Elle permet de représenter une liste audio.
 */
abstract class AudioList
{
    public const NO_AUTEUR = "Inconnue";
    public const NO_GENRE = "N/A";
    public const NO_DATE = "0000-00-00";
    public const NO_ALBUM = "N/A";
    public const NO_ANNEE = 0;
    public const NO_NUMERO = -1;
    protected string $nom;
    protected int $nbPiste, $duree;
    protected array $liste;
    protected int $id_bdd;
    protected bool $isPrivate = true;

    /**
     * Constructeur de la classe AudioList.
     * @param string $name Le nom de la liste.
     * @param array $arr Un tableau de pistes.
     */
    public function __construct(string $name, array $arr = [])
    {
        $this->nom = $name;
        $this->liste = $arr;
        $this->duree = 0;
        $this->nbPiste = 0;
        for ($i = 1; $i <= sizeof($arr); $i++) {
            $this->duree = $this->duree + $arr[$i]->duree;
            $this->nbPiste = $this->nbPiste + 1;
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

    /**
     * Pemet d'actualiser la durée et le nombre de pistes de la liste.
     * @return void
     */
    public function maj_liste_duree_nb(): void
    {
        $this->duree = 0;
        $this->nbPiste = 0;
        for ($i = 0; $i < count($this->liste); $i++) {
            $this->duree = $this->duree + $this->liste[$i]->duree;
            $this->nbPiste = $this->nbPiste + 1;
        }
    }

    /**
     * Pour l'id de la base de données.
     * @param int $id
     * @return void
     */
    public function setID(int $id): void
    {
        $this->id_bdd = $id;
    }

    /**
     * Permet de définir si la liste est privée ou non.
     * @param bool $isPrivate True si la liste est privée, false sinon.
     * @return void
     */
    public function setIsPrivate(bool $isPrivate): void
    {
        $this->isPrivate = $isPrivate;
    }

    public function getLongueur(): int
    {
        return sizeof($this->liste);
    }
    /**
     * Une liste audio (classe AudioList) est décrite par un nom, un nombre de pistes, une durée totale
     * et un tableau contenant les pistes constituant la liste.
     * • créer la classe AudioList en programmant un constructeur qui reçoit en paramètre le
     * nom de la liste et un tableau optionnel de piste qui prendra la valeur [ ] s'il est omis. Le
     * constructeur initialise les propriétés, notamment en calculant le nombre de pistes et la durée
     * totale. La classe implante le getter magique __get() pour accéder aux valeurs des propriétés,
     * mais ne permet pas leur modification.
     */
}