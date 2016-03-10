<?php
namespace EventStore\Bundle\ClientBundle\Tests\Integration;

use EventStore\Bundle\ClientBundle\DependencyInjection\EventStoreClientExtension;
use EventStore\StreamFeed\LinkRelation;
use EventStore\WritableEvent;
use PHPUnit_Framework_TestCase as TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

class EventStoreTest extends TestCase
{
    public function testEventStoreCanCreateAStreamAndOpenIt()
    {
        $es = $this->getEventStore();

        $event = WritableEvent::newInstance('SomethingHappened', ['foo' => 'bar']);
        $streamName = 'StreamName';

        $es->writeToStream($streamName, $event);
        $es->openStreamFeed($streamName);
    }

    private function getEventStore()
    {
        $loader = new EventStoreClientExtension();

        $builder = new ContainerBuilder();
        $loader->load([$this->getConfig()], $builder);

        return $builder->get('event_store_client.event_store');
    }

    private function getConfig()
    {
        $yaml = <<<EOF
base_url: http://127.0.0.1:2113
user: userlogin
password: userpass
EOF;

        return (new Parser())->parse($yaml);
    }
}
