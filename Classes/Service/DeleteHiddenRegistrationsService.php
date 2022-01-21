<?php
declare(strict_types=1);

namespace AFM\Registeraddress\Service;


use AFM\Registeraddress\Event\AfterDeleteEvent;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeleteHiddenRegistrationsService
{

    protected EventDispatcher $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /*
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function selectEntries(string $table = 'tt_address', int $maxAge = 86400) {
        $limit = time() - $maxAge;
        $hiddenField = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'];

        /**@var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder**/
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        /** @noinspection PhpParamsInspection */
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $query = $queryBuilder
            ->select('uid','pid','email')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq($hiddenField, 1),
                $queryBuilder->expr()->lt('crdate', $limit)
            )
            ->execute();
        return $query->fetchAllAssociative();
    }

    public function delete(array $records, string $table, bool $forceDelete = false): ?int
    {
        $uidList = array_map(function (array $record): int {
            return $record['uid'];
        }, $records);
        /** @var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder **/
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $condition = $queryBuilder->expr()->in(
            'uid',
            $uidList
        );
        if ($forceDelete) {
            $queryBuilder->delete($table)
                ->where($condition);
        } else {
            $deleteField = $GLOBALS['TCA'][$table]['ctrl']['delete'];
            $queryBuilder->update($table)
                ->set($deleteField, 1)
                ->where($condition);
        }
        $result = $queryBuilder->execute();
        $this->eventDispatcher->dispatch(new AfterDeleteEvent($records, $table, $forceDelete));
        return $result;
    }
}
