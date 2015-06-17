<?php

namespace SS6\ShopBundle\Controller\Front;

use SS6\ShopBundle\Controller\Front\BaseController;
use SS6\ShopBundle\Form\Front\Customer\CustomerFormType;
use SS6\ShopBundle\Model\Customer\CustomerData;
use SS6\ShopBundle\Model\Customer\CustomerEditFacade;
use SS6\ShopBundle\Model\Domain\Domain;
use SS6\ShopBundle\Model\Order\Item\OrderItemPriceCalculation;
use SS6\ShopBundle\Model\Order\OrderFacade;
use SS6\ShopBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\Request;

class CustomerController extends BaseController {

	/**
	 * @var \SS6\ShopBundle\Model\Customer\CustomerEditFacade
	 */
	private $customerEditFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Domain\Domain
	 */
	private $domain;

	/**
	 * @var \SS6\ShopBundle\Model\Order\Item\OrderItemPriceCalculation
	 */
	private $orderItemPriceCalculation;

	/**
	 * @var \SS6\ShopBundle\Model\Order\OrderFacade
	 */
	private $orderFacade;

	public function __construct(
		CustomerEditFacade $customerEditFacade,
		OrderFacade $orderFacade,
		Domain $domain,
		OrderItemPriceCalculation $orderItemPriceCalculation
	) {
		$this->customerEditFacade = $customerEditFacade;
		$this->orderFacade = $orderFacade;
		$this->domain = $domain;
		$this->orderItemPriceCalculation = $orderItemPriceCalculation;
	}

	public function editAction(Request $request) {
		if (!$this->isGranted(Roles::ROLE_CUSTOMER)) {
			$this->getFlashMessageSender()->addErrorFlash('Pro přístup na tuto stránku musíte být přihlášeni');
			return $this->redirect($this->generateUrl('front_login'));
		}

		$user = $this->getUser();

		$form = $this->createForm(new CustomerFormType());

		$customerData = new CustomerData();

		if (!$form->isSubmitted()) {
			$customerData->setFromEntity($user);
		}

		$form->setData($customerData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$customerData = $form->getData();
			$this->customerEditFacade->editByCustomer(
				$user->getId(),
				$customerData
			);

			$this->getFlashMessageSender()->addSuccessFlash('Vaše údaje byly úspěšně zaktualizovány');
			return $this->redirect($this->generateUrl('front_customer_edit'));
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$this->getFlashMessageSender()->addErrorFlash('Prosím zkontrolujte si správnost vyplnění všech údajů');
		}

		return $this->render('@SS6Shop/Front/Content/Customer/edit.html.twig', [
			'form' => $form->createView(),
		]);
	}

	public function ordersAction() {
		if (!$this->isGranted(Roles::ROLE_CUSTOMER)) {
			$this->getFlashMessageSender()->addErrorFlash('Pro přístup na tuto stránku musíte být přihlášeni');
			return $this->redirect($this->generateUrl('front_login'));
		}

		$user = $this->getUser();
		/* @var $user \SS6\ShopBundle\Model\Customer\User */

		$orders = $this->orderFacade->getCustomerOrderList($user);
		return $this->render('@SS6Shop/Front/Content/Customer/orders.html.twig', [
			'orders' => $orders,
		]);
	}

	/**
	 * @param string $orderNumber
	 */
	public function orderDetailRegisteredAction($orderNumber) {
		return $this->orderDetailAction(null, $orderNumber);
	}

	/**
	 * @param string $urlHash
	 */
	public function orderDetailUnregisteredAction($urlHash) {
		return $this->orderDetailAction($urlHash, null);
	}

	/**
	 * @param string $urlHash
	 * @param string $orderNumber
	 */
	private function orderDetailAction($urlHash = null, $orderNumber = null) {
		if ($orderNumber !== null) {
			if (!$this->isGranted(Roles::ROLE_CUSTOMER)) {
				$this->getFlashMessageSender()->addErrorFlash('Pro přístup na tuto stránku musíte být přihlášeni');
				return $this->redirect($this->generateUrl('front_login'));
			}

			$user = $this->getUser();
			try {
				$order = $this->orderFacade->getByOrderNumberAndUser($orderNumber, $user);
				/* @var $order \SS6\ShopBundle\Model\Order\Order */
			} catch (\SS6\ShopBundle\Model\Order\Exception\OrderNotFoundException $ex) {
				$this->getFlashMessageSender()->addErrorFlash('Objednávka nebyla nalezena');
				return $this->redirect($this->generateUrl('front_customer_orders'));
			}
		} else {
			$order = $this->orderFacade->getByUrlHashAndDomain($urlHash, $this->domain->getId());
			/* @var $order \SS6\ShopBundle\Model\Order\Order */
		}

		$orderItemTotalPricesById = $this->orderItemPriceCalculation->calculateTotalPricesIndexedById($order->getItems());

		return $this->render('@SS6Shop/Front/Content/Customer/orderDetail.html.twig', [
			'order' => $order,
			'orderItemTotalPricesById' => $orderItemTotalPricesById,
		]);

	}

}
