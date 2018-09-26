<?php

namespace App\Domain\Model\Repository;

use App\Domain\Entity\Account;
use App\Domain\Model\Repository\DoctrineAwareInterface;
use App\Domain\Model\Repository\DoctrineAwareTrait;
use App\Domain\Model\Repository\Contract\AccountRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;


class AccountRepository implements AccountRepositoryInterface, DoctrineAwareInterface
{
    use DoctrineAwareTrait;

    /**
     * @inheritdoc
     */
    public function getAll(): ?array
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
            ->from(Account::class, 'c');
        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @inheritdoc
     */
    public function getById(string $id): ?Account
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
            ->from(Account::class, 'c')
            ->where('c.id = :id')
            ->setMaxResults(1);

        $qb->setParameter('id', $id);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function getByEmail(string $email): ?Account
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
            ->from(Account::class, 'c')
            ->where('c.email = :email')
            ->setMaxResults(1);

        $qb->setParameter('email', $email);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function getByCredentials(string $email, string $password): ?Account
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('c')
            ->from(Account::class, 'c')
            ->where('c.email = :email')
            ->andWhere('c.password = :password')
            ->setMaxResults(1);

        $qb->setParameter('email', $email);
        $qb->setParameter('password', $password);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function create(Account $account, $flush = true): ?Account
    {
        $this->getEntityManager()->persist($account);

        if ($flush) {
            $this->flush();
        }

        return $account;
    }

    /**
     * @inheritdoc
     */
    public function update(Account $account, $flush = true): ?Account
    {

        $account = $this->getEntityManager()->merge($account);

        if ($flush) {
            $this->flush();
        }

        return $account;
    }

    /**
     * @inheritdoc
     */
    public function remove(Account $account, $flush = true): bool
    {
        $this->getEntityManager()->remove($account);

        if ($flush) {
            $this->flush();
        }

        return true;
    }
}
