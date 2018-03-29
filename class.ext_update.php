<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Class ext_update
 *
 * Performs update tasks for extension registeraddress
 */
// @codingStandardsIgnoreStart
class ext_update
{

    /**
     * @return boolean
     */
    public function access() {
        return true;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function main() {

        $content = '';

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_address');


        $addresslist = $queryBuilder->select('uid', 'registeraddresshash', 'email')
                            ->from('tt_address')
                            ->where($queryBuilder->expr()->eq('registeraddresshash', $queryBuilder->createNamedParameter('')))
                            ->groupBy('uid')
                            ->execute()->fetchAll();

        foreach ($addresslist as $fixAddress) {
            $content .= 'Updating tt_address uid:' . $fixAddress['uid'];

            $rnd = microtime(true) . random_int(10000,90000);
            $regHash = sha1( $fixAddress['email'].$rnd );

            $queryBuilder->update('tt_address', ['registeraddresshash' => $regHash], ['uid' => $fixAddress['uid']])
        }

        $content .= 'tt_address entries updated.' . PHP_EOL;

        return nl2br($content);


    }
}
