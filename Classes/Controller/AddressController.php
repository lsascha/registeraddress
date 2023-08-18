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

use AFM\Registeraddress\Event\ApproveBeforePersistEvent;
use AFM\Registeraddress\Event\CreateBeforePersistEvent;
use AFM\Registeraddress\Event\DeleteBeforePersistEvent;
use AFM\Registeraddress\Event\UpdateBeforePersistEvent;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Generic\Exception\UnsupportedMethodException;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use AFM\Registeraddress\Domain\Model\Address;
use AFM\Registeraddress\Domain\Repository\AddressRepository;
use AFM\Registeraddress\Event\InitializeCreateActionEvent;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
     * @var AddressRepository
     */
    protected $addressRepository;

    public function injectAddressRepository(AddressRepository $addressRepository): void
    {
        $this->addressRepository = $addressRepository;
    }

    /**
     * persistenceManager
     *
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * This creates another stand-alone instance of the Fluid view to render a template
     * @param string $templateName the name of the template to use
     * @param string $format the format of the fluid template "html" or "txt"
     * @return StandaloneView the Fluid instance
     * @throws InvalidExtensionNameException
     */
    protected function getPlainRenderer($templateName = 'default', $format = 'txt')
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setRequest($this->request);
        $view->setFormat($format);

        // find plugin view configuration
        $frameworkConfiguration = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );

        // find paths from plugin configuration
        $view->getTemplatePaths()->fillFromConfigurationArray($frameworkConfiguration['view']);

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
     * @param string $returnPath
     * @param array|null $replyTo
     * @return integer the number of recipients who were accepted for delivery
     */
    protected function sendEmail(array $recipient, array $from, $subject, $bodyHTML = '', $bodyPlain = '', string $returnPath = '', array $replyTo = NULL)
    {

        if ($replyTo == NULL) {
            $replyTo = $from;
        }
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail
            ->setTo($recipient)
            ->setFrom($from)
            ->setReplyTo($replyTo)
            ->setSubject($subject)
            ->setReturnPath($returnPath);

        if ($bodyHTML !== '' && $bodyHTML !== NULL ) {
            $mail->html($bodyHTML);
        }
        if ($bodyPlain !== '' && $bodyPlain !== NULL ) {
            $mail->text($bodyPlain);
        }

        return $mail->send();
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * sends an e-mail to users
     * @param string $templateName
     * @param string $recipientmails
     * @param array $data
     * @param string $type
     * @param string $subjectSuffix
     * @return void
     * @throws \InvalidArgumentException
     * @throws InvalidExtensionNameException
     */
    protected function sendResponseMail( string $templateName, string $recipientmails = '', array $data = NULL, $type = self::MAILFORMAT_TXT, $subjectSuffix = '' )
    {
        $oldSpamProtectSetting = $GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses'] ?? '';
        // disable spamProtectEmailAddresses setting for e-mails
        $GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses'] = 0;

        $recipients = explode(',', $recipientmails);

        $from = [$this->settings['sendermail'] => $this->settings['sendername']];
        $subject = $this->settings['responseSubject'];

        $replyTo = GeneralUtility::trimExplode(',',$this->settings['replyTo']);
        $returnPath = $this->settings['returnPath'];

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
                $mailText,
                $returnPath,
                $replyTo
            );
        }

        // revert spamProtectSettings
        $GLOBALS['TSFE']->config['config']['spamProtectEmailAddresses'] = $oldSpamProtectSetting;
    }


    /**
     * checks if address already exists
     * @param  string $address address to check
     * @return QueryResultInterface|array returns the already existing address or NULL if it is new
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
     * @IgnoreValidation
     * @return void
     */
    public function formOnlyAction(Address $newAddress = NULL): ResponseInterface
    {
        $this->view->assign('newAddress', $newAddress);
        return $this->htmlResponse();
    }

    /**
     * action new
     *
     * @param Address $newAddress
     * @IgnoreValidation
     * @return void
     */
    public function newAction(Address $newAddress = NULL): ResponseInterface
    {
        $this->view->assign('newAddress', $newAddress);
        return $this->htmlResponse();
    }


    public function initializeCreateAction(): void
    {
        $this->eventDispatcher->dispatch(new InitializeCreateActionEvent($this->arguments, $this->request));
    }

    /**
     * action create
     *
     * @param Address $newAddress
     * @return void
     * @throws \InvalidArgumentException
     * @throws IllegalObjectTypeException
     * @throws InvalidExtensionNameException
     */
    public function createAction(Address $newAddress): ResponseInterface
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

            $this->eventDispatcher->dispatch(new CreateBeforePersistEvent($newAddress));

            $this->sendResponseMail(
                'Address/MailNewsletterRegistration',
                $newAddress->getEmail(),
                $data,
                $this->settings['mailformat'],
                LocalizationUtility::translate('mail.registration.subjectsuffix', 'registeraddress')
            );

            $this->persistenceManager->persistAll();
        }

        $this->view->assign('address', $newAddress);
        return $this->htmlResponse();
    }

    /**
     * action inormation mail anforderung
     *
     * @param \string $email
     * @param integer $uid
     * @return void
     * @throws \InvalidArgumentException
     * @throws InvalidExtensionNameException
     */
    public function informationAction($email, $uid): ResponseInterface
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
                $mailTemplate,
                $address->getEmail(),
                $data,
                $this->settings['mailformat'],
                LocalizationUtility::translate('mail.info.subjectsuffix', 'registeraddress')
            );

            $this->view->assign('address', $address);
        }
        return $this->htmlResponse();
    }

    /**
     * action send unsubscribe link to e-mail address
     *
     * @param \string $email
     * @return void
     * @throws \InvalidArgumentException
     * @throws UnsupportedMethodException
     * @throws InvalidExtensionNameException
     */
    public function unsubscribeFormAction( $email = NULL ): ResponseInterface
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
                $mailTemplate,
                $address->getEmail(),
                $data,
                $this->settings['mailformat'],
                LocalizationUtility::translate('mail.unsubscribe.subjectsuffix', 'registeraddress')
            );

            $this->view->assign('address', $address);
        }
        return $this->htmlResponse();
    }

    /**
     * action approve
     *
     * @param string $hash
     * @Validate("NotEmpty", param="hash")
     * @param boolean $doApprove
     * @return void
     * @throws \InvalidArgumentException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws InvalidExtensionNameException
     */
    public function approveAction(string $hash = '', bool $doApprove = false): ResponseInterface
    {
        /** @var Address $address */
        $address = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden($hash);

        $this->view->assign('hash', $hash);

        if ($address && $address->getHidden() && $doApprove) {
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
                    'Address/MailNewsletterApproveSuccess',
                    $address->getEmail(),
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
                    'Address/Admin/MailAdminApprove',
                    $adminRecipient,
                    ['address' => $address],
                    self::MAILFORMAT_TXT,
                    $subject
                );

            }

            $this->eventDispatcher->dispatch(new ApproveBeforePersistEvent($address));

            $this->persistenceManager->persistAll();
        } else {
            $this->view->assign('alreadyApproved',true);
        }

        $this->view->assign('address', $address);
        $this->view->assign('doApprove', $doApprove);
        return $this->htmlResponse();
    }

    /**
     * action edit
     *
     * @param \string $hash
     * @Validate("NotEmpty", param="hash")
     * @return void
     */
    public function editAction(string $hash = '' ): ResponseInterface
    {
        $address = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden($hash);

        if ($address) {
            $this->view->assign('hash', $hash);
            $this->view->assign('address', $address);
        }
        return $this->htmlResponse();
    }

    /**
     * action update
     *
     * @param Address $address
     * @return void
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
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

        $this->eventDispatcher->dispatch(new UpdateBeforePersistEvent($address));

        // send email to admin with updated data
        if ($this->settings['adminmail'] && !empty($this->settings['updateSubject'])) {
            $adminRecipient = $this->settings['adminmail'];
            $subject = $this->settings['updateSubject'];

            $this->sendResponseMail(
                'Address/Admin/MailAdminUpdate',
                $adminRecipient,
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
     * @Validate("NotEmpty", param="hash")
     * @param boolean $doDelete
     * @return void
     * @throws \InvalidArgumentException
     * @throws IllegalObjectTypeException
     * @throws InvalidExtensionNameException
     */
    public function deleteAction(string $hash = '', $doDelete = false): ResponseInterface
    {
        $address = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden($hash);
        $this->view->assign('hash', $hash);

        if ($address && $doDelete) {

            if ($this->settings['sendDeleteApproveMails']) {
                $data = [
                    'address' => $address
                ];
                $this->sendResponseMail(
                    'Address/MailNewsletterDeleteSuccess',
                    $address->getEmail(),
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
                    'Address/Admin/MailAdminDelete',
                    $adminRecipient,
                    ['address' => $address],
                    self::MAILFORMAT_TXT,
                    $subject
                );
            }
            $this->eventDispatcher->dispatch(new DeleteBeforePersistEvent($address));

            $this->addressRepository->remove($address);

        }
        $this->view->assign('address', $address);
        $this->view->assign('doDelete', $doDelete);
        return $this->htmlResponse();
    }

    /**
     * Generates content for field eigene_anrede
     *
     * @param Address $address
     * @return string|null
     */
    protected function generateEigeneAnrede($address)
    {
        $title = $address->getTitle() ? $address->getTitle() . ' ' : '';
        if ($address->getLastName()) {
            if ($address->getGender() === 'm') {
                $eigeneAnrede = LocalizationUtility::translate(
                        'salutationgeneration.lastname.m',
                        'registeraddress'
                    ) . $title . $address->getLastName();

            } elseif ($address->getGender() === 'f') {
                $eigeneAnrede = LocalizationUtility::translate(
                        'salutationgeneration.lastname.f',
                        'registeraddress'
                    ) . $title . $address->getLastName();
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
        return $eigeneAnrede ?? '';
    }
}
