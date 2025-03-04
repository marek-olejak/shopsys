<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'shopsys:domains-urls:configure',
    description: 'Copies domain URL configuration from .dist template if it\'s not set yet',
)]
class ConfigureDomainsUrlsCommand extends Command
{
    /**
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param string $configFilepath
     */
    public function __construct(
        private readonly Filesystem $localFilesystem,
        private readonly string $configFilepath,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->localFilesystem->exists($this->configFilepath)) {
            $output->writeln('<fg=green>URLs for domains were already configured.</fg=green>');
        } else {
            $output->writeln('URLs for domains were not configured yet.');
            $this->localFilesystem->copy($this->configFilepath . '.dist', $this->configFilepath);
            $output->writeln(
                sprintf('<fg=green>Copied the default configuration into "%s".</fg=green>', $this->configFilepath),
            );
        }

        return Command::SUCCESS;
    }
}
