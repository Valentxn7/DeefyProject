<?php

namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;
/**
 * Classe mère servant d'héritage à toute classe comportant du son
 */
abstract class AudioTrack
{
    protected string $titre, $genre, $nom_fich;
    protected int $duree;
    protected int $id_bdd;

    public function __construct(string $titre_piste, string $path)
    {
        $this->titre = $titre_piste;
        $this->nom_fich = $path;
    }

    public function __toString(): string
    {
        return json_encode($this);
    }

    /**
     * @throws \Exception
     */
    public function __get(string $name): string
    {
        if (property_exists($this, $name)) return $this->$name;
        else
            throw new \Exception("invalid property : " . $name);
    }

    /**
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        try {
            $valFixe = ["titre", "nom_fich"];
            if (!in_array($name, $valFixe)) {
                if ($name === "duree" && $value < 0)
                    throw new InvalidPropertyValueException("La durée ne peut pas être négative");
                else
                    if (property_exists($this, $name))
                        $this->$name = $value;
                    else
                        throw new InvalidPropertyNameException("La variable n'existe pas : " . $name);
            } else {
                throw new \Exception("Impossible de changer des valeurs fixes.");
            }
        } catch (\Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
            echo "Trace : " . $e->getTraceAsString() . "\n";
        }

    }

}