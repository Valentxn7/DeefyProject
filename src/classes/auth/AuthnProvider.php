<?php

namespace iutnc\deefy\auth;

use Exception;

class AuthnProvider
{
    /**
     * La méthode getSignedInUser() qui retourne l’utilisateur stocké en session. Si aucun utilisateur n’est authentifié,
     * elle déclenche une exception.
     *
     * @return array Les info utilisateurs stockées en session
     */
    public static function getSignedInUser(): array
    {
        if (empty($_SESSION['user_info'])) {
            //throw new Exception("Aucun utilisateur authentifié.");
            $_SESSION['user_info'] = [
                'id' => -1,
                'nom' => 'Voyageur',
                'role' => Authz::NO_USER
            ];
            return $_SESSION['user_info'];
        }
        return $_SESSION['user_info'];
    }
}