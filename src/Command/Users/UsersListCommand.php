<?php

namespace App\Command\Users;

use App\Repository\Security\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UsersListCommand extends Command
{
    protected static $defaultName = 'users:list';

    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct("Get users list");
        $this->userRepository = $userRepository;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $rows = [];
        foreach ($this->userRepository->findAll() as $user){
            $rows[] = [
                $user->getId(),
                $user->getLogin(),
                $user->getFullName(),
                $user->getEmail(),
            ];
        }
        $io->table(['ID', 'login', 'fullname', 'email'], $rows);
        return 0;
    }
}