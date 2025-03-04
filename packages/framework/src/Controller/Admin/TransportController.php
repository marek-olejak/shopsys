<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Transport\TransportFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Grid\TransportGridFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TransportController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\Grid\TransportGridFactory $transportGridFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportDataFactoryInterface $transportDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected readonly TransportFacade $transportFacade,
        protected readonly TransportGridFactory $transportGridFactory,
        protected readonly TransportDataFactoryInterface $transportDataFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    #[Route(path: '/transport/new/')]
    public function newAction(Request $request)
    {
        $transportData = $this->transportDataFactory->create();

        $form = $this->createForm(TransportFormType::class, $transportData, [
            'transport' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transport = $this->transportFacade->create($transportData);

            $this->addSuccessFlashTwig(
                t('Shipping <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $transport->getName(),
                    'url' => $this->generateUrl('admin_transport_edit', ['id' => $transport->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_transportandpayment_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Transport/new.html.twig', [
            'form' => $form->createView(),
            'currencies' => $this->currencyFacade->getAllIndexedById(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     */
    #[Route(path: '/transport/edit/{id}', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, $id)
    {
        $transport = $this->transportFacade->getById($id);
        $transportData = $this->transportDataFactory->createFromTransport($transport);

        $form = $this->createForm(TransportFormType::class, $transportData, [
            'transport' => $transport,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->transportFacade->edit($transport, $transportData);

            $this->addSuccessFlashTwig(
                t('Shipping <strong><a href="{{ url }}">{{ name }}</a></strong> was modified'),
                [
                    'name' => $transport->getName(),
                    'url' => $this->generateUrl('admin_transport_edit', ['id' => $transport->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_transportandpayment_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlash(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing shipping - %name%', ['%name%' => $transport->getName()]),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Transport/edit.html.twig', [
            'form' => $form->createView(),
            'transport' => $transport,
            'currencies' => $this->currencyFacade->getAllIndexedById(),
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     */
    #[Route(path: '/transport/delete/{id}', requirements: ['id' => '\d+'])]
    public function deleteAction($id)
    {
        try {
            $transportName = $this->transportFacade->getById($id)->getName();

            $this->transportFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Shipping <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $transportName,
                ],
            );
        } catch (TransportNotFoundException $ex) {
            $this->addErrorFlash(t('Selected shipping doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_transportandpayment_list');
    }

    public function listAction()
    {
        $grid = $this->transportGridFactory->create();

        return $this->render('@ShopsysFramework/Admin/Content/Transport/list.html.twig', [
            'gridView' => $grid->createView(),
        ]);
    }
}
