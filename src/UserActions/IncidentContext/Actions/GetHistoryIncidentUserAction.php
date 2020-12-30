<?php

namespace App\UserActions\IncidentContext\Actions;

use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Incident;
use App\ReadModel\Incident\DTO\Detail\HistoryDTO;
use App\ReadModel\Incident\DTO\Detail\HistoryCodeDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentTypeDTO;
use App\ReadModel\Incident\DTO\Detail\UserDTO;
use App\Repository\Incident\IncidentRepository;
use App\Security\Permissions\UserPermissions;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;
use Throwable;

class GetHistoryIncidentUserAction extends AbstractIncidentUserAction
{
    public static function supports(IncidentDTO $incidentDTO, UserPermissions $userPermissions): bool
    {
        /** @var IncidentTypeDTO $type */
        $type = $incidentDTO->type;

        return $type->handler === 'incident';
    }

    public function exportRights(IncidentDTO $incidentDTO, UserPermissions $userPermissions): array
    {
        return [
            'canGetHistoryIncident' => fn () => true,
        ];
    }

    /**
     * @param IncidentDTO $incidentDTO
     * @param UserInterface $user
     * @return Action[]
     * @throws Throwable
     */
    public function execute(IncidentDTO $incidentDTO, UserInterface $user): array
    {
        /** @var IncidentRepository $incidentRepository */
        $incidentRepository = $this->em->getRepository(Incident::class);
        $incident = $incidentRepository->find($incidentDTO->id);

        $history = [];
        $previousStatus = null;
        foreach ($incident->getStatuses() as $status) {
            $previousStatusCode = null !== $previousStatus ? $previousStatus->getCode(): null;
            $createdBy = $status->getCreatedBy();

            $historyDtoPartFrom = new HistoryCodeDTO();
            $historyDtoPartFrom->code = $previousStatusCode;
            $historyDtoPartFrom->title = null !== $previousStatusCode ? $this->incidentStatusLocator->getByCode($previousStatusCode)->getTitle() : null;

            $historyDtoPartTo = new HistoryCodeDTO();
            $historyDtoPartTo->code = $status->getCode();
            $historyDtoPartTo->title = $this->incidentStatusLocator->getByCode($status->getCode())->getTitle();

            $initiatedBy = new UserDTO();
            $initiatedBy->id = $createdBy->getId();
            $initiatedBy->login = $createdBy->getLogin();
            $initiatedBy->email = $createdBy->getEmail();
            $initiatedBy->lastName = $createdBy->getLastName();
            $initiatedBy->firstName = $createdBy->getFirstName();

            $historyDto = new HistoryDTO();
            $historyDto->from = $historyDtoPartFrom;
            $historyDto->to = $historyDtoPartTo;
            $historyDto->initiatedBy = $initiatedBy;
            $historyDto->initiatedAt = $status->getCreatedAt();

            $previousStatus = $status;

            $history[] = $historyDto;
        }

        return $history;
    }
}
