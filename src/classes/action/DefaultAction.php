<?php

namespace iutnc\deefy\action;

use Exception;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\repository\DeefyRepository;

/**
 * Classe DefaultAction.
 * Elle permet d'afficher un message de bienvenue personnalisé.
 */
class DefaultAction extends Action
{
    private static string $phrase = "Bienvenue, ";

    /**
     * @throws Exception
     */
    public function execute(): string
    {
        unset($_SESSION['playlist']);
        DeefyRepository::getInstance()->verifToken();
        $user = AuthnProvider::getSignedInUser();
        $username = $user['nom'];
        return "<h3>" . DefaultAction::$phrase . $username . " !</h3>";
    }

    public static function setPhrase(string $p): void
    {
        DefaultAction::$phrase = $p;
    }
}