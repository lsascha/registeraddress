<?php

namespace AFM\Registeraddress\ViewHelpers;



use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;


class GetCaptchaViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    protected $escapeOutput = false;

    /**
     * Get the array value from given key
     *
     * @param string $key
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
    )
    {
        $result = '';
        $settings = $renderingContext->getVariableProvider()->get('settings');

        if (
            class_exists(\JambageCom\Div2007\Captcha\CaptchaManager::class) &&
            is_object(
                $captcha = \JambageCom\Div2007\Captcha\CaptchaManager::getCaptcha(
                    'registeraddress',
                    $settings['captcha']
                )
            )
        ) {

            $captchaMarker = array();
            $markerFilled = $captcha->addGlobalMarkers(
                $captchaMarker,
                true
            );

            $result = $captchaMarker['###CAPTCHA_IMAGE###'];
        }
                
        return $result;
    }


    /**
     * @return DatabaseConnection
     */
    protected function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
