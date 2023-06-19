<?php

declare(strict_types=1);

namespace AFM\Registeraddress\Update;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

class CreateAddressHashUpdate implements UpgradeWizardInterface
{

    protected QueryBuilder $queryBuilder;

    public function __construct()
    {
        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_address');
        $this->queryBuilder->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
    }

    public function getIdentifier(): string
    {
        return 'registeraddress_createAddressHash';
    }

    public function getTitle(): string
    {
        return 'Registeraddress Hash Update';
    }

    public function getDescription(): string
    {
        return 'Creates a hash for handling registration where necessary';
    }

    public function executeUpdate(): bool
    {
        $addresslist = $this->queryBuilder->select('uid', 'registeraddresshash', 'email')
            ->from('tt_address')
            ->where(
                $this->queryBuilder->expr()->eq(
                    'registeraddresshash',
                    $this->queryBuilder->createNamedParameter('')
                )
            )->groupBy('uid')->executeQuery()->fetchAll();

        foreach ($addresslist as $fixAddress) {
            $content .= 'Updating tt_address uid:' . $fixAddress['uid'] . PHP_EOL;

            $rnd = microtime(true) . random_int(10000, 90000);
            $regHash = sha1($fixAddress['email'] . $rnd);

            $this->queryBuilder->update('tt_address')
                ->set('registeraddresshash', $regHash)->where($this->queryBuilder->expr()->eq(
                'uid',
                $this->queryBuilder->createNamedParameter($fixAddress['uid'])
            ))->executeStatement();
        }
        return true;
    }

    public function updateNecessary(): bool
    {
        return (bool)$this->queryBuilder->count('uid')
            ->from('tt_address')->where($this->queryBuilder->expr()->eq(
            'registeraddresshash',
            $this->queryBuilder->createNamedParameter('')
        ))->executeQuery()
            ->fetchFirstColumn();
    }

    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }
}
