<?php

namespace iutnc\deefy\auth;

use Exception;
use iutnc\deefy\exception\AccessControlException;
use iutnc\deefy\repository\DeefyRepository;

/**
 * La classe Authz qui permet de gérer l’autorisation.
 * Elle fournit des méthodes pour vérifier les droits d’accès.
 * Elle fournit des constantes pour les rôles.
 * Elle reçoit un tableau d’informations utilisateur en paramètre.
 */
class Authz
{
    /**
     * Constantes pour les rôles
     */
    public const ADMIN = 100;
    public const MODO = 50;
    public const USER = 10;
    public const NO_USER = 0;
    private array $user;

    public function __construct(array $user)
    {
        $this->user = $user;
    }

    /**
     * La méthode checkRole() qui reçoit un rôle attendu et vérifie que le rôle de l’utilisateur
     * authentifié est conforme.
     *
     * @param int $required
     * @throws AccessControlException
     */
    public function checkRole(int $required): void
    {
        if ($this->user['role'] < $required) {
            http_response_code(403);
            throw new AccessControlException("Droits insuffisants pour cette action.");
        }
    }

    /**
     * La méthode checkPlaylistOwner qui reçoit en paramètre un ID de playlist et vérifie qu'elle
     * appartient à l'utilisateur qui est connecté ou que l'utilisateur connecté au rôle ADMIN
     * (valeur 100 dans la table).
     *
     * @param int $playlistId
     * @throws AccessControlException
     * @throws Exception
     */
    public function checkPlaylistOwner(int $playlistId): void
    {
        $isOwner = DeefyRepository::getInstance()->isPlaylistOwner($this->user['id'], $playlistId);
        $isAdmin = $this->user['role'] == Authz::ADMIN;

        if (!$isOwner && !$isAdmin) {
            http_response_code(403);
            throw new AccessControlException("Accès non autorisé pour cette playlist.");
        }
    }

}