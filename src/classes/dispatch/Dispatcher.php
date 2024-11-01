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
use iutnc\deefy\auth\AuthnProvider;
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
                case 'add-playlist':  // a verifier
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
                case 'delete-playlist':
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
        $user = AuthnProvider::getSignedInUser();
        $username = $user['nom'];
        $logInOrOut = $user['id'] == -1 ? "<a href='?action=login'>Connexion</a>" : "<a href='?action=logout'>Déconnexion</a>";

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
                <li id="acceuil"><a href="?action=default">Accueil</a></li>
                <li id="playlist"><a href="?action=playlists">Playlists</a></li>
                <li id="add"><a href="?action=add-playlist">Ajouter Playlist</a></li>
                <!--<li><a href="?action=add-track">Ajouter Piste</a></li>-->
                <li id="log">{$logInOrOut}</li>
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
}

