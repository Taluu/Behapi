<?php declare(strict_types=1);
namespace Behapi\Debug;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

use Behat\Testwork\Cli\Controller;
use Behat\Testwork\Output\OutputManager;

final class CliController implements Controller
{
    /** @var Configuration */
    private $configuration;

    /** @var OutputManager */
    private $manager;

    /** @var string Formatter's name to use on debug occasions */
    private $formatter;

    public function __construct(OutputManager $manager, Configuration $configuration, string $formatter = 'pretty')
    {
        $this->manager = $manager;
        $this->formatter = $formatter;
        $this->configuration = $configuration;
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
        $this->configuration->setStatus($input->getOption('behapi-debug'));

        if (true === $this->configuration->getStatus()) {
            // disable all formatters, enable only the pretty one
            $this->manager->disableAllFormatters();
            $this->manager->enableFormatter($this->formatter);
        }
    }
}
