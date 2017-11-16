<?php
namespace AFM\Registeraddress\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Sascha Löffler <sl@afm-koeln.de>, Atelier für Mediengestaltung
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
class AddressRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

    /**
     * Returns an Object by email address and ignores hidden field.
     *
     * @param \String $email
     * @return Tx_Extbase_Persistence_QueryResultInterface|array
     *         all objects, will be empty if no objects are found, will be an array if raw query results are enabled
     */
    public function findOneByEmailIgnoreHidden($email) {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(TRUE);
        //$query->getQuerySettings()->setRespectStoragePage(FALSE);

        $query->matching(
            $query->equals('email', $email )
        );

        return $query->execute()->getFirst();
    }

    /**
     * Returns an Object by hash and ignores hidden field.
     *
     * @param \String $hash
     * @return Tx_Extbase_Persistence_QueryResultInterface|array
     *         all objects, will be empty if no objects are found, will be an array if raw query results are enabled
     */
    public function findOneByRegisteraddresshashIgnoreHidden($hash) {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(TRUE);
        //$query->getQuerySettings()->setRespectStoragePage(FALSE);

        $query->matching(
            $query->equals('registeraddresshash', $hash )
        );

        return $query->execute()->getFirst();
    }

    /**
     * Returns all Objects by hash.
     *
     * @param \String $hash
     * @return Tx_Extbase_Persistence_QueryResultInterface|array
     *         all objects, will be empty if no objects are found, will be an array if raw query results are enabled
     */
    public function findAllByRegisteraddresshash($hash) {
        $query = $this->createQuery();

        $query->matching(
            $query->equals('registeraddresshash', $hash )
        );

        return $query->execute();
    }

    /**
     * Returns an Object by hash and ignores hidden field.
     *
     * @param \String $uid
     * @return Tx_Extbase_Persistence_QueryResultInterface|array
     *         all objects, will be empty if no objects are found, will be an array if raw query results are enabled
     */
    public function findOneByUidIgnoreHidden($uid) {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(TRUE);
        //$query->getQuerySettings()->setRespectStoragePage(FALSE);

        $query->matching(
            $query->equals('uid', $uid )
        );

        return $query->execute()->getFirst();
    }

}
