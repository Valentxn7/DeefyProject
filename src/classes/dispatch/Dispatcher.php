<?php

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action\AddAlbumTrackAction;
use iutnc\deefy\action\AddPlaylistAction;
use iutnc\deefy\action\AddPodcastTrackAction;
use iutnc\deefy\action\AddUserAction;
use iutnc\deefy\action\AllPlaylistAction;
use iutnc\deefy\action\DefaultAction;
use iutnc\deefy\action\DestroyPlaylistAction;
use iutnc\deefy\action\DisplayPlaylistAction;
use iutnc\deefy\action\LoginAction;
use iutnc\deefy\repository\DeefyRepository;

class Dispatcher
{
    protected string $action;

    public function __construct()
    {
        $this->action = $_GET['action'] ?? 'default';
    }

    /**
     * @throws \Exception
     */
    public function run(): void
    {
        if (($_SERVER['REQUEST_METHOD'] !== "POST") && ($_SERVER['REQUEST_METHOD'] !== "GET") )
            $this->renderPage("Erreur 418 : I'm a teapot");
        else {
            switch ($this->action) {
                case 'default':
                    $act = new DefaultAction();
                    break;
                case 'playlists':
                    $act = new AllPlaylistAction();
                    break;
                case 'add-playlist':
                    $act = new AddPlaylistAction();
                    break;
                case 'add-Albumtrack':
                    $act = new AddAlbumTrackAction();
                    break;
                case 'add-Podcasttrack':
                    $act = new AddPodcastTrackAction();
                    break;
                case 'add-user':
                    $act = new AddUserAction();
                    break;
                case 'login':
                    $act = new LoginAction();
                    break;
                case 'logout':
                    DeefyRepository::getInstance()->logoutUser();
                    header("Location: TD12.php?action=default");
                    break;
                case 'destroy':
                    $act = new DestroyPlaylistAction();
                    break;
                case 'display-playlist':
                    $act = new DisplayPlaylistAction();
                    break;
                default:
                    $this->renderPage("Action inconnue");
                    break;
            }
            if (isset($act))
                $this->renderPage($act->execute());
        }
    }


    private function renderPage(string $html): void
    {
        DeefyRepository::getInstance()->VerifToken();
        if (empty($_SESSION['user_info']['nom'])) {
            $username = "Invité";
            $logInOrOut = "<li><a href='?action=login'>Connexion</a></li>";
        } else {
            $username = $_SESSION['user_info']['nom'];
            $logInOrOut = "<li><a href='?action=logout'>Déconnexion</a></li>";
        }

        $ret = <<<HTML
    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deefy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">
            <h1>Deefy</h1>
        </div>
        <nav>
            <ul>
                <li><a href="?action=default">Accueil</a></li>
                <li><a href="?action=playlists">Playlists</a></li>
                <li><a href="?action=add-playlist">Ajouter Playlist</a></li>
                <!--<li><a href="?action=add-track">Ajouter Piste</a></li>-->
                {$logInOrOut}
            </ul>
        </nav>
    </header>
    <main>
        <div class="content">
            {$html}
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Deefy. Tous droits réservés.</p>
    </footer>
</body>
</html>
HTML;
        echo $ret;
    }


    private function renderPage_old(string $html): void
    {
        if (empty($_SESSION['user_info']['nom'])) {
            $username = "unknow ! ";
        } else {
            $username = " " . $_SESSION['user_info']['nom'];
        }
        DeefyRepository::getInstance()->VerifToken();
        $ret = <<<HTML
            <!DOCTYPE html>
            <html lang='fr'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Deefy</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 0;
                        background-color: #f4f4f4;
                    }
                    header {
                        background-color: #4CAF50;
                        color: white;
                        padding: 10px 20px;
                        text-align: center;
                    }
                    footer {
                        background-color: #4CAF50;
                        color: white;
                        text-align: center;
                        padding: 10px 0;
                        position: fixed;
                        bottom: 0;
                        width: 100%;
                    }
                    .content {
                        padding: 20px;
                        text-align: center;
                        min-height: calc(100vh - 100px); /* Height minus header and footer */
                    }
                </style>
            </head>
            <body>
                <header>
                    <h1>Bienvenue sur Deefy $username</h1>
                    <nav>
                        <a href='?action=default' style='color: white; margin: 0 15px;'>Accueil</a>
                        <a href='?action=playlist' style='color: white; margin: 0 15px;'>Afficher Playlists</a>
                        <a href='?action=add-playlist' style='color: white; margin: 0 15px;'>Ajouter Playlist</a>
                        <a href='?action=add-track' style='color: white; margin: 0 15px;'>Ajouter Piste</a>
                    </nav>
                </header>
                <div class='content'>
                    $html
                </div>
                <footer>
                    <p>&copy; <?php echo date("Y"); ?> Mon Application. Tous droits réservés.</p>
                </footer>
            </body>
            </html>
HTML;
        echo $ret;
    }

    /**
     * private function renderPage(string $html): void
     * {
     * $ret = "<!DOCTYPE html>
    * <html lang='fr'>
     * <head>
     * <meta charset='UTF-8'>
     * </head>
     * <body>" . $html . "</body>
     * </html>";
    * echo $ret;
     * }
     **/

}

