<?php

namespace AFM\Registeraddress\Service;

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

use AFM\Registeraddress\Domain\Model\Address;
use AFM\Registeraddress\Domain\Repository\AddressRepository;
use AFM\Registeraddress\Event\CreateBeforePersistEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class AddressService implements SingletonInterface
{


    public function __construct(
        protected array $settings,
        protected AddressRepository $addressRepository,
        protected PersistenceManager $persistenceManager,
        protected MailService $mailService,
        protected EventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function createAddress(Address $newAddress) {
        if(!$this->checkIfAddressExists($newAddress->getEmail())) {
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

            $this->mailService->sendResponseMail(
                'Address/MailNewsletterRegistration',
                $newAddress->getEmail(),
                $data,
                $this->settings['mailformat'],
                LocalizationUtility::translate('mail.registration.subjectsuffix', 'registeraddress')
            );

            $this->persistenceManager->persistAll();
        }


    }

    public function checkIfAddressExists($address): ?Address
    {
        $oldAddress = $this->addressRepository->findOneByEmailIgnoreHidden( $address );
        return isset($oldAddress) && $oldAddress ? $oldAddress : null;
    }
}
