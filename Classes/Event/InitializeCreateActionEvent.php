<?php
declare(strict_types=1);

namespace AFM\Registeraddress\Event;

use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Mvc\Request;

class InitializeCreateActionEvent
{

    protected Arguments $arguments;

    protected Request $request;

    public function __construct(Arguments $arguments, Request $request)
    {
        $this->arguments = $arguments;
        $this->request = $request;
    }

    public function getArguments(): Arguments
    {
        return $this->arguments;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

}
