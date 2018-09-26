<?php

namespace App\Domain\Model\Repository\Contract;

use App\Domain\Entity\Account;
use phpDocumentor\Reflection\Types\Boolean;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

interface AccountRepositoryInterface
{
    /**
     * @return Account|null
     */
    public function getAll(): ?array;

    /**
     * @param string $id
     * @return Account|null
     */
    public function getById(string $id): ?Account;

    /**
     * @param string $email
     * @return Account|null
     */
    public function getByEmail(string $email): ?Account;

    /**
     * @param string $email
     * @param string $password
     * @return Account|null
     */
    public function getByCredentials(string $email, string $password): ?Account;

    /**
     * @param Account $account
     * @return Account|null
     */
    public function create(Account $account, $flush = false): ?Account;

    /**
     * @param Account $account
     * @return Account|null
     */
    public function update(Account $account, $flush = false): ?Account;

    /**
     * @param bool $flush
     * @param Account $account
     * @return bool
     */
    public function remove(Account $account, $flush = false): bool;
}
