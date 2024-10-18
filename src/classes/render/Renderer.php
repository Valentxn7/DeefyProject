<?php

namespace iutnc\deefy\render;

interface Renderer
{
    const COMPACT = "1";
    const INTER = "2";
    const LONG = '3';

    public function render(int $selector): string;

}