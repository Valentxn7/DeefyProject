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

    public function render(int $selector, $index = null): string;

}