<?php
declare(strict_types=1);

namespace AFM\Registeraddress\Command;

use AFM\Registeraddress\Service\DeleteHiddenRegistrationsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeleteHiddenRegistrationsCommand extends Command
{

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Delete all hidden registrations older than min. 24h (just the default value).')
        ->setHelp('You can change table and time if needed. First argument is for the table, the second are the max age of entries in seconds from now. Add -d for dry run or -f to remove entries completely from database. With the option -l you can define a log table with relation field to the other table. If set entries in log table will be deleted too.')
        ->addArgument(
            'table',
            InputArgument::OPTIONAL,
            'Execute command on this table. If not set the default is tt_address.',
            'tt_address')
        ->addArgument(
            'maxAge',
            InputArgument::OPTIONAL,
        'Set max age in seconds. If not set the default is set to 86400 = 24h.',
        86400)
        ->addOption(
            'force-delete',
            'f',
            InputOption::VALUE_NONE,
        'Force deleting entries from database. Otherwise, records will be marked as deleted.')
        ->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
        'Dry run before deleting entries. Increase verbosity (-v) to see additional information about records to be deleted.'
        );
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

        $table = $input->getArgument('table');
        $maxAge = (int)$input->getArgument('maxAge');
        $forceDelete = $input->getOption('force-delete');

        $deleteHiddenRegistrations = GeneralUtility::makeInstance(DeleteHiddenRegistrationsService::class);

        if($input->getOption('dry-run')) {
            $query = $deleteHiddenRegistrations->selectEntries($table, $maxAge);
            $result = $query->fetchAll();
            $count = $query->rowCount();
            foreach ($result as $row) {
                $io->writeln('uid:' . $row['uid'] . '; pid:' . $row['pid'] . '; E-Mail:' . $row['email'], OutputInterface::VERBOSITY_VERBOSE);
            }
            $io->writeln('Identified ' . $count . ' entries in table "'. $table .'" that can be deleted. Remove --dry-run to actually delete those entries.');
            return 0;
        }

        if($logTableAndField && GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($logTableAndField[0])) {
            $countDeletedLogEntries = $deleteHiddenRegistrations->deleteLogEntries($forceDelete, $table, $logTableAndField, $maxAge);

            if($forceDelete) {
                $io->writeln($countDeletedLogEntries . ' entries removed from log table.');
            } else {
                $io->writeln($countDeletedLogEntries . ' log entries updated and marked as deleted.');
            }
        }
        $countDeletedEntries = $deleteHiddenRegistrations->deleteEntries(
            $forceDelete,
            $table,
            $maxAge);

        if($forceDelete) {
            $io->writeln($countDeletedEntries . ' entries removed from database.');
        } else {
            $io->writeln($countDeletedEntries . ' entries updated and marked as deleted.');
        }

        return 0;
    }
}
