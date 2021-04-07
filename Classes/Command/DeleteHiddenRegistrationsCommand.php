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

    /*
     * table with data
     * @var string
     */
    protected $table = 'tt_address';

    /*
     * Time in seconds, max age for hidden entries
     * @var integer
     */
    protected $maxAge = '86400';

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setHelp('Delete all hidden registrations older than 24h.');
    }

    /**
     * Executes the command for showing sys_log entries
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        $limit = time() - $this->maxAge;
        /**@var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder**/
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($this->table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $query = $queryBuilder
            ->select('uid','pid','email')
            ->from($this->table)
            ->where('hidden = 1')
            ->andWhere('crdate < ' . $limit)
            ->setMaxResults(10);
        $result = $query
            ->execute()
            ->fetchAll();
        $count = $query->execute()->rowCount();
        foreach ($result as $row) {
            $io->writeln('uid:' . $row['uid'] . '; pid:' . $row['pid'] . '; E-Mail:' . $row['email']);
        }

        $io->writeln($count . ' entries deleted!');
        return Command::SUCCESS;
    }
}
