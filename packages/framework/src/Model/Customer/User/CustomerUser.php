<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Security\TimelimitLoginInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(
 *     name="customer_users",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="email_domain", columns={"email", "domain_id"})
 *     },
 *     indexes={
 *         @ORM\Index(columns={"email"})
 *     }
 * )
 * @ORM\Entity
 */
class CustomerUser implements UserInterface, TimelimitLoginInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\Customer")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $customer;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $firstName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $email;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $password;

    /**
     * @var \DateTime
     */
    protected $lastActivity;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup")
     * @ORM\JoinColumn(name="pricing_group_id", referencedColumnName="id", nullable=false)
     */
    protected $pricingGroup;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $resetPasswordHash;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $resetPasswordHashValidThrough;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $telephone;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress")
     * @ORM\JoinColumn(name="default_delivery_address_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $defaultDeliveryAddress;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain>
     * @ORM\OneToMany(targetEntity="CustomerUserRefreshTokenChain", mappedBy="customerUser", cascade={"persist"})
     */
    protected $refreshTokenChain;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $newsletterSubscription;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     * @ORM\ManyToOne(targetEntity="\Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup")
     * @ORM\JoinColumn(name="role_group_id", referencedColumnName="id", nullable=false)
     */
    protected $roleGroup;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     */
    public function __construct(CustomerUserData $customerUserData)
    {
        $this->domainId = $customerUserData->domainId;
        $this->setEmail($customerUserData->email);
        $this->customer = $customerUserData->customer;
        $this->uuid = $customerUserData->uuid ?: Uuid::uuid4()->toString();
        $this->refreshTokenChain = new ArrayCollection();

        if ($customerUserData->createdAt !== null) {
            $this->createdAt = $customerUserData->createdAt;
        } else {
            $this->createdAt = new DateTime();
        }
        $this->setData($customerUserData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     */
    public function edit(CustomerUserData $customerUserData)
    {
        $this->setData($customerUserData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     */
    protected function setData(CustomerUserData $customerUserData): void
    {
        $this->firstName = $customerUserData->firstName;
        $this->lastName = $customerUserData->lastName;
        $this->pricingGroup = $customerUserData->pricingGroup;
        $this->telephone = $customerUserData->telephone;
        $this->defaultDeliveryAddress = $customerUserData->defaultDeliveryAddress;
        $this->newsletterSubscription = $customerUserData->newsletterSubscription;
        $this->roleGroup = $customerUserData->roleGroup;
    }

    /**
     * @param string $email
     */
    public function setEmail($email): void
    {
        $this->email = mb_strtolower($email);
    }

    /**
     * @param string $passwordHash
     */
    public function setPasswordHash(string $passwordHash): void
    {
        $this->password = $passwordHash;
        $this->resetPasswordHash = null;
        $this->resetPasswordHashValidThrough = null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return \DateTime
     */
    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    /**
     * @param \DateTime $lastActivity
     */
    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @param int $domainId
     */
    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password ?? '';
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        if ($this->getCustomer()->getBillingAddress()->isCompanyCustomer()) {
            return (string)$this->getCustomer()->getBillingAddress()->getCompanyName();
        }

        return $this->lastName . ' ' . $this->firstName;
    }

    /**
     * @return string
     */
    public function getCustomerUserFullName()
    {
        return $this->lastName . ' ' . $this->firstName;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup()
    {
        return $this->pricingGroup;
    }

    /**
     * @return string|null
     */
    public function getResetPasswordHash()
    {
        return $this->resetPasswordHash;
    }

    /**
     * @return array{id: int , email: string, password: string, timestamp: int, domainId: int}
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->getPassword(),
            'timestamp' => time(), // lastActivity
            'domainId' => $this->domainId,
        ];
    }

    /**
     * @param array{id: int , email: string, password: string, timestamp: int, domainId: int} $data
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->password = $data['password'];
        $this->domainId = $data['domainId'];
        $this->lastActivity = new DateTime();
        $this->lastActivity->setTimestamp($data['timestamp']);
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        $roles = [CustomerUserRole::ROLE_API_LOGGED_CUSTOMER];

        foreach ($this->roleGroup->getRoles() as $role) {
            $roles[] = $role;
        }

        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt(): ?string
    {
        return null; // bcrypt include salt in password hash
    }

    /**
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @param string $resetPasswordHash
     */
    public function setResetPasswordHash($resetPasswordHash): void
    {
        $this->resetPasswordHash = $resetPasswordHash;
        $this->resetPasswordHashValidThrough = new DateTime('+48 hours');
    }

    /**
     * @param string|null $hash
     * @return bool
     */
    public function isResetPasswordHashValid(?string $hash): bool
    {
        if ($hash === null || $this->resetPasswordHash !== $hash) {
            return false;
        }

        $now = new DateTime();

        return $this->resetPasswordHashValidThrough !== null && $this->resetPasswordHashValidThrough >= $now;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    public function getDefaultDeliveryAddress()
    {
        return $this->defaultDeliveryAddress;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChain $customerUserRefreshTokenChain
     */
    public function addRefreshTokenChain(CustomerUserRefreshTokenChain $customerUserRefreshTokenChain): void
    {
        $this->refreshTokenChain->add($customerUserRefreshTokenChain);
    }

    /**
     * @return bool
     */
    public function isNewsletterSubscription()
    {
        return $this->newsletterSubscription;
    }

    /**
     * @return bool
     */
    public function isActivated()
    {
        return $this->getCustomer()->getBillingAddress()->isActivated();
    }

    /**
     * @return bool
     */
    public function hasPasswordSet(): bool
    {
        return $this->password !== null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $defaultDeliveryAddress
     */
    public function setDefaultDeliveryAddress($defaultDeliveryAddress): void
    {
        $this->defaultDeliveryAddress = $defaultDeliveryAddress;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     */
    public function getRoleGroup()
    {
        return $this->roleGroup;
    }
}
