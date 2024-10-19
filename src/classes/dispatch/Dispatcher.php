<?php

namespace iutnc\deefy\dispatch;

use iutnc\deefy\action\AddPlaylistAction;
use iutnc\deefy\action\AddPodcastTrackAction;
use iutnc\deefy\action\AddUserAction;
use iutnc\deefy\action\DefaultAction;
use iutnc\deefy\action\DestroyPlaylistAction;
use iutnc\deefy\action\DisplayPlaylistAction;

class Dispatcher
{
    protected string $action;

    public function __construct()
    {
        $this->action = $_GET['action'] ?? 'default';
    }

    public function run(): void
    {
        switch ($this->action) {
            case 'default':
                $act = new DefaultAction();
                break;
            case 'playlist':
                $act = new DisplayPlaylistAction();  // 3EME
                break;
            case 'add-playlist':
                $act = new AddPlaylistAction();  // 1ER
                break;
            case 'add-track':
                $act = new AddPodcastTrackAction();  // 2EME
                break;
            case 'add-user':
                $act = new AddUserAction();
                break;
            case 'destroy':
                $act = new DestroyPlaylistAction();
                break;
            default:
                $this->renderPage("Action inconnue");
                break;
        }
        if (isset($act))
            $this->renderPage($act->execute());
    }

    private function renderPage(string $html): void
    {
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
                    <h1>Bienvenue sur Mon Application</h1>
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

