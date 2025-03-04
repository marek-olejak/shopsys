<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Payment\Payment;
use App\Model\Payment\PaymentDataFactory;
use App\Model\Payment\PaymentFacade;
use App\Model\Transport\Transport;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Component\Constraints\PaymentInCart;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PaymentInCartValidationTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private PaymentFacade $paymentFacade;

    /**
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    public function testUnavailablePayment(): void
    {
        $response = $this->addNonExistingPaymentToDemoCart();

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInCart::UNAVAILABLE_PAYMENT_ERROR, $validationErrors['input.paymentUuid'][0]['code']);
    }

    public function testHiddenPayment(): void
    {
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->hidePayment($payment);
        $response = $this->addPaymentToDemoCart($payment->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInCart::UNAVAILABLE_PAYMENT_ERROR, $validationErrors['input.paymentUuid'][0]['code']);
    }

    public function testDeletedPayment(): void
    {
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->paymentFacade->deleteById($payment->getId());
        $response = $this->addPaymentToDemoCart($payment->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInCart::UNAVAILABLE_PAYMENT_ERROR, $validationErrors['input.paymentUuid'][0]['code']);
    }

    public function testDisabledPayment(): void
    {
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->disablePaymentOnFirstDomain($payment);
        $response = $this->addPaymentToDemoCart($payment->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInCart::UNAVAILABLE_PAYMENT_ERROR, $validationErrors['input.paymentUuid'][0]['code']);
    }

    public function testInvalidPaymentTransportCombination(): void
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_DRONE, Transport::class);
        $this->addTransportToDemoCart($transport->getUuid());
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_DOMAIN . Domain::FIRST_DOMAIN_ID, Payment::class);
        $response = $this->addPaymentToDemoCart($payment->getUuid());

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsExtensionValidationFromResponse($response);
        $this->assertSame(PaymentInCart::INVALID_PAYMENT_TRANSPORT_COMBINATION_ERROR, $validationErrors['input'][0]['code']);
    }

    /**
     * @param string $paymentUuid
     * @return array
     */
    private function addPaymentToDemoCart(string $paymentUuid): array
    {
        $changePaymentInCartMutation = '
            mutation {
                ChangePaymentInCart(input:{
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    paymentUuid: "' . $paymentUuid . '"
                }) {
                    uuid
                }
            }
        ';

        return $this->getResponseContentForQuery($changePaymentInCartMutation);
    }

    /**
     * @return array
     */
    private function addNonExistingPaymentToDemoCart(): array
    {
        return $this->addPaymentToDemoCart(Uuid::uuid4()->toString());
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     */
    private function hidePayment(Payment $payment): void
    {
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $paymentData->hidden = true;
        $this->paymentFacade->edit($payment, $paymentData);
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     */
    private function disablePaymentOnFirstDomain(Payment $payment): void
    {
        $paymentData = $this->paymentDataFactory->createFromPayment($payment);
        $paymentData->enabled[1] = false;
        $this->paymentFacade->edit($payment, $paymentData);
    }

    /**
     * @param string $transportUuid
     */
    private function addTransportToDemoCart(string $transportUuid): void
    {
        $changeTransportInCartMutation = '
            mutation {
                ChangeTransportInCart(input:{
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                    transportUuid: "' . $transportUuid . '"
                }) {
                    uuid
                }
            }
        ';

        $this->getResponseContentForQuery($changeTransportInCartMutation);
    }
}
