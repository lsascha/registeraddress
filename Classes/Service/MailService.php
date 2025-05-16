<?php

namespace AFM\Registeraddress\Service;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidExtensionNameException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2025 Karsten Nowak <nowak@undkonsorten.com>
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

class MailService implements SingletonInterface
{

    const MAILFORMAT_TXT = 'txt';
    const MAILFORMAT_HTML = 'html';
    const MAILFORMAT_TXTHTML = 'both';

    protected array $settings;

    public function __construct(
        array $settings,
    )
    {
        $this->settings = $settings;
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
    public function sendResponseMail(
        string $templateName,
        string $recipientmails = '',
        array $data = NULL,
        string $type = self::MAILFORMAT_TXT,
        string $subjectSuffix = ''
    ): void
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
     * This creates another stand-alone instance of the Fluid view to render a template
     * @param string $templateName the name of the template to use
     * @param string $format the format of the fluid template "html" or "txt"
     * @return StandaloneView the Fluid instance
     * @throws InvalidExtensionNameException
     */
    protected function getPlainRenderer(
        $templateName = 'default',
        $format = 'txt'
    ): StandaloneView
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setRequest($this->getRequest());
        $view->setFormat($format);


        // find plugin view configuration
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $frameworkConfiguration = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK, 'registeraddress');

        // find paths from plugin configuration
        $view->getRenderingContext()->getTemplatePaths()->fillFromConfigurationArray($frameworkConfiguration['view']);

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
        $mail = GeneralUtility::makeInstance(MailMessage::class);
        $mail
            ->setTo($recipient)
            ->setFrom($from)
            ->setSubject($subject);

        if (!empty(array_filter($replyTo ?? []))) {
            $mail->setReplyTo($replyTo);
        }
        if ($returnPath) {
            $mail->setReturnPath($returnPath);
        }

        if ($bodyHTML !== '' && $bodyHTML !== NULL ) {
            $mail->html($bodyHTML);
        }
        if ($bodyPlain !== '' && $bodyPlain !== NULL ) {
            $mail->text($bodyPlain);
        }

        return $mail->send();
    }

    protected function getRequest()
    {
        return $GLOBALS['TYPO3_REQUEST'] ?? null;
    }

}
