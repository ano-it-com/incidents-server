<?php

namespace App\Command\Users;

use App\Services\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'users:create';

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
            ->addArgument('login', InputArgument::REQUIRED, 'User login')
            ->addArgument('email', InputArgument::REQUIRED, 'User email')
            ->addArgument('firstName', InputArgument::REQUIRED, 'User first name')
            ->addArgument('lastName', InputArgument::REQUIRED, 'User last name')
            ->addArgument('plainPassword', InputArgument::REQUIRED, 'User plain password')
            ->addOption('telegramId', 'tid', InputArgument::OPTIONAL, 'User telegram id');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $user = $this->userService->createUser(array_merge($input->getArguments(), $input->getOptions()));
            $this->em->flush();
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return 1;
        }

        $io->success(sprintf('User %s successfully created', $user->getLogin()));
        return 0;
    }
}