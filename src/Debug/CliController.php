<?php declare(strict_types=1);
namespace Behapi\Debug;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

use Behat\Testwork\Cli\Controller;

final class CliController implements Controller
{
    /** @var Configuration */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
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
    }
}
