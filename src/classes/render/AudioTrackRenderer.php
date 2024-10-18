<?php

namespace iutnc\deefy\render;

use iutnc\deefy\render\Renderer;

abstract class AudioTrackRenderer implements Renderer
{
    abstract function render(int $selector): string;
}