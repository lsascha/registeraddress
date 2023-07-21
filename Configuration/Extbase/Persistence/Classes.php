<?php
declare(strict_types = 1);

return [
    \AFM\Registeraddress\Domain\Model\Address::class => [
        'tableName' => 'tt_address',
        'properties' => [
            'name' => [
                'fieldName' => 'name'
            ],
            'gender' => [
                'fieldName' => 'gender'
            ],
            'title' => [
                'fieldName' => 'title'
            ],
            'firstName' => [
                'fieldName' => 'first_name'
            ],
            'middleName' => [
                'fieldName' => 'middle_name'
            ],
            'lastName' => [
                'fieldName' => 'last_name'
            ],
            'email' => [
                'fieldName' => 'email'
            ],
            'registeraddresshash' => [
                'fieldName' => 'registeraddresshash'
            ],
            'hidden' => [
                'fieldName' => 'hidden'
            ],
            'moduleSysDmailHtml' => [
                'fieldName' => 'module_sys_dmail_html'
            ],
            'eigeneAnrede' => [
                'fieldName' => 'eigene_anrede'
            ],
            'txDirectmailsubscriptionLocalgender' => [
                'fieldName' => 'tx_directmailsubscription_localgender'
            ],
        ],
    ],
];
