<?php


namespace App\Tests\Functional;

use App\Tests\KernelBrowserProxy;
use Doctrine\Common\DataFixtures\Executor\AbstractExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseWebTestCase extends WebTestCase
{
    use FixturesTrait {
        loadFixtures as traitLoadFixtures;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->getContainer()->get(EntityManagerInterface::class);
    }

    protected static function createClient(array $options = [], array $server = []): KernelBrowserProxy
    {
        return new KernelBrowserProxy(parent::createClient($options, $server), self::$container);
    }

    protected function loadFixtures(array $classNames = []): ?AbstractExecutor
    {
        return $this->traitLoadFixtures($classNames, false, null, 'doctrine', ORMPurger::PURGE_MODE_DELETE);
    }

    protected static function getTestFilePath($name)
    {
        $fileName = dirname(__FILE__) . "/files/$name";
        $tmpFile = sys_get_temp_dir() . "/$name";
        copy($fileName, $tmpFile);
        return $tmpFile;
    }
}