<?php

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;
use Random\RandomException;

class PushRstTokenAction extends Action
{
    /**
     * @throws RandomException
     * @throws \Exception
     */
    public function execute(): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            // verif si le token est présent
            if (!empty($_GET['token']))
                $token = $_GET['token'];
            else
                return "La requête n'a pas but aboutir, token manquant.";

            // verifier si le token est valide
            if (DeefyRepository::getInstance()->checkRstPwdToken($token)) {
                $_SESSION['token'] = $token;
                return <<<HTML
                    <form method="POST" action="?action=pushtoken">
                        <label for="password">Nouveau mot de passe :</label>
                        <input type="password" id="password" name="password"
                               pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}"
                               title="Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial @$!%*?&."
                               required>
                        <button type="submit">Réinitialiser le mot de passe</button>
                    </form>
                HTML;
            } else
                return "Token invalide ou expiré.";
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['password']) && !empty($_SESSION['token'])) {

                $password = $_POST['password'];
                // Vérifier la longueur et la complexité du mot de passe côté SERVEUR
                if (strlen($password) < 8 ||
                    !preg_match('/[A-Z]/', $password) ||
                    !preg_match('/[a-z]/', $password) ||
                    !preg_match('/\d/', $password) ||
                    !preg_match('/[@$!%*?&]/', $password)) {

                    return "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
                }

                $userId = DeefyRepository::getInstance()->getUserIdByRstToken($_SESSION['token']);
                DeefyRepository::getInstance()->changePwd($userId, $_POST['password'], $_SESSION['token']);
                header("Location: index.php?action=login");

            } else {
                return "Le mot de passe ne peut pas être vide.";
            }
        }
        return "";
    }

}