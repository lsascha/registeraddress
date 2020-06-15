<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;

/**
 * Class ext_update
 *
 * Performs update tasks for extension registeraddress
 */
// @codingStandardsIgnoreStart
class ext_update
{

    /**
     * @var \TYPO3\CMS\Core\Database\Query\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected $databaseConnection;

    /**
     * Constructor
     *
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $this->queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tt_address');
        $this->queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
    }

    /**
     * Called by the extension manager to determine if the update menu entry
     * should by showed.
     *
     * @return bool
     */
    public function access()
    {
        $count = $this->queryBuilder->count('uid')
            ->from('tt_address')
            ->where(
                $this->queryBuilder->expr()->eq(
                    'registeraddresshash',
                    $this->queryBuilder->createNamedParameter('')
                )
            )
            ->execute()
            ->fetchColumn(0);
        return ($count > 0);
    }

    /**
     * Main update function called by the extension manager.
     *
     * @return string
     * @throws \Exception
     */
    public function main()
    {
        $content = '';
        $addresslist = $this->queryBuilder->select('uid', 'registeraddresshash', 'email')
            ->from('tt_address')
            ->where(
                $this->queryBuilder->expr()->eq(
                    'registeraddresshash',
                    $this->queryBuilder->createNamedParameter('')
                )
            )
            ->groupBy('uid')
            ->execute()->fetchAll();

        foreach ($addresslist as $fixAddress) {
            $content .= 'Updating tt_address uid:' . $fixAddress['uid'] . PHP_EOL;

            $rnd = microtime(true) . random_int(10000, 90000);
            $regHash = sha1($fixAddress['email'] . $rnd);

            $this->queryBuilder->update('tt_address')
                ->set('registeraddresshash', $regHash)
                ->where(
                    $this->queryBuilder->expr()->eq(
                        'uid',
                        $this->queryBuilder->createNamedParameter($fixAddress['uid'])
                    )
                )
                ->execute();
        }

        $content .= 'tt_address entries updates finished.' . PHP_EOL;
        return nl2br($content);
    }
}
