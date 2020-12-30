<?php

namespace App\UserActions\IncidentActionContext\Notification;

use App\DependencyInjection\SecurityAwareInterface;
use App\DependencyInjection\SecurityAwareTrait;
use App\Messenger\Message\TelegramMessage;
use App\Modules\Notification\AbstractNotificationHandler;
use App\Modules\Notification\Messenger\NotificationMessageInterface;
use App\ReadModel\Incident\DTO\Detail\ActionDTO;
use App\ReadModel\Incident\DTO\Detail\IncidentDTO;
use App\UserActions\IncidentActionContext\Actions\ChangeActionToClarificationUserAction;
use App\UserActions\IncidentActionContext\Notification\Message\NotificationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class ChangeActionToClarificationNotificationHandler extends AbstractNotificationHandler implements SecurityAwareInterface
{
    use SecurityAwareTrait;

    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function supports(string $eventName): bool
    {
        return ChangeActionToClarificationUserAction::class === $eventName;
    }

    /**
     * @param NotificationMessage $message
     *
     * @return NotificationMessageInterface[]
     */
    public function handle(NotificationMessageInterface $message): array
    {
        $incidentGrantedUsers = $this->getIncidentGrantedUsers($message->getIncident()->id);
        $actionGrantedUsers = $this->getActionGrantedUsers($message->getAction()->id);

        $grantedUsers = array_intersect_key($incidentGrantedUsers, $actionGrantedUsers);
        if (isset($grantedUsers[$message->getUser()->getId()])) {
            unset($grantedUsers[$message->getUser()->getId()]);
        }

        return [
            $this->getTelegramNotification($message->getIncident(), $message->getAction(), $message->getContext(), $grantedUsers)
        ];
    }

    private function getTelegramNotification(IncidentDTO $incident, ActionDTO $action, ?array $context, array $grantedUsers): TelegramMessage
    {
        $incidentUrl = $this->router->generate('front.incident.view', ['id' => $incident->id], UrlGeneratorInterface::ABSOLUTE_URL);
        $actionUrl = $this->router->generate('front.incident.action.view', [
            'incidentId' => $incident->id,
            'actionId' => $action->id
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = "Запрос на уточнение по действию " .PHP_EOL .
        "<a href=\"{$actionUrl}\">{$action->type->title}</a>" .PHP_EOL .
        "из инцидента " .PHP_EOL .
        "ID {$incident->id} <a href=\"{$incidentUrl}\">{$incident->title}</a>" .PHP_EOL;

        $context = [
            'Комментарий' => $context['comment']->getText(),
            'Время'       => $action->createdAt->format('d.m.Y H:i:s'),
        ];

        return $this->getTelegramChannel($message, $context, $grantedUsers);
    }
}
