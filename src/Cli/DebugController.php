<?php
namespace Behapi\Cli;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Output\OutputManager;

use Behapi\Tools\Debug;

final class DebugController implements Controller
{
    /** @var Debug */
    private $debug;

    /** @var OutputManager */
    private $manager;

    /** @var string Formatter's name to use on debug occasions */
    private $formatter;

    public function __construct(OutputManager $manager, Debug $debug, string $formatter = 'pretty')
    {
        $this->debug = $debug;
        $this->manager = $manager;
        $this->formatter = $formatter;
    }

    /** {@inheritDoc} */
    public function configure(Command $command)
    {
        $command
            ->addOption('behapi-debug', null, InputOption::VALUE_NONE, 'Activates the debug mode for behapi');
    }

    /** {@inheritDoc} */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->debug->setStatus($input->getOption('behapi-debug'));

        if (true === $this->debug->getStatus()) {
            // disable all formatters, enable only the pretty one
            $this->manager->disableAllFormatters();
            $this->manager->enableFormatter($this->formatter);
        }
    }
}
