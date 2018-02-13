<?php

namespace Krak\DoctrineOAuth2\Model;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class Seed
{
    private $em;
    private $seeds;

    public function __construct(EntityManagerInterface $em, array $seeds = []) {
        $this->em = $em;
        $this->seeds = $seeds;
    }

    public function seed(LoggerInterface $logger) {
        foreach ($this->seeds as $seed) {
            $seed($this->em, $logger);
        }

        $this->em->flush();
    }
}
