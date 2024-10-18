<?php

namespace iutnc\deefy\action;

class DestroyPlaylistAction extends Action
{
    public function execute(): string
    {
        unset($_SESSION['playlist']);
        return "Playlist détruite";
    }
}