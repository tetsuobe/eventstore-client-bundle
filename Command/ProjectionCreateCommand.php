<?php

namespace EventStore\Bundle\ClientBundle\Command;

use EventStore\EventStore;
use EventStore\Http\ResponseCode;
use EventStore\Projections\Projection;
use EventStore\Projections\RunMode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectionCreateCommand extends ContainerAwareCommand
{
    use ProjectionCommandDictionary;

    /**
     * @var FormatterHelper
     */
    protected $formatter;

    /**
     * Configuration
     */
    protected function configure()
    {
        $help = <<<TXT
 The <info>%command.name%</info> command creates projection stream in EventStore. Usage:
 <info>php %command.full_name% projection-name projection-body|--file=file-name</info>
TXT;

        $this
            ->setName('eventstore:projection:create')
            ->setDescription('Creates projection stream in EventStore')
            ->setHelp($help)
            ->addArgument('name', InputArgument::REQUIRED, 'Projection name')
            ->addArgument('body', InputArgument::OPTIONAL, 'Projection body')
            ->addOption(
                'file',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Projection body from file.'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_OPTIONAL,
                'Removes previously created with the same name.',
                false
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getCommandParams($input);

        /** @var EventStore $es */
        $es = $this->getContainer()->get('event_store_client.event_store');

        $projection = new Projection(RunMode::CONTINUOUS, $this->name);
        $projection->setBody($this->body);

        $this->formatter = $this->getHelper('formatter');

        try {
            $es->writeProjection($projection, $this->force);
        } catch (\Exception $exception) {
            $output->writeln($this->errorMessage($exception->getMessage()));

            return $exception->getCode();
        }

        if (!in_array($es->getLastResponse()->getStatusCode(), [ResponseCode::HTTP_OK, ResponseCode::HTTP_CREATED])) {
            $output->writeln($this->errorMessage($es->getLastResponse()->getReasonPhrase()));

            return $es->getLastResponse()->getStatusCode();
        }
        $output->writeln($this->successMessage('Projection was created.'));

        return $es->getLastResponse()->getStatusCode();
    }
}
