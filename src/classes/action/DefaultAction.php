<?php

namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\repository\DeefyRepository;

class DefaultAction extends Action
{
    private static String $phrase = "Bienvenue, ";
    public function execute(): string
    {
        unset($_SESSION['playlist']);

        DeefyRepository::getInstance()->VerifToken();
        $user = AuthnProvider::getSignedInUser();
        $username = $user['nom'];

        return "<h3>" . DefaultAction::$phrase . $username . " !</h3>";
    }

    public static function setPhrase(String $p)
    {
        DefaultAction::$phrase = $p;
    }
}