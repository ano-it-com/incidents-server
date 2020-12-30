<?php

namespace ANOITCOM\EAVBundle\EAV\ORM\Persistence\ChangesCalculator;

interface ChangesCalculatorInterface
{

    public function getChanges(array $newValues, array $oldValues): array;
}