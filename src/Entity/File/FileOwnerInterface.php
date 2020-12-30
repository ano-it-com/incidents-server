<?php

namespace App\Entity\File;

interface FileOwnerInterface
{

    public function getId();


    public static function getOwnerCode(): string;

}