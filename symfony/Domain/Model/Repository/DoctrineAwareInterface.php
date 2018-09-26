<?php

namespace App\Domain\Model\Repository;

use Doctrine\ORM\EntityManagerInterface;

interface DoctrineAwareInterface
{
    public function getEntityManager(): EntityManagerInterface;

    public function setEntityManager(EntityManagerInterface $em);
}
