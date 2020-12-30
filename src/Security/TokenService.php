<?php


namespace App\Security;


use App\Entity\Security\Token;
use App\Entity\Security\User;
use App\Repository\Security\TokenRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class TokenService
{
    public const TOKEN_PREFIX = 'Bearer';
    private TokenRepository $tokenRepository;
    private EntityManagerInterface $em;

    public function __construct(TokenRepository $tokenRepository, EntityManagerInterface $em)
    {
        $this->tokenRepository = $tokenRepository;
        $this->em = $em;
    }

    public function getToken($token): ?Token
    {
        return $this->tokenRepository->findOneBy(['token' => $token]);
    }

    public function invalidateTokens(): int
    {
        $tokens = $this->tokenRepository->findAllExpired();
        foreach ($tokens as $token) {
            $this->invalidateToken($token);
        }
        return count($tokens);
    }

    public function invalidateToken(Token $token)
    {
        $this->em->remove($token);
        $this->em->flush();
    }

    public function generateToken(User $user): string
    {
        $apiToken = (new Token())
            ->setExpiresAt(new DateTime('+1 day'))
            ->setToken(bin2hex(random_bytes(60)))
            ->setUser($user);

        $this->em->persist($apiToken);
        $this->em->flush();

        return self::TOKEN_PREFIX . " {$apiToken->getToken()}";
    }
}