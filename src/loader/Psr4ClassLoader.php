<?php

namespace loader;

class Psr4ClassLoader
{
    public string $prefixe, $chemin;

    public function __construct(string $pref, string $chem)
    {
        $this->prefixe = $pref;
        $this->chemin = $chem;
    }

    public function loadClass(string $nom)
    {
        echo "<br><br>1er:  " . $nom;
        $filename = str_replace($this->prefixe, $this->chemin, $nom);
        echo "<br><br>2eme:  " . $filename;
        $filename = str_replace('\\', DIRECTORY_SEPARATOR, $filename) . '.php';
        echo "<br><br>3eme:  " . $filename;

        if (is_file($filename)) {
            echo "<br>----------- SUCCESS ------------ <br> ";
            require_once($filename);
        } else {
            echo "<br><br>Fichier non trouvé : " . $filename;
            echo "<br><br>IIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIIII<br>";
        }
    }

    public function register()
    {
        spl_autoload_register([$this, 'loadClass']);
    }
}