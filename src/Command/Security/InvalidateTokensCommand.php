<?php

namespace App\Command\Security;

use App\Security\TokenService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InvalidateTokensCommand extends Command
{
    protected static $defaultName = 'security:invalidate-tokens';

    private TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        parent::__construct("Invalidate expired tokens");
        $this->tokenService = $tokenService;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $countDeleted = $this->tokenService->invalidateTokens();
        $io->note("Delete $countDeleted token/s");
        return 0;
    }
}