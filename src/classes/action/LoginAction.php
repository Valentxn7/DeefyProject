<?php

namespace iutnc\deefy\action;

use Exception;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\repository\DeefyRepository;

/**
 * Classe LoginAction pour gérer la connexion des utilisateurs.
 * Elle permet de vérifier les informations d'identification et de connecter l'utilisateur.
 */
class LoginAction extends Action
{
    /**
     * @throws Exception
     */
    public function execute(): string
    {
        unset($_SESSION['playlist']);

        $user = AuthnProvider::getSignedInUser();
        if ($user['id'] != -1) {  // si l'utilisateur est déjà connecté et essaie de se reconnecter
            header("Location: index.php");
        }

        if ($this->http_method == "POST") {
            $rapport = $this->sanitize();
            if ($rapport != "OK")
                return $rapport;
            else {
                // Vérifier les informations d'identification
                $email = $_POST['email'];
                $password = $_POST['password'];

                if (!DeefyRepository::getInstance()->login($email, $password))
                    return "Adresse email ou mot de passe incorrect";
                else {
                    $ret = new DefaultAction();
                    $ret::setPhrase("Heureux de te revoir, ");
                    return $ret->execute();  // On revient à la page d'accueil
                    //return "Connexion réussie<br>Bienvenue, {$_SESSION['user_info']['nom']} !</h2>";
                }
            }
        } else {
            if ($this->http_method == "GET") {
                return $this->renderLoginForm();
            }
        }
        return "";  // Ne devrait jamais arriver car http method testé dans dispatcher
    }

    private
    function renderLoginForm(): string
    {
        return <<<HTML
            <h2>Connexion</h2><br>
            <form id="form-login" action="index.php?action=login" method="POST">
                <label for="email">Email : </label>
                <input type="email" id="email" name="email" required autocomplete="email"> <br>
                
                <label for="password">Mot de passe : </label>
                <input type="password" id="password" name="password" required> <br><br>
                
                <input type="submit" value="Se connecter"> <br>
            </form><br>
            <a class="add_user_button" href="?action=add-user">Créer un compte</a><br><br>
HTML;
    }

    private
    function sanitize(): string
    {
        if (empty($_POST['email']) || empty($_POST['password'])) {
            return "Tous les champs sont obligatoires.";
        }

        // Filtrer l'email
        if (!$_POST['email'] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            return "Adresse email invalide.";
        }
        $_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        // Même si cela est parfois non recommandé, je filtre tout de même le mot de passe
        // Et puis dans le meilleur des cas, cela crée une sécurité supplémentaire car me mdp est deja modifié avant le hashage
        // Et dans le meilleur des cas, je me protège contre toutes sortes d'attaques possibles et inimaginables
        $_POST['password'] = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        return "OK";
    }
}

