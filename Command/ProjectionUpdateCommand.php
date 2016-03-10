<?php

namespace EventStore\Bundle\ClientBundle\Command;

use EventStore\EventStore;
use EventStore\Http\ResponseCode;
use EventStore\Projections\Projection;
use EventStore\Projections\Statistics;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ProjectionUpdateCommand extends ContainerAwareCommand
{
    use ProjectionCommandDictionary;

    /**
     * Configuration
     */
    protected function configure()
    {
        $help = <<<TXT
 The <info>%command.name%</info> command updates projection stream in EventStore. Usage:
 <info>php %command.full_name% projection-name projection-body|--file=file-name</info>
TXT;

        $this
            ->setName('eventstore:projection:update')
            ->setDescription('Update projection stream in EventStore')
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
                'emit',
                null,
                InputOption::VALUE_OPTIONAL,
                'Enable emit'
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

        /** @var Statistics $statistic */
        $statistic = $es->readProjection($this->name);

        $projection = new Projection($statistic->getMode(), $statistic->getName());
        $projection->setBody($this->body);
        if (!is_null($emit = $input->getOption('emit'))) {
            $projection->setEmit($emit);
        }

        $es->updateProjection($projection);

        if ($es->getLastResponse()->getStatusCode() != ResponseCode::HTTP_OK) {
            throw new \Exception($es->getLastResponse()->getReasonPhrase(), $es->getLastResponse()->getStatusCode());
        }

        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelper('formatter');
        $formattedBlock = $formatter->formatBlock('Success! Projection was updated.', 'info');
        $output->writeln($formattedBlock);
    }
}
