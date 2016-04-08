<?php

namespace EventStore\Bundle\ClientBundle\Command;

use EventStore\EventStore;
use EventStore\Http\ResponseCode;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ProjectionDeleteCommand extends ContainerAwareCommand
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
            return false;
        }

        /** @var EventStore $es */
        $es = $this->getContainer()->get('event_store_client.event_store');

        $this->formatter = $this->getHelper('formatter');

        try {
            $es->deleteProjection($input->getArgument('name'));
        } catch (\Exception $exception) {
            $output->writeln($this->errorMessage($exception->getMessage()));

            return $exception->getCode();
        }

        if ($es->getLastResponse()->getStatusCode() != ResponseCode::HTTP_OK) {
            $output->writeln($this->errorMessage($es->getLastResponse()->getReasonPhrase()));

            return $es->getLastResponse()->getStatusCode();
        }
        $output->writeln($this->successMessage('Projection was deleted.'));

        return $es->getLastResponse()->getStatusCode();
    }
}
