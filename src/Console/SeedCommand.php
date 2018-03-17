<?php

namespace Krak\DoctrineOAuth2\Console;

use Symfony\Component\Console\{
    Command\Command,
    Input\InputInterface,
    Input\InputArgument,
    Input\InputOption,
    Output\OutputInterface,
    Logger\ConsoleLogger
};
use Psr\Container\ContainerInterface;

class SeedCommand extends Command
{
    private $container;

    public function __construct(ContainerInterface $container) {
        parent::__construct();
        $this->container = $container;
    }

    public function configure() {
        $this->setName('oauth2:seed')
            ->setDescription('Seed the oauth2 tables with any defined seeds');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $logger = new ConsoleLogger($output);
        $logger->info("Starting OAuth2 Seed");
        $this->container->get('oauth2.seed')->seed($logger);
        $logger->info("Finished");
    }
}
