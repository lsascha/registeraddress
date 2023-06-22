<?php

namespace AFM\Registeraddress\Event;

use AFM\Registeraddress\Domain\Model\Address;

final class DeleteBeforePersistEvent
{
    private Address $address;

    public function __construct(Address $address)
    {
        $this->address = $address;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }
    /**
     * @param Address $address
     * @return CreateBeforePersistEvent
     */
    public function setAddress(Address $address): CreateBeforePersistEvent
    {
        $this->address = $address;
        return $this;
    }
    
}