<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use EventStore\Bundle\ClientBundle\Command\ProjectionCreateCommand;
use EventStore\Bundle\ClientBundle\Command\ProjectionDeleteCommand;
use EventStore\Bundle\ClientBundle\Command\ProjectionUpdateCommand;
use EventStore\EventStore;
use EventStore\Exception\ProjectionNotFoundException;
use EventStore\Projections\Projection;
use EventStore\Projections\RunMode;
use EventStore\Projections\Statistics;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Defines application features from the specific context.
 */
class CliContext implements Context, KernelAwareContext, SnippetAcceptingContext
{

    use KernelDictionary;

    /**
     * @var EventStore
     */
    private $es;

    /**
     * @var Application
     */
    private $console;

    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var string
     */
    private $output;

    /**
     * @var \Exception
     */
    private $commandException = null;

    /**
     * @var int
     */
    private $exitCode = 0;

    /**
     * @BeforeScenario
     */
    public function setUp()
    {
        $this->es = $this->getContainer()->get('event_store_client.event_store');

        $this->console = new Application($this->kernel);
        $this->console->setAutoExit(false);
        $this->console->add(new ProjectionCreateCommand());
        $this->console->add(new ProjectionUpdateCommand());
        $this->console->add(new ProjectionDeleteCommand());
    }

    /**
     * @When /^I run "([^"]*)" command with parameters:$/
     */
    public function iRunCommandWithParameters($commandName, TableNode $table)
    {
        $command = $this->console->find($commandName);

        $consoleInput = ['command' => $command->getName()];
        foreach ($table->getRowsHash() as $parameter => $value) {
            $consoleInput[$parameter] = $value;
        }

        try {
            $this->tester = new CommandTester($command);
            $this->exitCode = $this->tester->execute($consoleInput);
            $this->output = trim($this->tester->getDisplay());
        } catch (\Exception $exception) {
            $this->commandException = $exception;
            $this->exitCode = $exception->getCode();
            $this->output = $exception->getMessage();
        }
    }

    /**
     * @When /^I run "([^"]*)" command with parameters and answer "([^"]*)":$/
     */
    public function iRunCommandWithParametersAndAnswer($commandName, $answer, TableNode $table)
    {
        $command = $this->console->find($commandName);

        $consoleInput = ['command' => $command->getName()];
        foreach ($table->getRowsHash() as $parameter => $value) {
            $consoleInput[$parameter] = $value;
        }

        try {
            $this->tester = new CommandTester($command);

            $helper = $command->getHelper('question');
            $helper->setInputStream($this->getInputStream($answer . '\\n'));

            $this->exitCode = $this->tester->execute($consoleInput);
            $this->output = trim($this->tester->getDisplay());
        } catch (\Exception $exception) {
            $this->commandException = $exception;
            $this->exitCode = $exception->getCode();
            $this->output = $exception->getMessage();
        }
    }

    /**
     * @Then /^the command exit code should be (\d+)$/
     */
    public function theCommandExitCodeShouldBe($exitCode)
    {
        if ($exitCode != $this->exitCode) {
            throw new \Exception('Command return code: '.$this->exitCode.$this->getExceptionMessage());
        }
    }

    /**
     * @return string
     */
    private function getExceptionMessage()
    {
        return !empty($this->commandException) ? ' | '.$this->commandException->getMessage() : '';
    }

    /**
     * @Given /^I should see on console:$/
     */
    public function iShouldSeeOnConsole(PyStringNode $string)
    {
        if ((string) $string != $this->output) {
            throw new \Exception('Command return: '.$this->output);
        }
    }

    /**
     * @Given /^projection "([^"]*)" should be created$/
     */
    public function projectionShouldBeCreated($name)
    {
        /** @var Statistics $projection */
        $projection = $this->es->readProjection($name);
        if ($name != $projection->getName()) {
            throw new \Exception("Projection $name was not created");
        }
    }

    /**
     * @Given /^projection "([^"]*)" does not exist$/
     */
    public function projectionDoesNotExist($name)
    {
        $this->es->deleteProjection($name);
    }

    /**
     * @Given /^projection "([^"]*)" exists$/
     */
    public function projectionExists($name)
    {
        $projection = new Projection(RunMode::CONTINUOUS, $name);
        $projection->setBody(
            'fromAll().when({$init : function(s,e) {return {count : 0}},$any  : function(s,e) {return {count : s.count +1}}})'
        );
        $this->es->writeProjection($projection);
    }

    /**
     * @Given /^projection "([^"]*)" should not exist$/
     */
    public function projectionShouldNotExist($name)
    {
        try {
            $this->es->readProjection($name);
        } catch (ProjectionNotFoundException $e) {
            $this->output = $e->getMessage();
            return true;
        }
        throw new \Exception('Exception expected');
    }

    /**
     * @param $input
     * @return resource
     */
    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}
