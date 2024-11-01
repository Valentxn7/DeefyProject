<?php

namespace iutnc\deefy\repository;

use Exception;
use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\dispatch\Dispatcher;
use PDO;
use Random\RandomException;

class DeefyRepository
{
    private PDO $pdo;
    private static ?DeefyRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf)
    {
        $this->pdo = new PDO($conf['dsn'], $conf['user'], $conf['pass'],
            [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ]);
        $this->pdo->prepare('SET NAMES \'UTF8\'')->execute();
    }

    /**
     * @throws Exception
     */
    public static function getInstance(): ?DeefyRepository
    {
        if (is_null(self::$instance)) {
            self::setConfig("Config.db.ini");  // FICHIER A CHANGER EN CAS DE CHANGEMENT DE CONFIGURATION
            self::$instance = new DeefyRepository(self::$config);
        }
        return self::$instance;
    }

    /**
     * @throws Exception
     */
    public static function setConfig(string $file): void
    {
        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new Exception("Error reading configuration file");
        }
        //$dsn = "$driver:$host;dbname=$database";
        $dsn = "{$conf['driver']}:host={$conf['host']};dbname={$conf['database']}";
        self::$config = ['dsn' => $dsn, 'user' => $conf['username'], 'pass' => $conf['password']];
    }

    /**
     * Programmer la méthode findPlaylistById() qui reçoit un entier identifiant une playlist,
     * la récupère dans la base, charge les pistes associées et retourne un objet de type Playlist complet.
     * @param int $id
     * @return Playlist
     * @throws Exception
     */
    public function findPlaylistById(int $id): Playlist
    {
        // READ !!
        // le probème ici c'est que le sujet de base a une base NF1 bien pas belle mais avantageuse pour ce cas...
        // remettre en NF3 est bien plus propre pour tout les autres requete mais pas pour celle-ci ...
        // on a soit besoin de faire BEAUCOUP de requete pour récupérer les pistes associées dans chaque table
        // soit faire une giga giga requetes avec des LEFT JOIN et tout le tralala pour récupérer tout en une fois donc refaire la base NF1 a contre coeur
        // ici on a choisi la 2eme solution pour des raisons de performance car faire 10000 requetes pour 10000 pistes c'est pas ouf
        // lets code

        // on recup d'abord le nom de la playlist pour sa création
        //echo "<br><br>3: " . var_dump($id);
        $query = "SELECT nom, isPrivate FROM playlist where id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$res) {
            throw new Exception("La playlist n'a pas été trouvée");
        }
        $pl = new Playlist($res['nom']);
        $pl->setID($id);
        $pl->setIsPrivate($res['isPrivate']);

        // merci bcp a coalesce qui nous sauve la vie (en gros il fusionne les 2 tables en une seule)
        $query = "
        SELECT pt.type, pt.id_track, pt.no_piste_dans_liste,
               COALESCE(m.titre, pod.titre) AS titre,
               COALESCE(m.genre, pod.genre) AS genre,
               COALESCE(m.duree, pod.duree) AS duree,
               COALESCE(m.filename, pod.filename) AS filename,
               COALESCE(m.artiste, pod.auteur) AS artiste_auteur,
               m.album, m.annee, m.numero, DATE_FORMAT(pod.date, '%Y-%m-%d') as date
        FROM playlist2track pt
        LEFT JOIN musique m ON pt.id_track = m.id AND pt.type = 'M'
        LEFT JOIN podcast pod ON pt.id_track = pod.id AND pt.type = 'P'
        WHERE pt.id_pl = :id
        ORDER BY pt.no_piste_dans_liste
    ";
        // voila le carnage (ou la bénédictions idk)

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($res as $row) {
            if ($row['type'] == 'M') { // si c une musique
                $mus = new AlbumTrack($row['titre'], $row['filename'], $row['album'], $row['numero']);
                $mus->id_bdd = $row['id_track'];
                $mus->duree = $row['duree'];
                $mus->artiste = $row['artiste_auteur'];
                $mus->genre = $row['genre'];
                $mus->annee = $row['annee'];
                $mus->numero = $row['numero'];
                $mus->album = $row['album'];
                $pl->ajouter($mus);
            } elseif ($row['type'] == 'P') { // si c un podcast
                $pod = new PodcastTrack($row['titre'], $row['filename']);
                $pod->id_bdd = $row['id_track'];
                $pod->duree = $row['duree'];
                $pod->auteur = $row['artiste_auteur'];
                $pod->date = $row['date'];
                $pod->genre = $row['genre'];
                $pl->ajouter($pod);
            }
        }
        return $pl;
