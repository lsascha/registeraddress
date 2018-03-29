<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\ResourceFactory;


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
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected $resourceFactory;

    /**
     * Constructor
     *
     * @throws \InvalidArgumentException
     */
    public function __construct()
    {
        $this->databaseConnection = $GLOBALS['TYPO3_DB'];
        $this->resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ResourceFactory::class);

        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_address');
    }

    /**
     * Called by the extension manager to determine if the update menu entry
     * should by showed.
     *
     * @return bool
     */
    public function access() {
        return true;
    }

    /**
     * Main update function called by the extension manager.
     *
     * @return string
     * @throws \Exception
     */
    public function main() {

        $content = '';


        $addresslist = $this->queryBuilder->select('uid', 'registeraddresshash', 'email')
                                          ->from('tt_address')
                                          ->where($this->queryBuilder->expr()->eq('registeraddresshash', $this->queryBuilder->createNamedParameter('')))
                                          ->groupBy('uid')
                                          ->execute()->fetchAll();

        foreach ($addresslist as $fixAddress) {
            $content .= 'Updating tt_address uid:' . $fixAddress['uid'];

            $rnd = microtime(true) . random_int(10000,90000);
            $regHash = sha1( $fixAddress['email'].$rnd );

            //$this->queryBuilder->update('tt_address', ['registeraddresshash' => $regHash], ['uid' => $fixAddress['uid']])
            $this->queryBuilder->update('tt_address')
                               ->set('registeraddresshash', $regHash)
                               ->where(['uid' => $fixAddress['uid']])
                               ->execute();
        }

        $content .= 'tt_address entries updated.' . PHP_EOL;

        return nl2br($content);


    }
}
