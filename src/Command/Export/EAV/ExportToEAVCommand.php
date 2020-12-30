<?php

namespace App\Command\Export\EAV;

use App\Modules\Export\EAV\EAVExportersLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportToEAVCommand extends Command
{

    protected static $defaultName = 'export:eav:export';

    private EAVExportersLocator $exportersLocator;


    public function __construct(EAVExportersLocator $exportersLocator)
    {
        parent::__construct("Create Types for export");
        $this->exportersLocator = $exportersLocator;
    }


    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->newLine();

        foreach ($this->exportersLocator->getAllClasses() as $exporterClass) {
            $io->writeln('Export entities by ' . $exporterClass . '...');
            $exporter = $this->exportersLocator->get($exporterClass);
            $exporter->exportEntities();
        }

        foreach ($this->exportersLocator->getAllClasses() as $exporterClass) {
            $io->writeln('Export relations by ' . $exporterClass . '...');
            $exporter = $this->exportersLocator->get($exporterClass);
            $exporter->exportRelations();
        }

        $io->success('End');

        return 0;
    }
}