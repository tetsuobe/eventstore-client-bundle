<?php

namespace EventStore\Bundle\ClientBundle\Command;

use Symfony\Component\Console\Input\InputInterface;

trait ProjectionCommandDictionary
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $body;

    /** @var boolean */
    private $force = false;

    /**
     * @param InputInterface $input
     * @throws \Exception
     */
    protected function getCommandParams(InputInterface $input)
    {
        $this->name = $input->getArgument('name');
        if (empty($this->name)) {
            $errorMessage = 'Missing projection <info>name</info>.';
            throw new \Exception($errorMessage);
        }

        $this->body = $input->getArgument('body');
        if (empty($this->body)) {
            $this->parseFile($input);
        }
        $this->force = $input->getOption('force');
    }

    /**
     * @param InputInterface $input
     * @throws \Exception
     */
    protected function parseFile(InputInterface $input)
    {
        $file = $input->getOption('file');
        if (empty($file)) {
            $errorMessage = 'Missing projection body, it should be added via <info>body</info> argument or <info>--file</info> option.';
            throw new \Exception($errorMessage);
        }
        if (!file_exists($file)) {
            $errorMessage = sprintf('Projection file <info>%s</info> does not exists', $file);
            throw new \Exception($errorMessage);
        }
        $this->body = file_get_contents($file);
    }
}
