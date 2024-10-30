<?php

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;

class DefaultAction extends Action
{
    private static String $phrase = "Bienvenue, ";
    public function execute(): string
    {
        DeefyRepository::getInstance()->VerifToken();
        if (empty($_SESSION['user_info']['nom'])) {
            $username = "Voyageur";
        } else {
            $username = $_SESSION['user_info']['nom'];
        }

        return "<h3>" . DefaultAction::$phrase . $username . " !</h3>";
    }

    public static function setPhrase(String $p)
    {
        DefaultAction::$phrase = $p;
    }
}