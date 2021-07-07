<?php
namespace AFM\Registeraddress\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Sascha Löffler <lsascha@gmail.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use AFM\Registeraddress\Domain\Model\Address;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 *
 *
 * @package registeraddress
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class AddressController extends ActionController
{

    const MAILFORMAT_TXT = 'txt';
    const MAILFORMAT_HTML = 'html';
    const MAILFORMAT_TXTHTML = 'both';

    /**
     * addressRepository
     *
     * @var \AFM\Registeraddress\Domain\Repository\AddressRepository
     * @inject
     */
    protected $addressRepository;

    /**
     * This creates another stand-alone instance of the Fluid view to render a template
     * @param string $templateName the name of the template to use
     * @param string $format the format of the fluid template "html" or "txt"
     * @return StandaloneView the Fluid instance
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    protected function getPlainRenderer($templateName = 'default', $format = 'txt')
    {
        $view = $this->objectManager->get(StandaloneView::class);
        $view->getRequest()->setControllerExtensionName('registeraddress');
        $view->setFormat($format);


        // find plugin view configuration
        $frameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        // find partial paths from plugin configuration
        $partialPaths = $this->getViewProperty($frameworkConfiguration, 'partialRootPaths');
        // set configured partialPaths so they can be overwritten
        $view->setPartialRootPaths($partialPaths);

        $templatePaths = $this->getViewProperty($frameworkConfiguration, 'templateRootPaths');
        $view->setTemplateRootPaths($templatePaths); // set configured TemplateRootPaths from plugin

        $layoutPaths = $this->getViewProperty($frameworkConfiguration, 'layoutRootPaths');
        $view->setLayoutRootPaths($layoutPaths);


        $view->setTemplate($templateName);

        $view->assign('settings', $this->settings);
        return $view;
    }


    /**
     * Send email
     *
     * @param array $recipient
     * @param array $from
     * @param string $subject
     * @param string $bodyHTML
     * @param string $bodyPlain
     * @param array $replyTo
     * @return integer the number of recipients who were accepted for delivery
     * @throws \InvalidArgumentException
     */
    protected function sendEmail(array $recipient, array $from, $subject, $bodyHTML = '', $bodyPlain = '', array $replyTo = NULL)
    {

        if ($replyTo == NULL) {
            $replyTo = $from;
        }
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail
            ->setTo($recipient)
            ->setFrom($from)
            ->setReplyTo($replyTo)
            ->setSubject($subject);

        if ($bodyHTML !== '' && $bodyHTML !== NULL ) {
            $mail->addPart($bodyHTML, 'text/html');
        }
        if ($bodyPlain !== '' && $bodyPlain !== NULL ) {
            $mail->addPart($bodyPlain, 'text/plain');
        }

        return $mail->send();
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * sends an e-mail to users
     * @param string $recipientmails
     * @param string $templateName
     * @param array $data
     * @param string $type
     * @param string $subjectSuffix
     * @return void
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    protected function sendResponseMail( $recipientmails = '', $templateName, array $data = NULL, $type = self::MAILFORMAT_TXT, $subjectSuffix = '' )
    {
        $oldSpamProtectSetting = $GLOBALS['TSFE']->spamProtectEmailAddresses;
        // disable spamProtectEmailAddresses setting for e-mails
        $GLOBALS['TSFE']->spamProtectEmailAddresses = 0;

        $recipients = explode(',', $recipientmails);

        $from = [$this->settings['sendermail'] => $this->settings['sendername']];
        $subject = $this->settings['responseSubject'];

        // add suffix to subject if set
        if ($subjectSuffix != '') {
            $subject .= ' - ' . $subjectSuffix;
        }

        $mailHtml = '';
        $mailText = '';

        switch ($type) {
            case self::MAILFORMAT_TXT:
                $mailTextView = $this->getPlainRenderer($templateName, 'txt');
                break;
            case self::MAILFORMAT_HTML:
                $mailHtmlView = $this->getPlainRenderer($templateName, 'html');
                break;

            /** @noinspection PhpMissingBreakStatementInspection */
            case self::MAILFORMAT_TXTHTML:
                $mailHtmlView = $this->getPlainRenderer($templateName, 'html');
            default:
                $mailTextView = $this->getPlainRenderer($templateName, 'txt');
                break;
        }

        if (isset($mailTextView)) {
            $mailTextView->assignMultiple($data);
            $mailText = $mailTextView->render();
        }
        if (isset($mailHtmlView)) {
            $mailHtmlView->assignMultiple($data);
            $mailHtml = $mailHtmlView->render();
        }

        foreach ($recipients as $recipient) {
            $recipientMail = [trim($recipient)];
            $this->sendEmail(
                $recipientMail,
                $from,
                $subject,
                $mailHtml,
                $mailText
            );
        }

        // revert spamProtectSettings
        $GLOBALS['TSFE']->spamProtectEmailAddresses = $oldSpamProtectSetting;
    }


    /**
     * checks if address already exists
     * @param  string $address address to check
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface|array returns the already existing address or NULL if it is new
     */
    private function checkIfAddressExists($address)
    {
        $oldAddress = $this->addressRepository->findOneByEmailIgnoreHidden( $address );

        return isset($oldAddress) && $oldAddress ? $oldAddress : null;
    }

    /**
     * error action
     * @return null|string
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    protected function errorAction()
    {
        $this->forwardToReferringRequest();

        $errorMessage = LocalizationUtility::translate(
            'mail.registration.errorAction',
            'registeraddress'
        );
        return !$errorMessage ? 'Failed executing the action.' : $errorMessage;
    }

    /**
     * action form only
     *
     * @param Address $newAddress
     * @dontvalidate $newAddress
     * @return void
     */
    public function formOnlyAction(Address $newAddress = NULL)
    {
        $this->view->assign('newAddress', $newAddress);
    }

    /**
     * action new
     *
     * @param Address $newAddress
     * @dontvalidate $newAddress
     * @return void
     */
    public function newAction(Address $newAddress = NULL)
    {
        $this->view->assign('newAddress', $newAddress);
    }


    /**
     * action create
     *
     * @param Address $newAddress
     * @return void
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function createAction(Address $newAddress)
    {
        $oldAddress = $this->checkIfAddressExists($newAddress->getEmail());
        if ($oldAddress) {
            $this->view->assign('oldAddress', $oldAddress);
            $this->view->assign('alreadyExists', true);
        } else {
            $rnd = microtime(true).random_int(10000,90000);
            $regHash = sha1( $newAddress->getEmail().$rnd );
            $newAddress->setRegisteraddresshash( $regHash );
            $newAddress->setHidden(true);
            $newAddress->setConsent($this->settings['consent']);
            $this->addressRepository->add($newAddress);

            $data = [
                'address' => $newAddress,
                'hash' => $regHash
            ];

            $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
            $signalSlotDispatcher->dispatch(__CLASS__, 'createBeforePersist', [$newAddress]);

            $this->sendResponseMail(
                $newAddress->getEmail(),
                'Address/MailNewsletterRegistration',
                $data,
                $this->settings['mailformat'],
                LocalizationUtility::translate('mail.registration.subjectsuffix', 'registeraddress')
            );

            $persistenceManager = $this->objectManager->get(PersistenceManager::class);
            $persistenceManager->persistAll();
        }

        $this->view->assign('address', $newAddress);
    }

    /**
     * action inormation mail anforderung
     *
     * @param \string $email
     * @param integer $uid
     * @return void
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function informationAction($email, $uid)
    {
        $address = $this->addressRepository->findOneByEmailIgnoreHidden($email);

        if ($address && $address->getUid() == $uid) {
            $data = [
                'address' => $address,
                'hash' => $address->getRegisteraddresshash()
            ];

            if ($address->getHidden()) {
                // if e-mail still unapproved, send complete registration mail again
                $mailTemplate = 'Address/MailNewsletterRegistration';
            } else {
                // if e-mail already approved, just send information mail to edit or delete
                $mailTemplate = 'Address/MailNewsletterInformation';
            }
            $this->sendResponseMail(
                $address->getEmail(),
                $mailTemplate,
                $data,
                $this->settings['mailformat'],
                LocalizationUtility::translate('mail.info.subjectsuffix', 'registeraddress')
            );

            $this->view->assign('address', $address);
        }
    }

    /**
     * action send unsubscribe link to e-mail address
     *
     * @param \string $email
     * @return void
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function unsubscribeFormAction( $email = NULL )
    {
        $address = $this->addressRepository->findOneByEmail($email);

        if ($email != NULL) {
            $this->view->assign('email', $email);
        }
        if ($address) {
            $data = [
                'address' => $address,
                'hash' => $address->getRegisteraddresshash()
            ];

            $mailTemplate = 'Address/MailNewsletterUnsubscribe';
            $this->sendResponseMail(
                $address->getEmail(),
                $mailTemplate,
                $data,
                $this->settings['mailformat'],
                LocalizationUtility::translate('mail.unsubscribe.subjectsuffix', 'registeraddress')
            );

            $this->view->assign('address', $address);
        }
    }

    /**
     * action approve
     *
     * @param string $hash
     * @validate $hash NotEmpty
     * @param boolean $doApprove
     * @return void
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function approveAction($hash = NULL, $doApprove = false)
    {
        $address = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden($hash);

        $this->view->assign('hash', $hash);

        if ($address && $doApprove && $address->getHidden() === true) {
            $address->setHidden(false);
            $address->setModuleSysDmailHtml(true);

            $eigeneAnrede = $this->generateEigeneAnrede($address);
            $address->setEigeneAnrede($eigeneAnrede);

            $this->addressRepository->update($address);

            if ($this->settings['sendDeleteApproveMails']) {
                $data = [
                    'address' => $address
                ];
                $this->sendResponseMail(
                    $address->getEmail(),
                    'Address/MailNewsletterApproveSuccess',
                    $data,
                    $this->settings['mailformat'],
                    LocalizationUtility::translate(
                        'mail.approvesuccess.subjectsuffix',
                        'registeraddress'
                    )
                );
            }


            if ($this->settings['adminmail']) {
                $adminRecipient = $this->settings['adminmail'];
                $subject = $this->settings['approveSubject'];

                $this->sendResponseMail(
                    $adminRecipient,
                    'Address/Admin/MailAdminApprove',
                    ['address' => $address],
                    self::MAILFORMAT_TXT,
                    $subject
                );

            }

            $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
            $signalSlotDispatcher->dispatch(__CLASS__, 'approveBeforePersist', [$address]);

            $persistenceManager = $this->objectManager->get(PersistenceManager::class);
            $persistenceManager->persistAll();
            
            $this->view->assign('gotApproved', true);
        }

        $this->view->assign('address', $address);
        $this->view->assign('doApprove', $doApprove);
    }

    /**
     * action edit
     *
     * @param \string $hash
     * @validate $hash NotEmpty
     * @return void
     */
    public function editAction( $hash = NULL )
    {
        $address = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden($hash);

        if ($address) {
            $this->view->assign('hash', $hash);
            $this->view->assign('address', $address);
        }
    }

    /**
     * action update
     *
     * @param Address $address
     * @return void
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
    public function updateAction(Address $address)
    {
        $hash = $address->getRegisteraddresshash();

        // always save old e-mail address
        $addressOld = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden($hash);
        $address->setEmail($addressOld->getEmail());

        $eigeneAnrede = $this->generateEigeneAnrede($address);
        $address->setEigeneAnrede($eigeneAnrede);

        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->dispatch(__CLASS__, 'updateBeforePersist', [$address]);

        // send email to admin with updated data
        if ($this->settings['adminmail'] && !empty($this->settings['updateSubject'])) {
            $adminRecipient = $this->settings['adminmail'];
            $subject = $this->settings['updateSubject'];

            $this->sendResponseMail(
                $adminRecipient,
                'Address/Admin/MailAdminUpdate',
                ['address' => $address],
                self::MAILFORMAT_TXT,
                $subject
            );
        }

        $this->addressRepository->update($address);

        // Reset internal messages
        $flashMessageQueue = $this->controllerContext->getFlashMessageQueue();
        $flashMessageQueue->getAllMessagesAndFlush(AbstractMessage::OK);

        $this->addFlashMessage(LocalizationUtility::translate(
            'flashMessage.update',
            'registeraddress')
        );
        $this->redirect(
            'edit',
            'Address',
            'registeraddress',
            ['hash' => $address->getRegisteraddresshash()]
        );
    }


    /**
     * action delete
     *
     * @param \string $hash
     * @validate $hash NotEmpty
     * @param boolean $doDelete
     * @return void
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException
     */
    public function deleteAction($hash = NULL, $doDelete = false)
    {
        $address = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden($hash);
        $this->view->assign('hash', $hash);

        if ($address && $doDelete) {

            if ($this->settings['sendDeleteApproveMails']) {
                $data = [
                    'address' => $address
                ];
                $this->sendResponseMail(
                    $address->getEmail(),
                    'Address/MailNewsletterDeleteSuccess',
                    $data,
                    $this->settings['mailformat'],
                    LocalizationUtility::translate(
                        'mail.deletesuccess.subjectsuffix',
                        'registeraddress'
                    )
                );
            }

            if ($this->settings['adminmail']) {
                $adminRecipient = $this->settings['adminmail'];
                $subject = $this->settings['deleteSubject'];

                $this->sendResponseMail(
                    $adminRecipient,
                    'Address/Admin/MailAdminDelete',
                    ['address' => $address],
                    self::MAILFORMAT_TXT,
                    $subject
                );
            }
            $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
            $signalSlotDispatcher->dispatch(__CLASS__, 'deleteBeforePersist', [$address]);

            $this->addressRepository->remove($address);

        }
        $this->view->assign('address', $address);
        $this->view->assign('doDelete', $doDelete);
    }

    /**
     * Generates content for field eigene_anrede
     *
     * @param Address $address
     * @return string|null
     */
    protected function generateEigeneAnrede($address)
    {
        if ($address->getLastName()) {
            if ($address->getGender() === 'm') {
                $eigeneAnrede = LocalizationUtility::translate(
                        'salutationgeneration.lastname.m',
                        'registeraddress'
                    ) . $address->getLastName();

            } elseif ($address->getGender() === 'f') {
                $eigeneAnrede = LocalizationUtility::translate(
                        'salutationgeneration.lastname.f',
                        'registeraddress'
                    ) . $address->getLastName();
            }
        } elseif ($address->getFirstName()) {
            $eigeneAnrede = LocalizationUtility::translate(
                    'salutationgeneration.onlyfirstname',
                    'registeraddress'
                ) . $address->getFirstName();
        } else {
            $eigeneAnrede = LocalizationUtility::translate(
                'salutationgeneration.other',
                'registeraddress'
            );
        }
        return $eigeneAnrede;
    }
}
