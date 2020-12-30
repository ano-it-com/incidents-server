<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Liip\TestFixturesBundle\LiipTestFixturesBundle::class => ['all' => true],
    Nelmio\ApiDocBundle\NelmioApiDocBundle::class => ['all' => true],
    SsoBundle\SsoBundle::class => ['all' => true],
    Nelmio\CorsBundle\NelmioCorsBundle::class => ['all' => true],
    Zenstruck\ScheduleBundle\ZenstruckScheduleBundle::class =>['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['all' => true],


    TelegramNotificationBundle\TelegramNotificationBundle::class => ['dev' => true,                 'stage' => true, 'prod' => true],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true, 'stage' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class =>                ['dev' => true, 'test' => true],
    ANOITCOM\EAVBundle\EAVBundle::class => ['all' => true],
];
