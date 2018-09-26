<?php

namespace App\Domain\Model;

use \Exception;
use Symfony\Component\HttpFoundation\Request;
use App\Domain\Entity\Account;
use App\Domain\Model\Repository\Contract\AccountRepositoryInterface;
use App\Domain\AbstractDomain;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Todo: fix cache expiration time.
 *
 * Class AccountModel
 * @package App\Domain\Model
 */
class AccountModel extends AbstractDomain
{

    /**
     * Return all accounts
     *
     * @return array|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getAllAccounts(): ?array
    {
        try{
            $cachedItemsAccounts = $this->container->get('cache.app')->getItem('getAllAccounts');
            if (!$cachedItemsAccounts->isHit()) {
                $cachedItemsAccounts->expiresAt(30);
                $cachedItemsAccounts->set($this->container->get(AccountRepositoryInterface::class)->getAll());
                $this->container->get('cache.app')->save($cachedItemsAccounts);
            }
            return $cachedItemsAccounts->get();
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param int $id
     * @return Account
     * @throws Exception
     */
    public function getAccountById(int $id): Account
    {
        try{
            if (empty($id)) {
                throw new Exception('Account ID can not be empty');
            }
            if (!$account = $this->container->get(AccountRepositoryInterface::class)->getById($id)) {
                throw new \Exception('Account ID does not exist');
            }
            return $account;
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException($e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param string $email
     * @param string $password
     * @return Account|null
     * @throws Exception
     */
    public function getAccountByCredentials(string $email, string $password): ?Account
    {
        try{
            if (empty($email)) {
                throw new \Exception('Email can not be empty');
            }
            if (empty($password)) {
                throw new \Exception('Password can not be empty');
            }
            return $this->container->get(AccountRepositoryInterface::class)->getByCredentials($email, $password);
        } catch (Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $array
     * @return Account
     * @throws Exception
     */
    public function addAccount($array): Account
    {
        try{
            $account    = new Account($array);
            if ($accountExists = $this->container->get(AccountRepositoryInterface::class)->getByEmail($account->getEmail())) {
                throw new \Exception('An Account already exists with the provided email!');
            }
            if (!in_array($account->getRole(), Account::$availableRoles)) {
                throw new \Exception('Invalid Account Role');
            }
            $account->setCreatedAt(date('Y-m-d G:i:s'));
            $newAccount = $this->container->get(AccountRepositoryInterface::class)->create($account);
            $message = [
                'template'  =>'email/account/registration.html.twig',
                'data'      => $newAccount->toArray(),
                'subject'   => 'Account Registration'
            ];
            $newEmailMessage = json_encode($message);
            $this->container->get('old_sound_rabbit_mq.emailing_producer')->setContentType('application/json');
            $this->container->get('old_sound_rabbit_mq.emailing_producer')->publish($newEmailMessage);
            return $newAccount;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param string $id
     * @param $array
     * @return Account
     * @throws Exception
     */
    public function updateAccount(string $id, $array):  Account
    {
        try{
            if (!$account = $this->container->get(AccountRepositoryInterface::class)->getById($id)) {
                throw new Exception('Account not found!');
            }
            $account->populate($array);
            $account->setUpdatedAt(date('Y-m-d'));
            return $this->container->get(AccountRepositoryInterface::class)->update($account);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function removeAccount(int $id): bool
    {
        try{
            $account = $this->container->get(AccountRepositoryInterface::class)->getById($id);
            $account->setDeletedAt(date('Y-m-d G:i:s'));
            return $this->container->get(AccountRepositoryInterface::class)->remove($account);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