//        $query = "SELECT * FROM playlist2track WHERE id_pl = :idPl";  // on récupère les pistes associées
//        $stmt = $this->pdo->prepare($query);
//        $stmt->execute(['idPl' => $id]);
//
//        while (!$res = $stmt->fetch(\PDO::FETCH_ASSOC)) {
//            if ($res['type'] == 'P') {
//                $query = "SELECT * FROM podcast WHERE id = :id";
//                $stmt = $this->pdo->prepare($query);
//                $stmt->execute(['id' => $res['id_track']]);
//                $res = $stmt->fetch(\PDO::FETCH_ASSOC);
//                $pod = new PodcastTrack($res['titre'], $res['filename']);
//                $pod->id_bdd = $res['id'];
//                $pod->duree = $res['duree'];
//                $pod->auteur = $res['auteur_podcast'];
//                $pod->date = $res['date_podcast'];
//                $pod->genre = $res['genre'];
//                $pl->ajouter($pod);
//            } else {
//                if ($res['type'] == 'M') {
//                    $query = "SELECT * FROM musique WHERE id = :id";
//                    $stmt = $this->pdo->prepare($query);
//                    $stmt->execute(['id' => $res['id_track']]);
//                    $res = $stmt->fetch(\PDO::FETCH_ASSOC);
//                    $mus = new AlbumTrack($res['titre'], $res['filename'], $res['album'], $res['numero']);
//                    $mus->id_bdd = $res['id'];
//                    $mus->duree = $res['duree'];
//                    $mus->artiste = $res['artiste'];
//                    $mus->genre = $res['genre'];
//                    $pl->ajouter($mus);
//                }
//            }
//        }
//
//        return $pl;
    }

    public function saveEmptyPlaylist(Playlist $pl): void
    {
        // on créer la playlist
        $query = "INSERT INTO playlist (nom, isPrivate) VALUES (:nom, :isPrivate)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['nom' => $pl->nom, 'isPrivate' => $pl->isPrivate]);
        $pl->setID($this->pdo->lastInsertId());

        // on l'associe a l'utilisateur
        $query = "INSERT INTO user2playlist (id_user, id_pl) VALUES (:id_user, :id_playlist)";
        $stmt = $this->pdo->prepare($query);
        $user = AuthnProvider::getSignedInUser();
        $stmt->execute(['id_user' => $user['id'], 'id_playlist' => $pl->id_bdd]);

        $_SESSION['playlist'] = $pl;
    }

    public function savePodcastTrack(PodcastTrack $podcastTrack): bool
    {
        // on save le podcast
        $query = "INSERT INTO podcast (titre, genre, duree, filename, auteur, date)
                            VALUES(:titre, :genre, :duree, :nom_fich, :auteur, :date)";
        $stmt = $this->pdo->prepare($query);
        if ($stmt->execute(['titre' => $podcastTrack->titre, 'genre' => $podcastTrack->genre, 'duree' => $podcastTrack->duree,
            'nom_fich' => $podcastTrack->nom_fich, 'auteur' => $podcastTrack->auteur, 'date' => $podcastTrack->date])) {
            $podcastTrack->id_bdd = $this->pdo->lastInsertId(); // on devrait faire une fonction setID ...
            return true;
        } else
            return false;
    }

    public function saveMusiqueTrack(AlbumTrack $musique): bool
    {
        // on save le podcast
        $query = "INSERT INTO musique (titre, genre, duree, filename, artiste, album, annee, numero)
                                VALUES(:titre, :genre, :duree, :nom_fich, :artiste, :album, :annee, :numero)";
        $stmt = $this->pdo->prepare($query);
        if ($stmt->execute(['titre' => $musique->titre, 'genre' => $musique->genre, 'duree' => $musique->duree,
            'nom_fich' => $musique->nom_fich, 'artiste' => $musique->artiste, 'annee' => $musique->annee,
            'album' => $musique->album, 'numero' => $musique->numero])) {
            $musique->id_bdd = $this->pdo->lastInsertId(); // on devrait faire une fonction setID ...
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return array d'objets Playlist
     */
    public function allPlaylistConverted(): array
    {
        $query = "SELECT * FROM playlist";
        $stmt = $this->pdo->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $playlists = [];
        foreach ($result as $row) {
            $pl = new Playlist($row['nom']);
            $pl->setID($row['id']);
            array_push($playlists, $pl);
        }

        return $playlists;
    }

    public function allPlaylistID(): array
    {
        $query = "SELECT id FROM playlist";
        $stmt = $this->pdo->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $liste_id = [];
        foreach ($result as $row) {
            array_push($liste_id, $row['id']);
        }
        return $liste_id;
    }

    /**
     * @throws RandomException
     */
    public function addUser(string $email, string $password, string $username): string
    {
        if ($this->checkUser($email)) {
            return 'a';  // already
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);  // 2^12 itérations de hashage

            $query = "INSERT INTO user (email, passwd, role, username) VALUES (:email, :password, :role, :username)";
            $stmt = $this->pdo->prepare($query);
            if ($stmt->execute(['email' => $email, 'password' => $hash, 'username' => $username, 'role' => Authz::USER])) {
                $this->AfterLoginUser($this->pdo->lastInsertId());  // atribution du token
                $_SESSION['user_info'] = [
                    'id' => $this->pdo->lastInsertId(),
                    'nom' => $username,
                    'role' => Authz::USER];
                return "OK";
            } else
                return "KO";
        }
    }

    /**
     * @throws Exception
     */
    public function DeleteUser(): void
    {
        $user = AuthnProvider::getSignedInUser();
        $query = "DELETE FROM user WHERE id = :id_user";
        $stmt = $this->pdo->prepare($query);
        if ($stmt->execute(['id_user' => $user['id']])) {
            $this->logoutUser();
        }
        // TODO : Supprimer les playlists et les pistes associées
    }

    public function checkUser(string $email): bool
    {
        $query = "SELECT * FROM user WHERE email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email]);

        return (bool)$stmt->fetch(PDO::FETCH_ASSOC); // reviens a $stmt->fetch(\PDO::FETCH_ASSOC) == false ? false : true;
    }

    /**
     * Ajouter une piste existante à une playlist existante.
     * @param PodcastTrack $podcastTrack
     * @param Playlist $pl
     * @return void
     * @throws Exception
     */
    public function addPodcastToPlaylist(PodcastTrack $podcastTrack, Playlist $pl): void
    {
        // on ajoute d'abord le podcast a la base
        if (DeefyRepository::getInstance()->savePodcastTrack($podcastTrack)) {
            $query = "INSERT INTO playlist2track (id_pl, id_track, no_piste_dans_liste, type) 
                                          VALUES (:id_playlist, :id_track, :no_piste, :type)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['id_playlist' => $pl->id_bdd, 'id_track' => $podcastTrack->id_bdd,
                'no_piste' => $pl->getLongueur() + 1, 'type' => 'P']);
        } else
            throw new Exception("Erreur lors de l'ajout du podcast à la playlist");
    }

    /**
     * @throws Exception
     */
    public function addMusiqueToPlaylist(AlbumTrack $musique, Playlist $pl): void
    {
        // on ajoute d'abord le podcast a la base
        if (DeefyRepository::getInstance()->saveMusiqueTrack($musique)) {
            $query = "INSERT INTO playlist2track (id_pl, id_track, no_piste_dans_liste, type) 
                                          VALUES (:id_playlist, :id_track, :no_piste, :type)";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['id_playlist' => $pl->id_bdd, 'id_track' => $musique->id_bdd,
                'no_piste' => $pl->getLongueur() + 1, 'type' => 'M']);
        } else
            throw new Exception("Erreur lors de l'ajout de la musique à la playlist");
    }

    /**
     * @throws Exception
     */
    public function deletePlaylist(int $pl_id): void
    {
        // Supprime les fichiers audio sur le serv
        $query = "SELECT m.filename, p2t.type 
                    FROM playlist2track AS p2t
                    JOIN musique AS m ON m.id = p2t.id_track AND p2t.type = 'M'
                    WHERE p2t.id_pl = :playlist_id1
                    UNION
                    SELECT p.filename, p2t.type 
                    FROM playlist2track AS p2t
                    JOIN podcast AS p ON p.id = p2t.id_track AND p2t.type = 'P'
                    WHERE p2t.id_pl = :playlist_id2";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['playlist_id1' => $pl_id, 'playlist_id2' => $pl_id]);

        while ($row = $stmt->fetch()) {
            $server_path = realpath($_SERVER['DOCUMENT_ROOT'] . "\dewweb\Deefy\sound\\");  // A CHANGER EN CAS DE CHANGEMENT DE CHEMIN
            echo "<br><br>1: " . $row['filename'];
            $filepath = str_replace("http://localhost\\dewweb\\Deefy\\sound\\", $server_path . "\\", $row['filename']);
            echo "<br><br>2: " . $filepath;
            if (file_exists($filepath)) {
                unlink($filepath);  // on supprime le fichier
            } else {
                throw new Exception("Erreur lors de la suppression des fichiers audio");
            }
        }

        // Supprime dans musique
        $query = "DELETE FROM musique WHERE id IN (SELECT id_track FROM playlist2track WHERE id_pl = :playlist_id AND type = 'M')";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['playlist_id' => $pl_id]);


        // Supprime dans podcast
        $query = "DELETE FROM podcast WHERE id IN (SELECT id_track FROM playlist2track WHERE id_pl = :playlist_id AND type = 'P')";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['playlist_id' => $pl_id]);


        // Supprime dans playlist2track
        $query = "DELETE FROM playlist2track WHERE id_pl = :playlist_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['playlist_id' => $pl_id]);


        // Supprime dans user2playlist
        $deleteUser2Playlist = "DELETE FROM user2playlist WHERE id_pl = :playlist_id";
        $stmtUser2Playlist = $this->pdo->prepare($deleteUser2Playlist);
        $stmtUser2Playlist->execute(['playlist_id' => $pl_id]);


        // Supprime la playlist
        $query = "DELETE FROM playlist WHERE id = :playlist_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['playlist_id' => $pl_id]);
    }


    /**
     * @throws RandomException
     */
    function AfterLoginUser($userId): void
    {
        // on efface l'ancien token
        $this->DeleteActualToken($userId);

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
    function VerifToken(): bool
    {
        if (isset($_COOKIE['remember_me'])) {
            $token = $_COOKIE['remember_me'];

            $query = "SELECT user_id FROM user_tokens WHERE token = :token AND expires_at > NOW()";  // si token là et NON expiré
            $stmt = $this->pdo->prepare($query);
            $stmt->execute(['token' => $token]);
            $userId = $stmt->fetchColumn(); // la seul et unique colonne retourné

            if ($userId) {  // si token est là et NON expiré
                $query = "SELECT username, role FROM user WHERE :userId = id";  // on remplie les infos de session
                $stmt = $this->pdo->prepare($query);
                $stmt->execute(['userId' => $userId]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                $_SESSION['user_info'] = ['id' => $userId, 'nom' => $result['username'], 'role' => $result['role']];
                return true;
            } else
                setcookie('remember_me', '', time() + 3, '/', ''); // Token expiré, supprime le cookie
        }
        return false;
    }

    function DeleteActualToken($user_id): void
    {
        $query = "DELETE FROM user_tokens WHERE user_id = :userid";  // Supprime le token de la base de données
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['userid' => $user_id]);

        setcookie('remember_me', '', time() + 1, '/', ''); // Supprime le cookie
    }

    /**
     * @throws Exception
     */
    function logoutUser(): void
    {
        $user = AuthnProvider::getSignedInUser();
        if ($user['id'] != -1) {
            $this->DeleteActualToken($user['id']);

            DeefyRepository::getInstance()->DeleteActualToken($user['id']);

            // Supprime la session
            session_unset();
            session_destroy();

            // redirige vers la page d'accueil
            $_GET['action'] = 'default';
            $dispatcher = new Dispatcher();
            $dispatcher->run();
        }
    }

    /**
     * @throws RandomException
     */
    public function login(string $email, string $passwd2check): bool
    {
        $query = "SELECT id, passwd, username, role from user where email = :email";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['email' => $email]);

        if (!($result = $stmt->fetch(PDO::FETCH_ASSOC))) {  // si on n'a pas de result = pas d'email
            return false;
        } else {
            if (!password_verify($passwd2check, $result['passwd']))
                return false;
            else {
                $_SESSION['user_info'] = ['id' => $result['id'], 'nom' => $result['username'], 'role' => $result['role']];
                $this->AfterLoginUser($result['id']); // genère le token
                return true;
            }


        }
    }

    public function isPlaylistOwner(int $id_user, int $playlistId): bool
    {
        $query = "SELECT COUNT(*) FROM user2playlist WHERE id_user = :id_user AND id_pl = :id_playlist";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id_user' => $id_user, 'id_playlist' => $playlistId]);

        return $stmt->fetchColumn() > 0;
    }

}