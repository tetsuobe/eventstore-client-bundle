<?php

namespace EventStore\Bundle\ClientBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class ProjectionLinkToCreateCommand extends ProjectionCreateCommand
{
    /**
     * Configuration
     */
    protected function configure()
    {
        $help = <<<TXT
 The <info>%command.name%</info> command creates category stream in EventStore. Usage:
 <info>php %command.full_name% name stream category aggregate pattern</info>
TXT;

        $this
            ->setName('eventstore:projection:linkto:create')
            ->setDescription('Creates category stream in EventStore')
            ->setHelp($help)
            ->addArgument('name', InputArgument::REQUIRED, 'Projection name')
            ->addArgument('stream', InputArgument::REQUIRED, 'Stream name')
            ->addArgument('category', InputArgument::REQUIRED, 'Category name')
            ->addArgument('aggregate', InputArgument::OPTIONAL, 'Aggregate name', 'aggregateId')
            ->addArgument('pattern', InputArgument::OPTIONAL, 'Aggregate pattern', '$any')
            ->addOption('force', null, InputOption::VALUE_OPTIONAL, 'Removes previously created with the same name.');
    }

    /**
     * @param InputInterface $input
     * @throws \Exception
     */
    protected function getCommandParams(InputInterface $input)
    {
        $this->name = $input->getArgument('name');

        $stream = $input->getArgument('stream');
        $category = $input->getArgument('category');
        $aggregate = $input->getArgument('aggregate');
        $pattern = $input->getArgument('pattern');

        $this->body = <<<SCRIPT
fromStream('$stream')
.when({
  $pattern:function(s, e) {
    linkTo('$category-' + e.data.$aggregate, e)
  }
});
SCRIPT;
    }
}
