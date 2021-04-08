<?php
declare(strict_types=1);

namespace AFM\Registeraddress\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeleteHiddenRegistrationsCommand extends Command
{

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Delete all hidden registrations older than 24h.');
        $this->setHelp('You can change table and time if needed. First argument is used table, the second are the max age of entries in seconds from now.');
        $this->addArgument(
            'table',
            InputArgument::OPTIONAL,
            'Execute on this table. Default is tt_address.');
        $this->addArgument(
            'maxAge',
            InputArgument::OPTIONAL,
        'Set max age in seconds. Default is 86400 = 24h');
    }

    /**
     * Executes the command for showing sys_log entries.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $table = $input->getArgument('table') ?: 'tt_address';
        $maxAge = $input->getArgument('maxAge') ?: '86400';
        $limit = time() - $maxAge;
        /**@var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder**/
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $hiddenField = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'];
        $query = $queryBuilder
            ->select('uid','pid','email')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq($hiddenField, 1)
            )
            ->andWhere('crdate < ' . $limit)
            ->setMaxResults(10);
        $result = $query
            ->execute()
            ->fetchAll();
        $count = $query->execute()->rowCount();
        foreach ($result as $row) {
            $io->writeln('uid:' . $row['uid'] . '; pid:' . $row['pid'] . '; E-Mail:' . $row['email']);
        }

        $io->writeln($count . ' entries in table "'. $table .'" deleted!');
        return Command::SUCCESS;
    }
}
