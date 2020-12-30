<?php

namespace App\UserActions\IncidentActionContext\Actions;

use App\Entity\Incident\Action\Action;
use App\Entity\Incident\Incident;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\HistoryDTO;
use App\ReadModel\Incident\DTO\Detail\HistoryCodeDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\ReadModel\Incident\DTO\Detail\UserDTO;
use App\Repository\Incident\Action\ActionRepository;
use App\Repository\Incident\IncidentRepository;
use App\Security\Permissions\UserPermissions;
use InvalidArgumentException;
use Symfony\Component\Security\Core\User\UserInterface;

class GetHistoryActionUserAction extends AbstractIncidentActionUserAction
{
    public static function supports(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): bool
    {
        return true;
    }

    public function exportRights(IncidentDTO $incidentDTO, ActionDTO $incidentActionDTO, UserPermissions $userPermissions): array
    {
        return [
            'canGetHistoryAction' => fn () => true
        ];
    }

    public function execute(ActionDTO $actionDTO, UserInterface $user)
    {
        /** @var ActionRepository $actionRepository */
        $actionRepository = $this->em->getRepository(Action::class);
        $action = $actionRepository->find($actionDTO->id);

        $history = [];
        $previousStatus = null;
        foreach ($action->getStatuses() as $status) {
            $previousStatusCode = null !== $previousStatus ? $previousStatus->getCode(): null;
            $createdBy = $status->getCreatedBy();

            $historyDtoPartFrom = new HistoryCodeDTO();
            $historyDtoPartFrom->code = $previousStatusCode;
            $historyDtoPartFrom->title = null !== $previousStatusCode ? $this->actionStatusLocator->getByCode($previousStatusCode)->getTitle() : null;

            $historyDtoPartTo = new HistoryCodeDTO();
            $historyDtoPartTo->code = $status->getCode();
            $historyDtoPartTo->title = $this->actionStatusLocator->getByCode($status->getCode())->getTitle();

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
