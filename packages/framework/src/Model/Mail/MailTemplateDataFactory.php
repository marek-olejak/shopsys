<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMail;

class MailTemplateDataFactory implements MailTemplateDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactoryInterface $uploadedFileDataFactory
     */
    public function __construct(protected readonly UploadedFileDataFactoryInterface $uploadedFileDataFactory)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData
     */
    protected function createInstance(): MailTemplateData
    {
        return new MailTemplateData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData
     */
    public function create(): MailTemplateData
    {
        $mailTemplateData = $this->createInstance();
        $mailTemplateData->attachments = $this->uploadedFileDataFactory->create();

        return $mailTemplateData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData
     */
    public function createFromMailTemplate(MailTemplate $mailTemplate): MailTemplateData
    {
        $mailTemplateData = $this->createInstance();
        $this->fillFromMailTemplate($mailTemplateData, $mailTemplate);
        $mailTemplateData->attachments = $this->uploadedFileDataFactory->createByEntity($mailTemplate);

        return $mailTemplateData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData $mailTemplateData
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate $mailTemplate
     */
    protected function fillFromMailTemplate(MailTemplateData $mailTemplateData, MailTemplate $mailTemplate)
    {
        $mailTemplateData->name = $mailTemplate->getName();
        $mailTemplateData->bccEmail = $mailTemplate->getBccEmail();
        $mailTemplateData->subject = $mailTemplate->getSubject();
        $mailTemplateData->body = $mailTemplate->getBody();
        $mailTemplateData->sendMail = $mailTemplate->isSendMail();
        $mailTemplateData->orderStatus = $mailTemplate->getOrderStatus();
        $mailTemplateData->domainId = $mailTemplate->getDomainId();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[] $orderStatuses
     * @param \Shopsys\FrameworkBundle\Model\Mail\MailTemplate[] $mailTemplates
     * @return \Shopsys\FrameworkBundle\Model\Mail\MailTemplateData[]
     */
    public function createFromOrderStatuses(array $orderStatuses, array $mailTemplates): array
    {
        $orderStatusMailTemplatesData = [];

        foreach ($orderStatuses as $orderStatus) {
            $mailTemplate = OrderMail::findMailTemplateForOrderStatus($mailTemplates, $orderStatus);

            if ($mailTemplate !== null) {
                $orderStatusMailTemplateData = $this->createFromMailTemplate($mailTemplate);
            } else {
                $orderStatusMailTemplateData = $this->create();
            }
            $orderStatusMailTemplateData->name = OrderMail::getMailTemplateNameByStatus($orderStatus);

            $orderStatusMailTemplatesData[$orderStatus->getId()] = $orderStatusMailTemplateData;
        }

        return $orderStatusMailTemplatesData;
    }
}
