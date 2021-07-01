<?php

namespace AFM\Registeraddress\Domain\Model;

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

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 *
 *
 * @package registeraddress
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Address extends AbstractEntity
{

    /**
     * name
     *
     * @var string
     */
    protected $name;

    /**
     * gender
     *
     * @var string
     */
    protected $gender;

    /**
     * firstName
     *
     * @var string
     */
    protected $firstName;

    /**
     * middleName
     *
     * @var string
     */
    protected $middleName;

    /**
     * lastName
     *
     * @var string
     */
    protected $lastName;

    /**
     * email
     *
     * @var string
     * @TYPO3\CMS\Extbase\Annotation\Validate("NotEmpty")
     * @TYPO3\CMS\Extbase\Annotation\Validate("EmailAddress")
     */
    protected $email;

    /**
     * hidden
     *
     * @var boolean
     */
    protected $hidden;

    /**
     * registeraddresshash
     *
     * @var string
     */
    protected $registeraddresshash;

    /**
     * module_sys_dmail_html
     *
     * @var boolean
     */
    protected $moduleSysDmailHtml;

    /**
     * eigeneAnrede
     *
     * @var string
     */
    protected $eigeneAnrede;

    /**
     * tx_directmailsubscription_localgender
     *
     * @var string
     */
    protected $txDirectmailsubscriptionLocalgender;

    /**
     * Consent
     *
     * @var string
     */
    protected $consent;

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name
     *
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Returns the gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Sets the gender
     *
     * @param string $gender
     * @return void
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * Returns the firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Sets the firstName
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * Returns the middleName
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * Sets the middleName
     *
     * @param string $middleName
     * @return void
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
    }

    /**
     * Returns the lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Sets the lastName
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * Returns the email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Returns the registeraddresshash
     *
     * @return string
     */
    public function getRegisteraddresshash()
    {
        return $this->registeraddresshash;
    }

    /**
     * Sets the registeraddresshash
     *
     * @param string $registeraddresshash
     * @return void
     */
    public function setRegisteraddresshash($registeraddresshash)
    {
        $this->registeraddresshash = $registeraddresshash;
    }

    /**
     * Setter for hidden
     *
     * @param boolean $hidden
     * @return void
     */
    public function setHidden($hidden)
    {
        $this->hidden = ($hidden ? true : false);
    }

    /**
     * Getter for hidden
     *
     * @return boolean
     */
    public function getHidden()
    {
        return ($this->hidden ? true : false);
    }

    /**
     * Setter for module_sys_dmail_html
     *
     * @param boolean $moduleSysDmailHtml
     * @return void
     */
    public function setModuleSysDmailHtml($moduleSysDmailHtml)
    {
        $this->moduleSysDmailHtml = ($moduleSysDmailHtml ? true : false);
    }

    /**
     * Getter for module_sys_dmail_html
     *
     * @return boolean
     */
    public function getModuleSysDmailHtml()
    {
        return ($this->moduleSysDmailHtml ? true : false);
    }


    /**
     * Returns the eigeneAnrede
     *
     * @return string
     */
    public function getEigeneAnrede()
    {
        return $this->eigeneAnrede;
    }

    /**
     * Sets the eigeneAnrede
     *
     * @param string $eigeneAnrede
     * @return void
     */
    public function setEigeneAnrede($eigeneAnrede)
    {
        $this->eigeneAnrede = $eigeneAnrede;

        $this->setTxDirectmailsubscriptionLocalgender($eigeneAnrede);
    }

    /**
     * Sets the eigeneAnrede
     *
     * @param string $txDirectmailsubscriptionLocalgender
     * @return void
     */
    public function setTxDirectmailsubscriptionLocalgender($txDirectmailsubscriptionLocalgender)
    {
        $this->txDirectmailsubscriptionLocalgender = $txDirectmailsubscriptionLocalgender;
    }

    /**
     * @return string
     */
    public function getConsent()
    {
        return $this->consent;
    }

    /**
     * @param string $consent
     */
    public function setConsent($consent)
    {
        $this->consent = $consent;
    }


}
