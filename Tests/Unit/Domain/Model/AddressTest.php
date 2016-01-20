<?php

namespace AFM\Registeraddress\Tests;
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
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Test case for class \AFM\Registeraddress\Domain\Model\Address.
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 * @package TYPO3
 * @subpackage registerttaddress
 *
 * @author Sascha Löffler <sl@afm-koeln.de>
 */
class AddressTest extends \TYPO3\CMS\Extbase\Tests\Unit\BaseTestCase {
	/**
	 * @var \AFM\Registeraddress\Domain\Model\Address
	 */
	protected $fixture;

	public function setUp() {
		$this->fixture = new \AFM\Registeraddress\Domain\Model\Address();
	}

	public function tearDown() {
		unset($this->fixture);
	}

	/**
	 * @test
	 */
	public function getNameReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setNameForStringSetsName() { 
		$this->fixture->setName('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getName()
		);
	}
	
	/**
	 * @test
	 */
	public function getGenderReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setGenderForStringSetsGender() { 
		$this->fixture->setGender('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getGender()
		);
	}
	
	/**
	 * @test
	 */
	public function getFirstNameReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setFirstNameForStringSetsFirstName() { 
		$this->fixture->setFirstName('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getFirstName()
		);
	}
	
	/**
	 * @test
	 */
	public function getMiddleNameReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setMiddleNameForStringSetsMiddleName() { 
		$this->fixture->setMiddleName('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getMiddleName()
		);
	}
	
	/**
	 * @test
	 */
	public function getLastNameReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setLastNameForStringSetsLastName() { 
		$this->fixture->setLastName('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getLastName()
		);
	}
	
	/**
	 * @test
	 */
	public function getEmailReturnsInitialValueForString() { }

	/**
	 * @test
	 */
	public function setEmailForStringSetsEmail() { 
		$this->fixture->setEmail('Conceived at T3CON10');

		$this->assertSame(
			'Conceived at T3CON10',
			$this->fixture->getEmail()
		);
	}
	
}
?>