<?php

namespace ANOITCOM\EAVBundle\Install;

use ANOITCOM\EAVBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class InstallCommand extends Command
{

    protected static $defaultName = 'eav:install';

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var KernelInterface
     */
    private $kernel;


    public function __construct(
        KernelInterface $kernel,
        Filesystem $fs
    ) {
        parent::__construct(self::$defaultName);
        $this->kernel = $kernel;
        $this->fs     = $fs;
    }


    protected function configure()
    {
        $this->addArgument('actions', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Available options: migration, config');
    }


    public function run(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $actions = $this->getActionsToDo($input);

        $io->writeln('Installing EAV Bundle');

        if (in_array('config', $actions, true)) {
            $io->writeln('Installing config...');

            $configPath = $this->installConfig();

            $io->success('Config installed to ' . $configPath);
        }

        if (in_array('migration', $actions, true)) {
            $io->writeln('Installing migrations...');
            $migrationsPath = $this->installMigrations();
            $io->success('Migrations installed to ' . $migrationsPath);
        }

        $io->success('Installation complete successful');

        return 0;

    }


    private function installConfig(): string
    {
        $dumper = new YamlReferenceDumper();
        $config = $dumper->dump(new Configuration());

        $path = $this->kernel->getProjectDir() . '/config/packages/eav.yaml';

        $this->fs->dumpFile($path, $config);

        return $path;
    }


    private function installMigrations(): string
    {

        [ $migrationContent, $className ] = $this->compileMigration();

        $path = $this->kernel->getProjectDir() . '/migrations/' . $className . '.php';

        $this->fs->dumpFile($path, $migrationContent);

        return $path;
    }


    private function compileMigration(): array
    {
        $templatePath = __DIR__ . '/Migrations/Migration.tpl.php';
        $className    = 'Version' . (new \DateTime('now', new \DateTimeZone('UTC')))->format('YmdHis');

        ob_start();

        include $templatePath;

        $content = ob_get_clean();

        return [ $content, $className ];

    }


    /**
     * @param InputInterface $input
     *
     * @return string[]
     */
    private function getActionsToDo(InputInterface $input): array
    {
        $allActions = [
            'migration',
            'config',
        ];

        $actions = $input->getArgument('actions');

        if ( ! count($actions)) {
            return $allActions;
        }

        $actionsToDo = [];

        foreach ($actions as $action) {
            if ( ! in_array($action, $allActions, true)) {
                throw new \InvalidArgumentException('Action \'' . $action . '\' not supported!');
            }

            $actionsToDo[] = $action;
        }

        return $actionsToDo;
    }
}