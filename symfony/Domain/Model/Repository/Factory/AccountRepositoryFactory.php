<?php

namespace App\Domain\Model\Repository\Factory;

use App\Core\Service\Contract\FactoryInterface;
use App\Domain\Model\Repository\AccountRepository;
use Psr\Container\ContainerInterface;

class AccountRepositoryFactory implements FactoryInterface
{
    /**
     * Account Repository
     *
     * @param ContainerInterface $container
     *
     * @return AccountRepository
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function service(ContainerInterface $container)
    {
        return (new AccountRepository())->setEntityManager($container->get('doctrine.orm.entity_manager'));
    }
}
