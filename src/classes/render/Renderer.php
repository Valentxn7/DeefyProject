<?php

namespace iutnc\deefy\render;

/**
 * Interface Renderer.
 * Elle permet de représenter un rendu.
 */
interface Renderer
{
    const COMPACT = "1";
    const LONG = '3';

    /**
     * @param int $selector 1 for long, 2 for preview
     * @param bool $isPrivate vrai si la playlist appartient à un user
     * @param $index index de la piste (pour la suppression)
     * @return string le rendu
     */
    public function render(int $selector, bool $isPrivate, $index = null): string;

}