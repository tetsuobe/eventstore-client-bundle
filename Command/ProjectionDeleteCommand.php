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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ProjectionDeleteCommand extends ContainerAwareCommand
{

    /**
     * Configuration
     */
    protected function configure()
    {
        $help = <<<TXT
 The <info>%command.name%</info> command delete projection stream in EventStore. Usage:
 <info>php %command.full_name% projection-name</info>
TXT;

        $this
            ->setName('eventstore:projection:delete')
            ->setDescription('Delete projection stream in EventStore')
            ->setHelp($help)
            ->addArgument('name', InputArgument::REQUIRED, 'Projection name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Continue with this action?', false, '/^yes/i');

        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        /** @var EventStore $es */
        $es = $this->getContainer()->get('event_store_client.event_store');

        $es->deleteProjection($input->getArgument('name'));

        if ($es->getLastResponse()->getStatusCode() != ResponseCode::HTTP_OK) {
            throw new \Exception($es->getLastResponse()->getReasonPhrase(), $es->getLastResponse()->getStatusCode());
        }

        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelper('formatter');
        $formattedBlock = $formatter->formatBlock('Success! Projection was deleted.', 'info');
        $output->writeln($formattedBlock);
    }
}
