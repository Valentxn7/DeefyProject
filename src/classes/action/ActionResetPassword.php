<?php

namespace iutnc\deefy\action;

use DateTime;
use Exception;
use iutnc\deefy\repository\DeefyRepository;
use PHPMailer\PHPMailer\PHPMailer;
use Random\RandomException;

class ActionResetPassword extends Action
{
    public function execute(): string
    {
        if ($this->http_method == 'GET') {
            return $this->displayResetForm();
        } elseif ($this->http_method == 'POST') {
            try {
                return $this->processResetRequest();
            } catch (Exception $e) {
                return "Erreur lors de la réinitialisation du mot de passe : {$e->getMessage()}";
            }
        }
        return "";
    }

    private function displayResetForm(): string
    {
        // Formulaire HTML simple pour saisir l'adresse email
        return <<<HTML
        <form method="POST" action="?action=reset-password">
            <label for="email">Entrez votre adresse email :</label>
            <input type="email" id="email" name="email" required>
            <button type="submit">Envoyer</button>
        </form>
        HTML;
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     * @throws Exception
     */
    private function processResetRequest(): string
    {
        // Vérifier que l'email est présent dans les données POST
        if (empty($_POST['email'])) {
            return "Veuillez saisir votre adresse email.";
        }

        $userEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');

        // verif s'il existe
        $userExist = DeefyRepository::getInstance()->checkUser($userEmail);
        if ($userExist) {
            $userId = DeefyRepository::getInstance()->getIdUserByEmail($userEmail);

            DeefyRepository::getInstance()->insertResetPwdToken($userId, $token, $expiresAt);

            $resetLink = "http://localhost/dewweb/Deefy/index.php?action=pushtoken&token=" . $token;

            return $this->sendResetEmail($userEmail, $resetLink);
        } else {
            return "Aucun utilisateur trouvé avec cet email.";
        }
    }

    private function sendResetEmail($userEmail, $resetLink): string
    {
        $mail = new PHPMailer(true);

        $subject = 'Réinitialisation de votre mot de passe sur Deefy';

        $message = "
<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>$subject</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #1f1f1f;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #2c2c2c;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 26px;
            color: #00e676; /* Vert Deefy */
            margin: 0;
        }
        .content {
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.6;
            color: #dcdcdc;
        }
        
        .content p {
            color: #dcdcdc;
        }
        
        .cta-button {
            display: inline-block;
            background-color: #00e676;
            color: whitesmoke;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .cta-button:hover {
            background-color: #00c853;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            color: whitesmoke;
            margin-top: 40px;
        }
        .footer a {
            color: #00e676;
            text-decoration: none;
        }
        .footer p {
            color: whitesmoke;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Bonjour,</h1>
        </div>
        <div class='content'>
            <p>Nous avons bien reçu votre demande de réinitialisation de mot de passe sur <b>Deefy</b>.</p>
            <p>Si vous êtes bien à l'origine de cette demande, cliquez sur le bouton ci-dessous pour réinitialiser votre mot de passe.</p>
            <p><a href=$resetLink class='cta-button'>Réinitialiser mon mot de passe</a></p>
            <p>Ce lien est valide pendant <b>24 heures</b>. Si vous n'avez pas effectué cette demande, vous pouvez ignorer ce message en toute sécurité.</p>
        </div>
        <div class='footer'>
            <p>Si vous avez des questions, contactez notre support à <a href='mailto:deefy.sae@gmail.com'>deefy.sae@gmail.com</a>.</p>
            <p>Merci d'utiliser <b>Deefy</b> pour découvrir de nouvelles musiques et podcasts !</p>
        </div>
    </div>
</body>
</html>
";

        try {
            $conf = parse_ini_file("Config.email.ini");
            if ($conf === false)
                throw new Exception("Error reading email configuration file");

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->addCustomHeader('Content-Type', 'text/html; charset=UTF-8');
            $mail->Username = $conf['email'];
            $mail->Password = $conf['mdp']; // mdp grâve difficile a généré mais a save absooooooolument
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom($conf['email'], 'Deefy Support');
            $mail->addAddress($userEmail);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();
            return "Un email de réinitialisation a été envoyé à votre adresse.";
        } catch (Exception $e) {
            return "Erreur lors de l'envoi de l'email : {$e->getMessage()}";
        }
    }
}