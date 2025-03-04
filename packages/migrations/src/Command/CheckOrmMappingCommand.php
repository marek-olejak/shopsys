<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:migrations:check-mapping',
    description: 'Check if ORM mapping is valid',
)]
class CheckOrmMappingCommand extends Command
{
    protected const RETURN_CODE_OK = 0;
    protected const RETURN_CODE_ERROR = 1;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Checking ORM mapping...');

        $schemaValidator = new SchemaValidator($this->em);
        $schemaErrors = $schemaValidator->validateMapping();

        if (count($schemaErrors) > 0) {
            foreach ($schemaErrors as $className => $classErrors) {
                $output->writeln('<error>The entity-class ' . $className . ' mapping is invalid:</error>');

                foreach ($classErrors as $classError) {
                    $output->writeln('<error>- ' . $classError . '</error>');
                }

                $output->writeln('');
            }

            return static::RETURN_CODE_ERROR;
        }

        $output->writeln('<info>ORM mapping is valid.</info>');

        return static::RETURN_CODE_OK;
    }
}
