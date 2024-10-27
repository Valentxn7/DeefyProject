<?php

namespace iutnc\deefy\action;

use iutnc\deefy\action\Action;
use iutnc\deefy\repository\DeefyRepository;

/**
 * Classe LoginAction pour gérer la connexion des utilisateurs.
 */
class LoginAction extends Action
{
    /**
     * @throws \Exception
     */
    public function execute(): string
    {
        if ($this->http_method == "POST") {
            $rapport = $this->sanitize();
            if ($rapport != "OK") {
                return $rapport;
            } else {
                // Vérifier les informations d'identification
                $email = $_POST['email'];
                $password = $_POST['password'];

                $user = DeefyRepository::getInstance()->findUser($email);

                if ($user && password_verify($password, $user['passwd'])) {
                    // Si les informations d'identification sont correctes
                    $_SESSION['user_info'] = [
                        'id' => $user['id'],
                        'nom' => $user['username']
                    ];
                    return "<h2>Connexion réussie</h2><br>Bienvenue, {$user['username']}!";
                } else {
                    return "Identifiants invalides.";
                }
            }
        } else if ($this->http_method == "GET") {
            // Afficher le formulaire de connexion
            return $this->renderLoginForm();
        }
    }

    public function renderLoginForm(): string
    {
        return <<<HTML
            <h2>Connexion</h2><br>
            <form id="form-login" action="TD12.php?action=login" method="POST">
                <label for="email">Email : </label>
                <input type="email" id="email" name="email" required autocomplete="email" title="Veuillez entrer une adresse email valide"> <br>
                
                <label for="password">Mot de passe : </label>
                <input type="password" id="password" name="password" required> <br><br>
                
                <input type="submit" value="Se connecter"> <br>
            </form><br><br>
HTML;
    }

    public function sanitize(): string
    {
        if (!isset($_POST['email']) || !isset($_POST['password'])) {
            return "Tous les champs sont obligatoires.";
        }

        if (is_null($_POST['email']) || is_null($_POST['password'])) {
            return "Tous les champs sont obligatoires.";
        }

        // Filtrer l'email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            return "Adresse email invalide.";
        }
        $_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        // Filtrer le mot de passe
        $_POST['password'] = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

        return "OK";
    }
}
