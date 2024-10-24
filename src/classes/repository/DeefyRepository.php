<?php

namespace iutnc\deefy\repository;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\audio\tracks\PodcastTrack;

class DeefyRepository
{
    private \PDO $pdo;
    private static ?DeefyRepository $instance = null;
    private static array $config = [];

    private function __construct(array $conf)
    {
        $this->pdo = new \PDO($conf['dsn'], $conf['user'], $conf['pass'],
            [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
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

    public function allPlaylistRAW(): array
    {
        $query = "SELECT * FROM playlist";
        $stmt = $this->pdo->query($query);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

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
        if ($this->findUser($email)) {
            return "already";
        } else {
            $query = "INSERT INTO user (email, passwd, role, username) VALUES (:email, :password, :role, :username)";
            $stmt = $this->pdo->prepare($query);
            if ($stmt->execute(['email' => $email, 'password' => $password, 'username' => $username, 'role' => 1]))
                return "OK";
            else
                return "KO";
        }
    }

    public function findUser(string $email): bool
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
        $query = "INSERT INTO playlist_track (id_playlist, id_track) VALUES (:id_playlist, :id_track)";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['id_playlist' => $playlist->id_bdd, 'id_track' => $podcastTrack->id_bdd]);
    }
}