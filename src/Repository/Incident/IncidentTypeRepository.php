<?php

namespace App\Repository\Incident;

use App\Entity\Incident\IncidentType;
use App\Entity\Security\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IncidentType|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncidentType|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncidentType[]    findAll()
 * @method IncidentType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncidentTypeRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncidentType::class);
    }

    // /**
    //  * @return IncidentType[] Returns an array of IncidentType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?IncidentType
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function createBaseAggregateQueryBuilder(User $user): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i')
                   ->leftJoin('i.actions', 'actions')
                   ->leftJoin('actions.statuses', 'statuses')
                   ->leftJoin('actions.status', 'status')
                   ->leftJoin('actions.responsibleUser', 'user')
                   ->leftJoin('actions.responsibleGroup', 'groups')
                   ->leftJoin('actions.createdBy', 'createdBy')
                   ->leftJoin('actions.actionTasks', 'actionTasks', Expr\Join::WITH, 'actionTasks.deleted <> true')
                   ->leftJoin('actionTasks.createdBy', 'actionTasksCreatedBy')
                   ->leftJoin('actionTasks.status', 'actionTasksStatus')
                   ->leftJoin('actionTasks.statuses', 'actionTasksStatuses')
                   ->leftJoin('actionTasks.type', 'actionTasksType')
                   ->leftJoin('i.categories', 'categories')
                   ->leftJoin('i.locations', 'locations')
                   ->leftJoin('i.status', 'incidentStatus')
                   ->leftJoin('i.statuses', 'incidentStatuses')
                   ->leftJoin('i.createdBy', 'incidentCreatedBy')
                   ->leftJoin('incidentStatus.createdBy', 'incidentStatusCreatedBy')
                   ->leftJoin('incidentStatuses.createdBy', 'incidentStatusesCreatedBy')
                   ->leftJoin('statuses.createdBy', 'statusesCreatedBy')
                   ->leftJoin('statuses.responsibleGroup', 'statusesResponsibleGroups')
                   ->leftJoin('statuses.responsibleUser', 'statusesResponsibleUser')
                   ->leftJoin('status.responsibleGroup', 'statusResponsibleGroups')
                   ->leftJoin('status.responsibleUser', 'statusResponsibleUser')
                   ->leftJoin('status.createdBy', 'statusCreatedBy')
                   ->leftJoin('actionTasksStatus.createdBy', 'actionTasksStatusCreatedBy')
                   ->leftJoin('actionTasksStatuses.createdBy', 'actionTasksStatusesCreatedBy')
                   ->addSelect('actions')
                   ->addSelect('categories')
                   ->addSelect('locations')
                   ->addSelect('incidentStatus')
                   ->addSelect('incidentStatuses')
                   ->addSelect('incidentCreatedBy')
                   ->addSelect('incidentStatusCreatedBy')
                   ->addSelect('incidentStatusesCreatedBy')
                   ->addSelect('actionTasks')
                   ->addSelect('statuses')
                   ->addSelect('status')
                   ->addSelect('statusesCreatedBy')
                   ->addSelect('statusesResponsibleGroups')
                   ->addSelect('statusesResponsibleUser')
                   ->addSelect('statusResponsibleGroups')
                   ->addSelect('statusResponsibleUser')
                   ->addSelect('statusCreatedBy')
                   ->addSelect('user')
                   ->addSelect('groups')
                   ->addSelect('createdBy')
                   ->addSelect('actionTasksCreatedBy')
                   ->addSelect('actionTasksStatus')
                   ->addSelect('actionTasksStatuses')
                   ->addSelect('actionTasksStatusCreatedBy')
                   ->addSelect('actionTasksStatusesCreatedBy')
                   ->andWhere('i.deleted != true')
                   ->andWhere('actions.deleted != true');

        return $qb;
    }
}
