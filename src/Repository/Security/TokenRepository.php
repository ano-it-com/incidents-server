<?php

namespace App\Repository\Security;

use App\Entity\Security\Token;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Token|null find($id, $lockMode = null, $lockVersion = null)
 * @method Token|null findOneBy(array $criteria, array $orderBy = null)
 * @method Token[]    findAll()
 * @method Token[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Token::class);
    }

    public function findAllExpired(): array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('token')
            ->from(Token::class, 'token')
            ->andWhere('token.expiresAt < :currentTime')
            ->setParameter('currentTime', new DateTime());

        return $qb->getQuery()->execute();
    }
}
