<?php

namespace App\Command\Users;

use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateUserCommand extends Command
{
    protected static $defaultName = 'users:update';

    private UserService $userService;

    private EntityManagerInterface $em;

    public function __construct(UserService $userService, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->userService = $userService;
        $this->em = $em;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setDescription('Create or update user')
            ->addArgument('userId', InputArgument::REQUIRED, 'User identifier')
            ->addOption('login', 'l', InputArgument::OPTIONAL, 'User login')
            ->addOption('email', 'em', InputArgument::OPTIONAL, 'User email')
            ->addOption('firstName', 'fm', InputArgument::OPTIONAL, 'User first name')
            ->addOption('lastName', 'ln', InputArgument::OPTIONAL, 'User last name')
            ->addOption('plainPassword', 'pp', InputArgument::OPTIONAL, 'User plain password')
            ->addOption('telegramId', 'tid', InputArgument::OPTIONAL, 'User telegram id');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $user = $this->userService->updateUser(array_merge($input->getArguments(), $input->getOptions()));
            $this->em->flush();
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return 1;
        }

        $io->success(sprintf('User %s successfully updated', $user->getLogin()));
        return 0;
    }
}