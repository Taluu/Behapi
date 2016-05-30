<?php
namespace Wisembly\Behat\Extension\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Output\OutputManager;

use Wisembly\Behat\Extension\Tools\Debug;

final class DebugController implements Controller
{
    /** @var Debug */
    private $debug;

    /** @var OutputManager */
    private $outputManager;

    /** @var string Formatter's name to use on debug occasions */
    private $formatter;

    public function __construct(OutputManager $manager, Debug $debug, $formatter = 'pretty')
    {
        $this->debug = $debug;
        $this->manager = $manager;
        $this->formatter = $formatter;
    }

    /** {@inheritDoc} */
    public function configure(Command $command)
    {
        $command
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Activates the debug mode');
    }

    /** {@inheritDoc} */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->debug->setStatus($input->getOption('debug'));

        if (true === $this->debug->getStatus()) {
            // disable all formatters, enable only the pretty one
            $this->manager->disableAllFormatters();
            $this->manager->enableFormatter($this->formatter);
        }
    }
}

