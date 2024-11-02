<?php

namespace iutnc\deefy\action;

use Exception;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\repository\DeefyRepository;

/**
 * Créer la classe AddUserAction puis ajouter le cas $action = 'add-user' dans le sélecteur
 * du dispatcher.
 * Si la requête HTTP est de type GET, la page contient un formulaire avec les champs Nom, Email,
 * Âge, et un bouton Connexion. Utiliser les types d’input adaptés.
 * Sinon, si la requête HTTP est de type POST, la page affiche la valeur des champs dans le message :
 * "Nom:Toto, Email:toto@gmail.com, Age:22 ans".
 * Filtrer les valeurs des champs avant de les afficher pour empêcher l’attaque XSS ( filter_var() )
 */
class AddUserAction extends Action
{

    /**
     * @throws Exception
     */
    public function execute(): string
    {
        unset($_SESSION['playlist']);

        $user = AuthnProvider::getSignedInUser();
        if ($user['id'] != -1) {   // si l'utilisateur est déjà connecté et essaie de se créer un compte
            header("Location: index.php");
        }

        if ($this->http_method == "POST") {
            $rapport = $this->sanitize();
            if ($rapport != "OK") {
                return $rapport;
            } else {
                $rapport = DeefyRepository::getInstance()->addUser($_POST['email'], $_POST['password'], $_POST['name']);
                if ($rapport == "OK"){
                    // on revient a la page d'acceuille
                    header("Location: index.php");
                }

                else if ($rapport == 'a') { // already
                    return "Un compte existe déjà avec cet email.";
                } else {
                    return "Erreur lors de la création du compte.";
                }
            }



        } else if ($this->http_method == "GET") {

            return <<<HTML
                    <h2>Créer un compte</h2><br>
                    <form id="form-add-user" action="index.php?action=add-user" method="POST">
                        
                        <label for="name">Nom : </label>
                        <input type="text" id="name" name="name" placeholder="Votre nom" required autocomplete="name"> <br>
                        
                        <label for="email">Email : </label>
                        <input type="email" id="email" name="email" required autocomplete="email" title="Veuillez entrer une adresse email valide"> <br>
                        <label for="age">Âge : </label>
                        <input type="number" id="age" name="age" min="10" required> <br>
                        
                        <label for="password">Mot de passe : </label>
                        <!-- pattern=".{8,}"   mon pattern de base -->
                        <input type="password" id="password" name="password"
                               pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[/@$!%*?&])[A-Za-z\d@$/!%*?&]{8,}" 
                               title="Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial /@$!%*?&."
                               required > <br><br>
                        
                        <input type="submit" value="Valider"> <br>
                        <p>
                            Déjà un compte ? <a href="?action=login">Connectez-vous ici</a>
                        </p>
                    </form><br>

HTML;
        }
        return "";
    }


    public function sanitize(): string
    {
        if (empty($_POST['name']) || empty($_POST['email']) || empty($_POST['age']) || empty($_POST['password'])) {
            return "Tous les champs sont obligatoires.";
        }

        if (!$_POST['email'] = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            return "Adresse email invalide.";
        }
        $_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        if (!filter_var($_POST['age'], FILTER_VALIDATE_INT) || $_POST['age'] < 13) {
            return "Vous devez avoir au moins 13 ans pour vous créer un compte sans l'accord de vos parents.";
        }
        $_POST['age'] = filter_var($_POST['age'], FILTER_SANITIZE_NUMBER_INT);

        $_POST['name'] = filter_var($_POST['name'], FILTER_SANITIZE_SPECIAL_CHARS);

        $password = $_POST['password'];
        // Vérifier la longueur et la complexité du mot de passe côté SERVEUR
        if (strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/\d/', $password) ||
            !preg_match('/[@$!%*?&]/', $password)) {

            return "Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
        }
        $_POST['password'] = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        return "OK";
    }
}