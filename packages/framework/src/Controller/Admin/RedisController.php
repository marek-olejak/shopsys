<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedisController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/redis/clean-storefront-query-cache')]
    public function cleanAction(Request $request): Response
    {
        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache();

        $this->addSuccessFlashTwig(
            t('Storefront queries cache has been cleaned.'),
        );

        return $this->redirectToRoute('admin_redis_show');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/superadmin/redis/show-clean-storefront-query-cache')]
    public function showAction(Request $request): Response
    {
        return $this->render('Admin/Content/StorefrontCache/clean.html.twig');
    }
}
