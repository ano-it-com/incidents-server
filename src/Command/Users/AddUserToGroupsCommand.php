<?php

namespace App\Command\Users;

use App\Entity\Security\Group;
use App\Repository\Security\UserRepository;
use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddUserToGroupsCommand extends Command
{
    protected static $defaultName = 'users:add:groups';

    private EntityManagerInterface $em;

    private UserService $userService;

    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $em, UserService $userService, UserRepository $userRepository)
    {
        $this->em = $em;
        parent::__construct("Add user to groups");
        $this->userService = $userService;
        $this->userRepository = $userRepository;
    }

    protected function configure()
    {
        $this
            ->addArgument('login', InputArgument::REQUIRED, 'User login')
            ->addArgument('groups', InputArgument::IS_ARRAY, 'Group codes', []);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $login = $input->getArgument('login');

        $user = $this->userRepository->findOneByLogin($login);
        if (!$user) {
            $io->error(sprintf('User with login %s not found', $login));
            return 1;
        }

        try {
            $groups = $this->userService->addUserToGroups($user, $input->getArgument('groups'));
            $this->em->flush();
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return 1;
        }

        $groupCodes = array_map(fn(Group $group) => $group->getCode(), $groups);

        $io->success(sprintf('User %s added to group/s (%s)', $user->getLogin(), implode(', ', $groupCodes)));
        return 0;
    }
}