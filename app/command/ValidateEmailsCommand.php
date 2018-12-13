<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Service;

/**
 * Class defining command for symfony console for performing email validation
 *
 * Class ValidateEmailsCommand
 * @package App\Command
 */
class ValidateEmailsCommand extends Command
{

    /**
     * @const int
     */
    CONST PROGRESS_BAR_SIZE = 100;

    /**
     * The command name
     *
     * @var string
     */
    protected static $defaultName = 'app:validate-emails';

    /**
     * @var Service\EmailValidator
     */
    protected $emailValidator;

    /**
     * ValidateEmailsCommand constructor.
     * @param Service\EmailValidator $emailValidator
     */
    public function __construct(Service\EmailValidator $emailValidator)
    {
        $this->emailValidator = $emailValidator;

        parent::__construct();
    }

    /**
     * Basic command config.
     */
    protected function configure()
    {

        $this
            ->setDescription('Validates List of Emails')
            ->setHelp("\r\nThe <info>%command.name%</info> command validates list of emails specified in file\r\nlocated in <info>--filePath</info> and creates two files with valid and invalid email list in <info>--outputDirectory</info>.\r\n")
            ->addArgument('filePath', InputArgument::REQUIRED, 'Path to the input file.')
            ->addArgument('outputDirectory', InputArgument::REQUIRED, 'The output directory for generated files.');


    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();

        $filePath = $input->getArgument('filePath');
        $outputDir = $input->getArgument('outputDirectory');

        if (!$filesystem->exists($filePath)) {
            $style = new OutputFormatterStyle('white', 'red');
            $output->getFormatter()->setStyle('errorStyle', $style);

            $errorMessage = '   Input file does not exist.   ';

            $output->writeln(str_repeat(' ', strlen($errorMessage)));
            $output->writeln('<errorStyle>' . str_repeat(' ', strlen($errorMessage)) . '</>');
            $output->writeln('<errorStyle>' . $errorMessage . '</>');
            $output->writeln('<errorStyle>' . str_repeat(' ', strlen($errorMessage)) . '</>');
            $output->writeln(str_repeat(' ', strlen($errorMessage)));
            return;
        }

        if (!$filesystem->exists($outputDir)) {
            $filesystem->mkdir($outputDir);
        }

        $now = microtime(true);

        $filePathValid = realpath($outputDir) . '/' . 'validEmails.' . $now . '.csv';
        $filePathInvalid = realpath($outputDir) . '/' . 'invalidEmails.' . $now . '.csv';

        $h = fopen($filePath, 'ab+');

        $lineCount = 0;
        while (!feof($h)) {
            $line = fgets($h, 4096);
            $lineCount += substr_count($line, PHP_EOL);
        }

        rewind($h);


        $hValidMails = fopen($filePathValid, 'ab+');
        $hInvalidMails = fopen($filePathInvalid, 'ab+');

        $progressBar = new ProgressBar($output, self::PROGRESS_BAR_SIZE);
        $progressBar->start();

        $progress = 0;
        $validCount = 0;
        $invalidCount = 0;
        while (($l = fgetcsv($h)) !== false) {
            $email = $l[0];
            if (!empty($email)) {
                if ($this->emailValidator->emailIsValid($email)) {
                    $validCount++;
                    fputcsv($hValidMails, array($email));
                } else {
                    $invalidCount++;
                    fputcsv($hInvalidMails, array($email));
                }
            }


            $progress++;
            if ($progress % ceil($lineCount / self::PROGRESS_BAR_SIZE) === 0) {
                $progressBar->advance();
            }

        }

        $content = sprintf(
            "Total Records Tested: %d\r\nValid Records: %d\r\nInvalid Records: %d\r\n",
            $lineCount,
            $validCount,
            $invalidCount
        );

        $filesystem->appendToFile(realpath($outputDir) . '/' . 'validateResult' . $now . '.txt', $content);

        $progressBar->finish();

    }


}