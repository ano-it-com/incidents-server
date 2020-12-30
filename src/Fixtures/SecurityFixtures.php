<?php

namespace App\Fixtures;

use App\Domain\Action\Status\ActionStatusApproving;
use App\Domain\Action\Status\ActionStatusClarification;
use App\Domain\Action\Status\ActionStatusClosed;
use App\Domain\Action\Status\ActionStatusCorrection;
use App\Domain\Action\Status\ActionStatusDraft;
use App\Domain\Action\Status\ActionStatusInWork;
use App\Domain\Action\Status\ActionStatusNew;
use App\Domain\Incident\Status\IncidentStatusClosed;
use App\Domain\Incident\Status\IncidentStatusInWork;
use App\Domain\Incident\Status\IncidentStatusNew;
use App\Entity\Security\Group;
use App\Entity\Security\GroupPermission;
use App\Entity\Security\Permission;
use App\Entity\Security\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityFixtures extends Fixture
{

    private $permissionsByCode = [];

    private $groupsByCode = [];

    private $encodedPassword;


    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->encodedPassword = $passwordEncoder->encodePassword(new User(), self::getPassword());
    }

    public function load(ObjectManager $manager)
    {
        $this->createPermissions($manager);
        $this->createGroups($manager);
        $this->createUsers($manager);
    }

    public static function getPassword()
    {
        return 'imsqweqwe';
    }

    private function createPermissions(ObjectManager $em): void
    {
        $toCreate = [
            ['code' => 'is_admin', 'title' => 'Администратор'],
            ['code' => 'is_supervisor', 'title' => 'Супервизор'],
            ['code' => 'is_executor', 'title' => 'Исполнитель'],
            ['code' => 'can_create_incidents', 'title' => 'Может создавать инциденты'],
            ['code' => 'can_view_only_as_responsible', 'title' => 'Видит только те действия, где назначен ответственным'],
            ['code' => 'can_view_incident_by_status', 'title' => 'Видимость инцидентов по статусам', 'restriction_type' => 'incident'],
            ['code' => 'can_view_action_by_status', 'title' => 'Видимость действий по статусам', 'restriction_type' => 'action'],
            ['code' => 'can_edit_incident_by_status', 'title' => 'Изменение инцидентов по статусам', 'restriction_type' => 'incident'],
            ['code' => 'can_edit_action_by_status', 'title' => 'Изменение действий по статусам', 'restriction_type' => 'action'],
            ['code' => 'can_view_incident_without_actions', 'title' => 'Видит инциденты без действия'],
        ];

        foreach ($toCreate as $toCreateItem) {
            $code = $toCreateItem['code'];
            $title = $toCreateItem['title'];
            $restrictionType = $toCreateItem['restriction_type'] ?? null;

            $permission = new Permission();
            $permission->setCode($code);
            $permission->setTitle($title);
            $permission->setRestrictionType($restrictionType);
            $em->persist($permission);

            $this->permissionsByCode[$permission->getCode()] = $permission;
        }

        $em->flush();
    }


    private function getStatusesRestrictions(): array
    {
        return [
            'can_view_incident_by_status' => [
                'admin' => [
                    IncidentStatusNew::CODE,
                    IncidentStatusInWork::CODE,
                    IncidentStatusClosed::CODE,
                ],
                'supervisor' => [
                    IncidentStatusNew::CODE,
                    IncidentStatusInWork::CODE,
                    IncidentStatusClosed::CODE,
                ],
                'executor' => [
                    IncidentStatusInWork::CODE,
                    IncidentStatusClosed::CODE,
                ]
            ],
            'can_edit_incident_by_status' => [
                'admin' => [
                    IncidentStatusNew::CODE,
                    IncidentStatusInWork::CODE,
                    IncidentStatusClosed::CODE,
                ],
                'supervisor' => [
                    IncidentStatusNew::CODE,
                    IncidentStatusInWork::CODE,
                ],
                'executor' => [
                ]
            ],
            'can_view_action_by_status' => [
                'admin' => [
                    ActionStatusDraft::CODE,
                    ActionStatusNew::CODE,
                    ActionStatusInWork::CODE,
                    ActionStatusClarification::CODE,
                    ActionStatusApproving::CODE,
                    ActionStatusCorrection::CODE,
                    ActionStatusClosed::CODE,
                ],
                'supervisor' => [
                    ActionStatusDraft::CODE,
                    ActionStatusNew::CODE,
                    ActionStatusInWork::CODE,
                    ActionStatusClarification::CODE,
                    ActionStatusApproving::CODE,
                    ActionStatusCorrection::CODE,
                    ActionStatusClosed::CODE,
                ],
                'executor' => [
                    ActionStatusNew::CODE,
                    ActionStatusInWork::CODE,
                    ActionStatusClarification::CODE,
                    ActionStatusApproving::CODE,
                    ActionStatusCorrection::CODE,
                    ActionStatusClosed::CODE,
                ]
            ],
            'can_edit_action_by_status' => [
                'admin' => [
                    ActionStatusDraft::CODE,
                    ActionStatusNew::CODE,
                    ActionStatusInWork::CODE,
                    ActionStatusClarification::CODE,
                    ActionStatusApproving::CODE,
                    ActionStatusCorrection::CODE,
                    ActionStatusClosed::CODE,
                ],
                'supervisor' => [
                    ActionStatusDraft::CODE,
                    ActionStatusNew::CODE,
                    ActionStatusInWork::CODE,
                    ActionStatusClarification::CODE,
                    ActionStatusApproving::CODE,
                ],
                'executor' => [
                    ActionStatusNew::CODE,
                    ActionStatusInWork::CODE,
                    ActionStatusCorrection::CODE,
                ]
            ]
        ];
    }


    private function createGroups(ObjectManager $em): void
    {
        $groups = [
            'admin' => [
                'title' => 'Администратор',
                'permissions' => [
                    'can_create_incidents',
                    'can_view_incident_without_actions',
                    'is_admin',
                    'is_supervisor',
                ],
                'public' => false,
            ],
            'supervisor' => [
                'title' => 'Супервизор',
                'permissions' => [
                    'can_create_incidents',
                    'can_view_incident_without_actions',
                    'is_supervisor',
                ],
                'public' => true,
            ],
            'executor' => [
                'title' => 'Исполнитель (служебная)',
                'permissions' => [
                    'can_view_only_as_responsible',
                    'is_executor',
                ],
                'public' => false,
            ],
        ];

        $restrictions = $this->getStatusesRestrictions();

        foreach ($groups as $code => $groupInfo) {
            $group = new Group();
            $group->setCode($code);
            $group->setTitle($groupInfo['title']);
            $group->setPublic($groupInfo['public']);

            $em->persist($group);
            $this->setReference("group::$code", $group);

            foreach ($groupInfo['permissions'] as $permissionCode) {
                $permission = $this->permissionsByCode[$permissionCode] ?? null;
                if (!$permission) {
                    throw new \InvalidArgumentException('Permission ' . $permissionCode . ' not found');
                }

                $this->createGroupPermission($permissionCode, $group, null, $em);
            }

            foreach ($restrictions as $permissionCode => $rights) {
                $permissionsByStatus = $rights[$code] ?? [];

                if (!count($permissionsByStatus)) {
                    continue;
                }

                $restriction = [];

                foreach ($permissionsByStatus as $status) {
                    $restriction[$status] = true;
                }

                $this->createGroupPermission($permissionCode, $group, $restriction, $em);
            }

            $this->groupsByCode[$group->getCode()] = $group;
        }

        $em->flush();
    }


    private function createGroupPermission(string $permissionCode, Group $group, ?array $restriction, ObjectManager $em): void
    {
        $permission = $this->permissionsByCode[$permissionCode] ?? null;
        if (!$permission) {
            throw new \InvalidArgumentException('Permission ' . $permissionCode . ' not found');
        }

        $gp = new GroupPermission();
        $gp->setPermission($permission);
        $gp->setGroup($group);
        $gp->setRestriction($restriction);

        $em->persist($gp);

    }


    private function createUsers(ObjectManager $em): void
    {
        $login = 'admin';
        $email = 'admin@ims.ru';
        $firstName = 'Администратор';
        $lastName = 'Администратор';
        $groupCode = 'admin';

        $user = $this->createUser(
            $login,
            $email,
            $firstName,
            $lastName,
            $groupCode,
            $em,
            15509887
        );

        $user->addGroup($this->groupsByCode['admin']);

        $login = 'supervisor';
        $email = 'supervisor@ims.ru';
        $firstName = 'Супервизор';
        $lastName = 'Супервизор';
        $groupCode = 'supervisor';

        $user = $this->createUser(
            $login,
            $email,
            $firstName,
            $lastName,
            $groupCode,
            $em,
            15509843
        );

        $user->addGroup($this->groupsByCode['supervisor']);

        for ($i = 1; $i <= 4; $i++) {

            $login = 'executor' . $i;
            $email = 'executor' . $i . '@ims.ru';
            $firstName = 'Исполнитель ' . $i;
            $lastName = '-';
            $groupCode = 'executor';

            $user = $this->createUser(
                $login,
                $email,
                $firstName,
                $lastName,
                $groupCode,
                $em,
                14243
            );

            $user->addGroup($this->groupsByCode['executor']);

            $group = new Group();
            $group->setCode($login);
            $group->setTitle('Группа исполнителей ' . $i);
            $group->setPublic(true);
            $em->persist($group);
            $this->createGroupPermission('is_executor', $group, null, $em);

            $user->addGroup($group);
        }

        $em->flush();
    }


    private function createUser(string $login, string $email, string $firstName, string $lastName, string $groupCode, ObjectManager $em, ?int $telegramId = null): User
    {
        $group = $this->groupsByCode[$groupCode] ?? null;

        if (!$group) {
            throw new \InvalidArgumentException('Group ' . $groupCode . ' not found');
        }

        $user = new User();
        $user->setLogin($login);
        $user->setPassword($this->encodedPassword);
        $user->setEmail($email);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setTelegramId($telegramId);

        $em->persist($user);

        $em->flush();
        $this->setReference("user::$groupCode", $user);

        return $user;
    }
}
