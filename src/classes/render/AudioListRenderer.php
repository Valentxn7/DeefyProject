<?php

namespace iutnc\deefy\render;

use iutnc\deefy\render\Renderer;

class AudioListRenderer implements Renderer
{
    private \iutnc\deefy\audio\lists\AudioList $audioList;

    public function __construct(\iutnc\deefy\audio\lists\AudioList $al)
    {
        $this->audioList = $al;
    }


    public function render(int $inutile): string
    {
        if (sizeof($this->audioList->liste) === 0) {
            return "La playlist {$this->audioList->nom} est vide";
        } else {
            $cont = "<br> {$this->audioList->nom} <br>";
            for ($i = 0; $i < sizeof($this->audioList->liste); $i++) {
                /*echo "<br><br>";
                echo var_dump($this->audioList->liste[$i]);
                echo "<br><br>";*/
                $albrend = new \iutnc\deefy\render\PodcastRenderer($this->audioList->liste[$i]);
                $cont .= $albrend->render(Renderer::COMPACT);
            }
            $cont .= "<br>nb pistes: " . $this->audioList->nbpiste;
            $cont .= "<br>durée: " . $this->audioList->duree;
            return $cont;
        }
    }

}