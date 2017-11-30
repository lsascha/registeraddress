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

/**
 *
 *
 * @package registeraddress
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class AddressController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
     * @return \TYPO3\CMS\Fluid\View\StandaloneView the Fluid instance
     */
    protected function getPlainRenderer($templateName = 'default', $format = 'txt') {

        //$controllerName = $this->controllerContext->getRequest()->getControllerName();

        $view = $this->objectManager->get('TYPO3\CMS\Fluid\View\StandaloneView');
        $view->getRequest()->setControllerExtensionName('registeraddress');
        $view->setFormat($format);


        // find plugin view configuration
        $frameworkConfiguration = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
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
     * @param string $typeOfEmail
     * @param string $subject
     * @param string $bodyHTML
     * @param string $bodyPlain
     * @param array $replyTo
     * @return integer the number of recipients who were accepted for delivery
     */
    protected function sendEmail(array $recipient, array $from, $subject, $bodyHTML = '', $bodyPlain = '', array $replyTo = NULL) {

        if ( $replyTo == NULL ) {
            $replyTo = $from;
        }
        $mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Mail\MailMessage');
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

    /**
     * sends an e-mail to users
     * @param string $recipientmails
     * @param string $templateName
     * @param array $data
     * @param array $data
     * @param string $subjectSuffix
     * @return void
     */
    private function sendResponseMail( $recipientmails = '', $templateName, array $data = NULL, $type = self::MAILFORMAT_TXT, $subjectSuffix = '' ) {
        $recipients = explode(',', $recipientmails);

        $from = array($this->settings['sendermail'] => $this->settings['sendername']);
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

            case self::MAILFORMAT_TXTHTML:
                $mailHtmlView = $this->getPlainRenderer($templateName, 'html');
            default:
                $mailTextView = $this->getPlainRenderer($templateName, 'txt');
                break;
        }

        if ( isset($mailTextView) ) {
            $mailTextView->assignMultiple($data);
            $mailText = $mailTextView->render();
        }
        if ( isset($mailHtmlView) ) {
            $mailHtmlView->assignMultiple($data);
            $mailHtml = $mailHtmlView->render();
        }

        foreach ($recipients as $recipient) {
            $recipientMail = array(trim($recipient));
            $this->sendEmail(
                $recipientMail,
                $from,
                $subject,
                $mailHtml,
                $mailText
            );
        }
    }


    /**
     * checks if address already exists
     * @param  string $address address to check
     * @return \AFM\Registeraddress\Domain\Model\Address returns the already existing address or NULL if it is new
     */
    private function checkIfAddressExists($address) {
        //$oldAddress = $this->addressRepository->findOneByEmailIgnoreHidden( $address->getEmail() );
        $oldAddress = $this->addressRepository->findOneByEmailIgnoreHidden( $address );

        if ( isset($oldAddress) && $oldAddress ) {
            return $oldAddress;
        } else {
            return NULL;
        }
    }


    /**
     * action form only
     *
     * @param \AFM\Registeraddress\Domain\Model\Address $newAddress
     * @dontvalidate $newAddress
     * @return void
     */
    public function formOnlyAction(\AFM\Registeraddress\Domain\Model\Address $newAddress = NULL) {
        $this->view->assign('newAddress', $newAddress);
    }

    /**
     * action new
     *
     * @param \AFM\Registeraddress\Domain\Model\Address $newAddress
     * @dontvalidate $newAddress
     * @return void
     */
    public function newAction(\AFM\Registeraddress\Domain\Model\Address $newAddress = NULL) {
        $this->view->assign('newAddress', $newAddress);
    }


    /**
     * action create
     *
     * @param \AFM\Registeraddress\Domain\Model\Address $newAddress
     * @return void
     */
    public function createAction(\AFM\Registeraddress\Domain\Model\Address $newAddress) {

        $oldAddress = $this->checkIfAddressExists($newAddress->getEmail());
        if ($oldAddress) {
            $this->view->assign('oldAddress', $oldAddress);
            $this->view->assign('alreadyExists', true);
        } else {
            $rnd = microtime(true).mt_rand(10000,90000);
            $regHash = sha1( $newAddress->getEmail().$rnd );
            $newAddress->setRegisteraddresshash( $regHash );
            $newAddress->setHidden(true);
            $this->addressRepository->add($newAddress);

            $data = array(
                'gender' => $newAddress->getGender(),
                'vorname' => $newAddress->getFirstName(),
                'nachname' => $newAddress->getLastName(),
                'hash' => $regHash
            );
            $this->sendResponseMail( $newAddress->getEmail(), 'Address/MailNewsletterRegistration', $data, $this->settings['mailformat'], \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.registration.subjectsuffix', 'registeraddress'));

            $persistenceManager = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager'); 
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
     */
    public function informationAction($email, $uid) {
        $address = $this->addressRepository->findOneByEmailIgnoreHidden( $email );

        if ( $address && $address->getUid() == $uid ) {
            $data = array(
                'gender' => $address->getGender(),
                'vorname' => $address->getFirstName(),
                'nachname' => $address->getLastName(),
                'hash' => $address->getRegisteraddresshash()
            );

            if ($address->getHidden()) {
                // if e-mail still unapproved, send complete registration mail again
                $mailTemplate = 'Address/MailNewsletterRegistration';
            } else {
                // if e-mail already approved, just send information mail to edit or delete
                $mailTemplate = 'Address/MailNewsletterInformation';
            }
            $this->sendResponseMail( $address->getEmail(), $mailTemplate, $data, $this->settings['mailformat'], \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.info.subjectsuffix', 'registeraddress'));


            $this->view->assign('address', $address);
        }
    }


    /**
     * action send unsubscribe link to e-mail address
     *
     * @param \string $email
     * @return void
     */
    public function unsubscribeFormAction( $email = NULL ) {

        $address = $this->addressRepository->findOneByEmail( $email );

        if ($email != NULL) {
            $this->view->assign('email', $email);
        }
        if ( $address ) {
            $data = array(
                'gender' => $address->getGender(),
                'vorname' => $address->getFirstName(),
                'nachname' => $address->getLastName(),
                'hash' => $address->getRegisteraddresshash()
            );

            $mailTemplate = 'Address/MailNewsletterUnsubscribe';
            $this->sendResponseMail( $address->getEmail(), $mailTemplate, $data, $this->settings['mailformat'], \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.unsubscribe.subjectsuffix', 'registeraddress'));

            $this->view->assign('address', $address);
        }
    }


    /**
     * action approve
     *
     * @param \string $hash
     * @return void
     */
    public function approveAction( $hash = NULL ) {
        $address = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden( $hash );

        if ($address) {
            $address->setHidden(false);
            $address->setModuleSysDmailHtml(true);

            // create anrede
            if ( $address->getLastName() ) {
                if ($address->getGender() == 'm') {
                    $eigeneAnrede = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('salutationgeneration.lastname.m', 'registeraddress').$address->getLastName();
                } elseif ($address->getGender() == 'f') {
                    $eigeneAnrede = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('salutationgeneration.lastname.f', 'registeraddress').$address->getLastName();
                }
            } elseif ( $address->getFirstName() ) {
                $eigeneAnrede = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('salutationgeneration.onlyfirstname', 'registeraddress').$address->getFirstName();
            } else {
                $eigeneAnrede = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('salutationgeneration.other', 'registeraddress');
            }
            $address->setEigeneAnrede($eigeneAnrede);

            $this->view->assign('address', $address);

            $this->addressRepository->update($address);


            if ($this->settings['sendDeleteApproveMails']) {
                $data = array(
                    'address' => $address
                );
                $this->sendResponseMail($address->getEmail(), 'Address/MailNewsletterApproveSuccess', $data, $this->settings['mailformat'], \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.approvesuccess.subjectsuffix', 'registeraddress'));
            }


            if ($this->settings['adminmail']) {
                $adminRecipient = $this->settings['adminmail'];
                $subject = $this->settings['approveSubject'];

                $this->sendResponseMail($adminRecipient, 'Address/Admin/MailAdminApprove', ['address' => $address], self::MAILFORMAT_TXT, $subject);

            }

            $persistenceManager = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager'); 
            $persistenceManager->persistAll(); 
        }
    }

    /**
     * action edit
     *
     * @param \string $hash
     * @return void
     */
    public function editAction( $hash = NULL ) {
        $address = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden( $hash );

        if ( $address ) {
            $this->view->assign('hash', $hash);
            $this->view->assign('address', $address);
        }

        // fix addresses without hash
        $dofix = (bool) $this->settings['doHashFix'];
        if ($dofix) {
            $addresslist = $this->addressRepository->findAllByRegisteraddresshash( '' );
            foreach ($addresslist as $fixAddress) {
                if ($fixAddress->getRegisteraddresshash() == '') {
                    $rnd = microtime(true).mt_rand(10000,90000);
                    $regHash = sha1( $fixAddress->getEmail().$rnd );
                    $fixAddress->setRegisteraddresshash( $regHash );
                    $this->addressRepository->update($fixAddress);
                }
            }
        }
    }

    /**
     * action update
     *
     * @param \AFM\Registeraddress\Domain\Model\Address $address
     * @return void
     */
    public function updateAction(\AFM\Registeraddress\Domain\Model\Address $address) {
        $hash = $address->getRegisteraddresshash();

        // always save old e-mail address
        $addressOld = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden( $hash );
        $address->setEmail($addressOld->getEmail());

        $this->addressRepository->update($address);

        // Reset internal messages
        $flashMessageQueue = $this->controllerContext->getFlashMessageQueue();
        $flashMessageQueue->getAllMessagesAndFlush(\TYPO3\CMS\Core\Messaging\AbstractMessage::OK);

        $this->addFlashMessage(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('flashMessage.update', 'registeraddress'));
        $this->redirect('edit', 'Address', 'registeraddress', array('hash' => $address->getRegisteraddresshash() ));
        
    }


    /**
     * action delete
     *
     * @param \string $hash
     * @return void
     */
    public function deleteAction($hash = NULL) {
        $address = $this->addressRepository->findOneByRegisteraddresshashIgnoreHidden( $hash );

        if ( $address ) {
            $this->view->assign('address', $address);

            if ($this->settings['sendDeleteApproveMails']) {
                $data = array(
                    'address' => $address
                );
                $this->sendResponseMail($address->getEmail(), 'Address/MailNewsletterDeleteSuccess', $data, $this->settings['mailformat'], \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mail.deletesuccess.subjectsuffix', 'registeraddress'));
            }

            if ($this->settings['adminmail']) {
                $adminRecipient = $this->settings['adminmail'];
                $subject = $this->settings['deleteSubject'];

                $this->sendResponseMail($adminRecipient, 'Address/Admin/MailAdminDelete', ['address' => $address], self::MAILFORMAT_TXT, $subject);
            }

            $this->addressRepository->remove($address);


        }
    }


}
