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

    protected DeleteHiddenRegistrationsService $registrationsService;

    public function __construct(string $name = null, DeleteHiddenRegistrationsService $registrationsService = null)
    {
        parent::__construct($name);
        $this->registrationsService = $registrationsService;
    }

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
        $dryRun = $input->getOption('dry-run');

        $records = $this->registrationsService->selectEntries($table, $maxAge);
        if (!$dryRun && $records) {
            $resultCount = $this->registrationsService->delete($records, $table, $forceDelete);
            if($forceDelete) {
                $io->writeln(sprintf("%d entries removed from database.", $resultCount));
            } else {
                $io->writeln(sprintf("%d entries updated and marked as deleted.", $resultCount));
            }
        } else {
            if(!$records) {
                $io->writeln("No entries found to delete.");
            } else {
                foreach ($records as $row) {
                    $io->writeln(sprintf("uid:%d; pid:%d; E-Mail:%s", $row['uid'], $row['pid'], $row['email']), OutputInterface::VERBOSITY_VERBOSE);
                }
                $io->writeln(sprintf("Identified %d entries in table \"%s\" that can be deleted. Remove --dry-run to actually delete those entries.",
                    count($records), $table));
            }
        }
        return 0;
    }
}
