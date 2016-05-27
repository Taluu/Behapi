<?php
namespace Wisembly\Behat\Extension\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

use Behat\Testwork\Cli\Controller;

use Wisembly\Behat\Extension\Tools\Debug;

final class DebugController implements Controller
{
    /** @var Debug */
    private $debug;

    public function __construct(Debug $debug)
    {
        $this->debug = $debug;
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
    }
}

