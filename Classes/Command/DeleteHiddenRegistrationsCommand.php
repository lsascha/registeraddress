<?php
declare(strict_types=1);

namespace AFM\Registeraddress\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            'Execute on this table. Default is tt_address.',
            'tt_address');
        $this->addArgument(
            'maxAge',
            InputArgument::OPTIONAL,
        'Set max age in seconds. Default is 86400 = 24h',
        86400);
        $this->addOption(
            'force-delete',
            'f',
            InputOption::VALUE_OPTIONAL,
        'Force deleting entries from database');
        $this->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_OPTIONAL,
        'Dry run before deleting entries.');
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

        if($input->getOption('dry-run')) {
            $query = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\AFM\Registeraddress\Service\DeleteHiddenRegistrationsService::class)->selectEntries($table, $maxAge);
            $result = $query->fetchAll();
            $count = $query->rowCount();
            foreach ($result as $row) {
                $io->writeln('uid:' . $row['uid'] . '; pid:' . $row['pid'] . '; E-Mail:' . $row['email']);
            }
            $io->writeln('Tried to delete ' . $count . ' entries in table "'. $table .'".');
            return Command::SUCCESS;
        }

        $io->writeln('No dry run');

        return Command::SUCCESS;
    }
}
