<?php

namespace Krak\DoctrineOAuth2\Console;

use Symfony\Component\Console\{
    Command\Command,
    Input\InputInterface,
    Input\InputArgument,
    Input\InputOption,
    Output\OutputInterface
};

class GenerateKeysCommand extends Command
{
    private $defaultPath;

    public function __construct(string $defaultPath) {
        parent::__construct();
        $this->defaultPath = $defaultPath;
    }

    public function configure() {
        $this->setName('oauth2:generate-keys')
            ->setDescription('Generate a set of public/private keys')
            ->addOption('output-path', 'o', InputOption::VALUE_REQUIRED, 'The path to export the keys into')
            ->addOption('key-size', 'k', InputOption::VALUE_REQUIRED, 'The size of the key to make, defaults to 1024');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $outputPath = $input->getOption('output-path') ?: $this->defaultPath;
        $keySize = (int) $input->getOption('key-size') ?: 1024;

        $output->writeln("<info>Generating RSA key pair</info>");

        $outputPath = rtrim($outputPath, '/');
        $private_output_path = $outputPath . '/oauth-private.key';
        $public_output_path = $outputPath . '/oauth-public.key';
        $cmd = sprintf('openssl genrsa -out %s %d', $private_output_path, $keySize);
        $this->cmd($output, $cmd);

        $cmd = sprintf('openssl rsa -in %s -pubout -out %s', $private_output_path, $public_output_path);
        $this->cmd($output, $cmd);

        $cmd = sprintf('chmod 600 %s %s', $private_output_path, $public_output_path);
        $this->cmd($output, $cmd);

        $output->writeln("<info>Key pair generated at: $outputPath</info>");
    }

    private function cmd($output, $cmd) {
        $output->writeln($cmd);
        `$cmd`;
    }
}
