<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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
        $typo3Version = VersionNumberUtility::getNumericTypo3Version();
        if (VersionNumberUtility::convertVersionNumberToInteger($typo3Version) >= 8000000) {
            // If TYPO3 version is version 8 or higher
            $this->queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('tt_address');
            $this->queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        } else {
            // For TYPO3 Version 7 or lower
            $this->databaseConnection = $GLOBALS['TYPO3_DB'];
        }
    }

    /**
     * Called by the extension manager to determine if the update menu entry
     * should by showed.
     *
     * @return bool
     */
    public function access() {

        $typo3Version = VersionNumberUtility::getNumericTypo3Version();
        if (VersionNumberUtility::convertVersionNumberToInteger($typo3Version) >= 8000000) {
            // If TYPO3 version is version 8 or higher
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
        } else {
            // For TYPO3 Version 7 or lower
            $count = $this->databaseConnection->exec_SELECTcountRows(
                'uid',
                'tt_address',
                'registeraddresshash=""'
            );
        }

        return ($count > 0);
    }

    /**
     * Main update function called by the extension manager.
     *
     * @return string
     * @throws \Exception
     */
    public function main() {

        $content = '';

        $typo3Version = VersionNumberUtility::getNumericTypo3Version();
        if (VersionNumberUtility::convertVersionNumberToInteger($typo3Version) >= 8000000) {
            // If TYPO3 version is version 8 or higher
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

        } else {
            // For TYPO3 Version 7 or lower
            $addresslist = $this->databaseConnection->exec_SELECTquery(
                'uid, registeraddresshash, email',
                'tt_address',
                'registeraddresshash=""'
            );

            foreach ($addresslist as $fixAddress) {
                $content .= 'Updating tt_address uid:' . $fixAddress['uid'] . PHP_EOL;

                $rnd = microtime(true) . random_int(10000, 90000);
                $regHash = sha1($fixAddress['email'] . $rnd);

                $this->databaseConnection->exec_UPDATEquery(
                    'tt_address',
                    'uid = "'.$fixAddress['uid'].'"',
                    ['registeraddresshash' => $regHash]
                );
            }
        }

        $content .= 'tt_address entries updates finished.' . PHP_EOL;

        return nl2br($content);

    }
}
