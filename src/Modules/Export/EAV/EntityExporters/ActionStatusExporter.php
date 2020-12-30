<?php

namespace App\Modules\Export\EAV\EntityExporters;

use ANOITCOM\EAVBundle\EAV\ORM\EntityManager\EAVEntityManagerInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRelationTypeRepository;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVEntityRepositoryInterface;
use ANOITCOM\EAVBundle\EAV\ORM\Repository\EAVTypeRepositoryInterface;
use App\Domain\Action\ActionStatusInterface;
use App\Domain\Action\ActionStatusLocator;
use App\Entity\Incident\Action\ActionStatus;
use App\Modules\Export\EAV\EAVExporterInterface;
use App\Repository\Incident\Action\ActionStatusRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

class ActionStatusExporter extends AbstractEAVExporter implements EAVExporterInterface
{
    private ActionStatusRepository $actionStatusRepository;

    private ActionStatusLocator $actionStatusLocator;

    public function __construct(
        EAVEntityManagerInterface $eavEm,
        EntityManagerInterface $em,
        EAVEntityRepositoryInterface $entityRepository,
        EAVTypeRepositoryInterface $typeRepository,
        EAVEntityRelationRepository $entityRelationRepository,
        EAVEntityRelationTypeRepository $entityRelationTypeRepository,
        ActionStatusRepository $actionStatusRepository,
        ActionStatusLocator $actionStatusLocator
    ) {
        parent::__construct($eavEm, $em, $entityRepository, $typeRepository, $entityRelationRepository, $entityRelationTypeRepository);

        $this->actionStatusRepository = $actionStatusRepository;
        $this->actionStatusLocator       = $actionStatusLocator;
    }

    protected function getImsRepository(): ServiceEntityRepositoryInterface
    {
        return $this->actionStatusRepository;
    }

    protected function getEavTypeAlias(): string
    {
        return 'action_status';
    }

    protected function getFillMapping(): array
    {
        return [
            'code'       => function (ActionStatus $actionStatus) { return $actionStatus->getCode(); },
            'title'      => function (ActionStatus $actionStatus) {
                /** @var ActionStatusInterface $statusClass */
                $statusClass = $this->actionStatusLocator->getClassByCode($actionStatus->getCode());

                return $statusClass::getTitle();
            },
            'ttl'        => function (ActionStatus $actionStatus) {
                /** @var ActionStatusInterface $statusClass */
                $statusClass = $this->actionStatusLocator->getClassByCode($actionStatus->getCode());

                return $statusClass::getTtl();
            },
            'created_at' => function (ActionStatus $actionStatus) { return $actionStatus->getCreatedAt(); },
        ];
    }

    protected function getRelationsMapping(): array
    {
        return [
            'incident'   => [
                'target_eav_type_alias' => 'action',
                'getter_callback'       => function (ActionStatus $actionStatus) { return [ $actionStatus->getAction() ]; }
            ],
            'created_by' => [
                'target_eav_type_alias' => 'user',
                'getter_callback'       => function (ActionStatus $actionStatus) { return [ $actionStatus->getCreatedBy() ]; }
            ],
        ];
    }
}
