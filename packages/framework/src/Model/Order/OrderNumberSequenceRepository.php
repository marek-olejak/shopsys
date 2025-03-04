<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Exception;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNumberSequenceNotFoundException;

class OrderNumberSequenceRepository
{
    protected const int ID = 1;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\FrameworkBundle\Model\Order\OrderNumberSequence>
     */
    protected function getOrderNumberSequenceRepository(): EntityRepository
    {
        return $this->em->getRepository(OrderNumberSequence::class);
    }

    /**
     * @return string
     */
    public function getNextNumber(): string
    {
        try {
            $this->em->beginTransaction();

            $requestedNumber = time();

            /** @var \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequence|null $orderNumberSequence */
            $orderNumberSequence = $this->getOrderNumberSequenceRepository()->find(
                static::ID,
                LockMode::PESSIMISTIC_WRITE,
            );

            if ($orderNumberSequence === null) {
                throw new OrderNumberSequenceNotFoundException(
                    'Order number sequence ID ' . static::ID . ' not found.',
                );
            }

            $lastNumber = $orderNumberSequence->getNumber();

            if ($requestedNumber <= $lastNumber) {
                $requestedNumber = (int)$lastNumber + 1;
            }

            $orderNumberSequence->setNumber((string)$requestedNumber);

            $this->em->flush();
            $this->em->commit();
        } catch (Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $orderNumberSequence->getNumber();
    }
}
