<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;

class FriendlyUrlRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getFriendlyUrlRepository()
    {
        return $this->em->getRepository(FriendlyUrl::class);
    }

    /**
     * @param int $domainId
     * @param string $slug
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function findByDomainIdAndSlug($domainId, $slug)
    {
        return $this->getFriendlyUrlRepository()->findOneBy(
            [
                'domainId' => $domainId,
                'slug' => $slug,
            ],
        );
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
     */
    public function getMainFriendlyUrl($domainId, $routeName, $entityId)
    {
        $criteria = [
            'domainId' => $domainId,
            'routeName' => $routeName,
            'entityId' => $entityId,
            'main' => true,
        ];
        $friendlyUrl = $this->getFriendlyUrlRepository()->findOneBy($criteria);

        if ($friendlyUrl === null) {
            throw new FriendlyUrlNotFoundException();
        }

        return $friendlyUrl;
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function findMainFriendlyUrl($domainId, $routeName, $entityId)
    {
        $criteria = [
            'domainId' => $domainId,
            'routeName' => $routeName,
            'entityId' => $entityId,
            'main' => true,
        ];

        return $this->getFriendlyUrlRepository()->findOneBy($criteria);
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @return array<int, \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null>
     */
    public function getMainFriendlyUrlsIndexedByDomains(string $routeName, int $entityId): array
    {
        return $this->em->createQueryBuilder()
            ->select('f')
            ->from(FriendlyUrl::class, 'f', 'f.domainId')
            ->andWhere('f.routeName = :routeName')
            ->setParameter('routeName', $routeName)
            ->andWhere('f.entityId = :entityId')
            ->setParameter('entityId', $entityId)
            ->andWhere('f.main = TRUE')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityId($routeName, $entityId)
    {
        $criteria = [
            'routeName' => $routeName,
            'entityId' => $entityId,
        ];

        return $this->getFriendlyUrlRepository()->findBy(
            $criteria,
            [
                'domainId' => 'ASC',
                'slug' => 'ASC',
            ],
        );
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getAllByRouteNameAndEntityIdAndDomainId($routeName, $entityId, $domainId)
    {
        $criteria = [
            'routeName' => $routeName,
            'entityId' => $entityId,
            'domainId' => $domainId,
        ];

        return $this->getFriendlyUrlRepository()->findBy($criteria);
    }

    /**
     * @param object[]|int[] $entitiesOrEntityIds
     * @param string $routeName
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function getMainFriendlyUrlsByEntitiesIndexedByEntityId(array $entitiesOrEntityIds, $routeName, $domainId)
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('f')
            ->from(FriendlyUrl::class, 'f', 'f.entityId')
            ->andWhere('f.routeName = :routeName')->setParameter('routeName', $routeName)
            ->andWhere('f.entityId IN (:entities)')->setParameter('entities', $entitiesOrEntityIds)
            ->andWhere('f.domainId = :domainId')->setParameter('domainId', $domainId)
            ->andWhere('f.main = TRUE');

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param int $domainId
     * @param string $routeName
     * @param int $entityId
     * @return string[]
     */
    public function getAllSlugsByRouteNameAndDomainId(int $domainId, string $routeName, int $entityId): array
    {
        $friendlyUrls = $this->getFriendlyUrlRepository()->findBy([
            'domainId' => $domainId,
            'routeName' => $routeName,
            'entityId' => $entityId,
        ]);

        return array_map(fn (FriendlyUrl $friendlyUrl) => $friendlyUrl->getSlug(), $friendlyUrls);
    }
}
