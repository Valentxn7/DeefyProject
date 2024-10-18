<?php

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\lists\AudioList;

class Album extends AudioList
{
    protected string $artiste, $album, $date;

    public function __construct(string $name, array $arr, string $art, string $alb, string $dat)
    {
        parent::__construct($name, $arr);
        $this->artiste = $art;
        $this->album = $alb;
        $this->date = $dat;
    }

    public function set_artiste(string $art)
    {
        $this->artiste = $art;
    }

    public function set_album(string $alb)
    {
        $this->album = $alb;
    }

    public function set_date(string $dat)
    {
        $this->date = $dat;
    }

}