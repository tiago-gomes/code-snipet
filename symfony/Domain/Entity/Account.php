<?php

namespace App\Domain\Entity;

use App\Core\Traits\TimeRecordsTrait;
use Doctrine\ORM\Mapping AS ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 *
 * todo: https://symfony.com/doc/current/security/guard_authentication.html
 */
class Account implements UserInterface
{
    use TimeRecordsTrait;

    const PRO_ROLE      = 'pro';
    const ADMIN_ROLE    = 'admin';
    const TRIAL_ROLE    = 'trial';
    const USER_ROLE     = 'user';

    /**
     * Allowed account roles.
     *
     * @var array $roles
     */
    public static $availableRoles = [
        self::ADMIN_ROLE,
        self::PRO_ROLE,
        self::TRIAL_ROLE,
        self::USER_ROLE
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", unique=true)
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true, length=35)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", nullable=true, length=35)
     */
    protected $lastName;

    /**
     * @ORM\Column(type="string", nullable=true, length=35)
     */
    protected $userName;

    /**
     * @ORM\Column(type="string", unique=true, length=75)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string", nullable=true, length=4)
     */
    private $salt;

    /**
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="string", nullable=true, length=25)
     */
    private $role;

    /**
     *
     * Populate entity with data
     *
     * @param array $array
     * @return Account|null
     */
    public function populate(array $array): ?self
    {
        if (!empty($array['id']) ) {
            $this->setId($array['id']);
        }

        if (!empty($array['firstName']) ) {
            $this->setFirstName($array['firstName']);
        }

        if (!empty($array['userName']) ) {
            $this->setUserName($array['userName']);
        }

        if (!empty($array['lastName'])) {
            $this->setLastName($array['lastName']);
        }

        if (!empty($array['email'])) {
            $this->setEmail($array['email']);
        }

        if (!empty($array['birthday'])) {
            $this->setBirthday($array['birthday']);
        }

        if (!empty($array['salt']) ) {
            $this->setSalt($array['salt']);
        }

        if (!empty($array['password']) ) {
            $this->setPassword($array['password']);
        }

        if (!empty($array['role']) ) {
            $this->setRole($array['role']);
        }

        return $this;
    }

    /**
     * Account constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        return $this->populate($data);
    }

    /**
     * @return mixed
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @param $role
     * @return Account
     */
    public function setRole($role): self
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * todo: Fix password encryption
     * @param $password
     * @return Account
     */
    public function setPassword(string $password): self
    {
        $this->password = hash('sha1', $password);
        return $this;
    }

    /**
     * @param $salt
     * @return Account
     */
    public function setSalt($salt): self
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return Account
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param $firstName
     * @return Account
     */
    public function setFirstName($firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param $lastName
     * @return Account
     */
    public function setLastName($lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param $email
     * @return Account
     */
    public function setEmail($email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getBirthday(): ?\DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime|null $birthday
     * @return Account|null
     */
    public function setBirthday(\DateTime $birthday = null): ?self
    {
        $this->birthday = $birthday ? clone $birthday : null;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $array = [
            'id'        => $this->getId(),
            'firstName' => $this->getFirstName(),
            'lastName'  => $this->getLastName(),
            'userName'  => $this->getUserName(),
            'email'     => $this->getEmail(),
            'role'      => $this->getRole(),
            'createdAt'    => $this->getCreatedAt()
        ];

        if (empty($this->getId())) {
            unset($array['id']);
        }

        if (empty($this->getPassword())) {
            unset($array['password']);
        }

        if (empty($this->getSalt())) {
            unset($array['salt']);
        }

        return $array;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return self::$availableRoles;
    }

    /**
     * @return null|string
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @return null|string
     */
    public function getUsername(): ?string
    {
        return $this->userName;
    }

    /**
     * @return null|string
     */
    public function setUsername($username): self
    {
        $this->userName = $username;
        return $this;
    }

    /**
     *
     */
    public function eraseCredentials()
    {
        return;
    }
}
