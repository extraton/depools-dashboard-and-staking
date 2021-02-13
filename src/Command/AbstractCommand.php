<?php namespace App\Command;

use Ewll\LogExtraDataBundle\LogExtraDataKeeper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;

abstract class AbstractCommand extends Command
{
    protected LogExtraDataKeeper $logExtraDataKeeper;
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        parent::__construct();
    }

    public function setLogExtraDataKeeper(LogExtraDataKeeper $logExtraDataKeeper): void
    {
        $this->logExtraDataKeeper = $logExtraDataKeeper;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logExtraDataKeeper->setData([
            'name' => $this->getName(),
            'session' => uniqid(),
        ]);

        $this->logger->info('Start command', $input->getArguments());

        $store = new FlockStore();
        $factory = new LockFactory($store);
        $lock = $factory->createLock(md5(serialize($input->getArguments())));

        if (!$lock->acquire()) {
            $this->logger->critical('Sorry, cannot lock file');

            return 1;
        }
        $result = $this->do($input, $output);
        $this->logger->info("Command has successfully finished", $input->getArguments());

        return $result;
    }

    abstract protected function do(InputInterface $input, OutputInterface $output);
}
