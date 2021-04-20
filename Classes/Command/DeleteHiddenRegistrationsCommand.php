<?php
declare(strict_types=1);

namespace AFM\Registeraddress\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeleteHiddenRegistrationsCommand extends Command
{

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setDescription('Delete all hidden registrations older than 24h (just the default value).')
        ->setHelp('You can change table and time if needed. First argument is for the table, the second are the max age of entries in seconds from now. Add -d for dry run or -f to remove entries completely from database.')
        ->addArgument(
            'table',
            InputArgument::OPTIONAL,
            'Execute command on this table. Default is tt_address.',
            'tt_address')
        ->addArgument(
            'maxAge',
            InputArgument::OPTIONAL,
        'Set max age in seconds. Default is 86400 = 24h.',
        86400)
        ->addOption(
            'force-delete',
            'f',
            InputOption::VALUE_NONE,
        'Force deleting entries from database.')
        ->addOption(
            'dry-run',
            'd',
            InputOption::VALUE_NONE,
        'Dry run before deleting entries. Output some information of data sets.'
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

        if($input->getOption('dry-run')) {
            $query = GeneralUtility::makeInstance(\AFM\Registeraddress\Service\DeleteHiddenRegistrationsService::class)->selectEntries($table, $maxAge);
            $result = $query->fetchAll();
            $count = $query->rowCount();
            foreach ($result as $row) {
                $io->writeln('uid:' . $row['uid'] . '; pid:' . $row['pid'] . '; E-Mail:' . $row['email']);
            }
            $io->writeln('Tried to delete ' . $count . ' entries in table "'. $table .'".');
            return Command::SUCCESS;
        }

        $countDeletedEntries = GeneralUtility::makeInstance(\AFM\Registeraddress\Service\DeleteHiddenRegistrationsService::class)->deleteEntries(
            $table,
            $maxAge,
            $forceDelete);

        if($forceDelete) {
            $io->writeln($countDeletedEntries . ' entries really deleted.');
        } else {
            $io->writeln($countDeletedEntries . ' entries updated and set to be deleted.');
        }

        return Command::SUCCESS;
    }
}
