<?php

namespace iutnc\deefy\repository;

use iutnc\deefy\action\DefaultAction;
use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\dispatch\Dispatcher;
use iutnc\deefy\exception\AuthException;
use Random\RandomException;

class DeefyRepository
{
    private \PDO $pdo;
    private static ?DeefyRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf)
    {
        $this->pdo = new \PDO($conf['dsn'], $conf['user'], $conf['pass'],
            [
                \PDO::ATTR_PERSISTENT => true,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_STRINGIFY_FETCHES => false,
            ]);
        $this->pdo->prepare('SET NAMES \'UTF8\'')->execute();
    }

    /**
     * @throws \Exception
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::setConfig("Config.db.ini");  // FICHIER A CHANGER EN CAS DE CHANGEMENT DE CONFIGURATION
            self::$instance = new DeefyRepository(self::$config);
        }
        return self::$instance;
    }

    public static function setConfig(string $file)
    {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new \Exception("Error reading configuration file");
        }
        //$dsn = "$driver:$host;dbname=$database";
        $dsn = "{$conf['driver']}:host={$conf['host']};dbname={$conf['database']}";
        self::$config = ['dsn' => $dsn, 'user' => $conf['username'], 'pass' => $conf['password']];
    }

    public function findPlaylistById(int $id): Playlist
    {
        return new Playlist("TO DO");
    }

    public function saveEmptyPlaylist(Playlist $pl): Playlist
    {
        $query = "INSERT INTO playlist (nom) VALUES (:nom)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['nom' => $pl->nom]);
        $pl->setID($this->pdo->lastInsertId());
        return $pl;
    }

    public function savePodcastTrack(PodcastTrack $podcastTrack): bool
    {
        $query = "INSERT INTO track (titre, genre, duree, filename, type, auteur_podcast, date_podcast) 
                            VALUES(:titre, :genre, :duree, :nom_fich, :type, :auteur, :date)";
        $stmt = $this->pdo->prepare($query);
        if ($stmt->execute(['titre' => $podcastTrack->titre, 'genre' => $podcastTrack->genre, 'duree' => $podcastTrack->duree,
            'nom_fich' => $podcastTrack->nom_fich, 'type' => 'P', 'auteur' => $podcastTrack->auteur, 'date' => $podcastTrack->date])) {
            $podcastTrack->id_bdd = $this->pdo->lastInsertId(); // on devrait faire une fonction setID ...
            return true;
        } else
            return false;


    }

    /**
     * @return array de la bdd
     */
    public function allPlaylistRAW(): array
    {
        $query = "SELECT * FROM playlist";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return array d'objets Playlist
     */
    public function allPlaylistConverted(): array
    {
        $query = "SELECT * FROM playlist";
        $stmt = $this->pdo->query($query);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $playlists = [];
        foreach ($result as $row) {
            $pl = new Playlist($row['nom']);
            $pl->setID($row['id']);
            array_push($playlists, $pl);
        }

        return $playlists;
    }

    public function addUser(string $email, string $password, string $username): string
    {
        if ($this->checkUser($email)) {
            return 'a';  // already
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);  // 2^12 itérations de hashage

            $query = "INSERT INTO user (email, passwd, role, username) VALUES (:email, :password, :role, :username)";
            $stmt = $this->pdo->prepare($query);
            if ($stmt->execute(['email' => $email, 'password' => $hash, 'username' => $username, 'role' => 1])) {
                $this->AfterLoginUser($this->pdo->lastInsertId());  // atribution du token
                $_SESSION['user_info'] = ['id' => $this->pdo->lastInsertId(), 'nom' => $username];
                return "OK";
            } else
                return "KO";
        }
    }

    public function DeleteUser(): void
    {
        $query = "DELETE FROM user WHERE id = :id_user";
        $stmt = $this->pdo->prepare($query);
        if ($stmt->execute(['id_user' => $_SESSION['user_info']['id']])) {
            $this->logoutUser();
        }
        // TODO : Supprimer les playlists et les pistes associées
    }

    public function checkUser(string $email): bool
    {
        $query = "SELECT * FROM user WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email]);

        return (bool)$stmt->fetch(\PDO::FETCH_ASSOC); // reviens a $stmt->fetch(\PDO::FETCH_ASSOC) == false ? false : true;
    }

    /**
     * Ajouter une piste existante à une playlist existante.
     * @param PodcastTrack $podcastTrack
     * @return void
     */
    public function addPodcastToPlaylist(PodcastTrack $podcastTrack, Playlist $playlist): void
    {
        $query = "INSERT INTO playlist2track (id_pl, id_track) VALUES (:id_playlist, :id_track)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id_playlist' => $playlist->id_bdd, 'id_track' => $podcastTrack->id_bdd]);
    }

    /**
     * @throws RandomException
     */
    function AfterLoginUser($userId)
    {
        // on efface l'ancien token
        $this->DeleteActualToken();

        // on génère un nouveau token
        $token = bin2hex(random_bytes(120)); // CA FAIT LE DOUBLE EN TAILLE !!!!!
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 days')); // Expiration dans 30 jours

        $query = "INSERT INTO user_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);

        setcookie('remember_me', $token, time() + (10 * 24 * 60 * 60), '/', '');  // 10J
    }

    /**
     * Doit uniquement être appelée par l'extérieur pour verif !!
     * @return bool
     */
    function VerifToken() : bool
    {
        if (isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];

            $query = "SELECT user_id FROM user_tokens WHERE token = :token AND expires_at > NOW()";  // si token là et NON expiré
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['token' => $token]);
            $userId = $stmt->fetchColumn(0); // la seul et unique colonne retourné

            if ($userId) {  // si token est là et NON expiré
                $query = "SELECT username FROM user WHERE :userId = id";  // on remplie les infos de session
                $stmt = $this->pdo->prepare($query);
                $stmt->execute(['userId' => $userId]);
                $username = $stmt->fetchColumn();

                $_SESSION['user_info'] = ['id' => $userId, 'nom' => $username];
                return true;
            } else
                setcookie('remember_me', '', time() + 3, '/', ''); // Token expiré, supprime le cookie
        }
        return false;
    }

    function DeleteActualToken()
    {
        if (isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];

            $query = "DELETE FROM user_tokens WHERE token = :token";  // Supprime le token de la base de données
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['token' => $token]);

            setcookie('remember_me', '', time() + 1, '/', ''); // Supprime le cookie
        }
    }

    function logoutUser()
    {
        $this->DeleteActualToken();

        // Supprime la session
        session_unset();
        session_destroy();

        // redirige vers la page d'accueil
        $_GET['action'] = 'default';
        $dispatcher = new Dispatcher();
        $dispatcher->run();
    }

    public function login(string $email, string $passwd2check): bool
    {
        $query = "SELECT id, passwd, username from user where email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email]);

        if (!($result = $stmt->fetch(\PDO::FETCH_ASSOC))) {  // si on n'a pas de result = pas d'email
            return false;
        } else {
            if (!password_verify($passwd2check, $result['passwd']))
                return false;
            else{
                $_SESSION['user_info'] = ['id' => $result['id'], 'nom' => $result['username']];
                $this->AfterLoginUser($result['id']); // genère le token
                return true;
            }


        }
    }

}