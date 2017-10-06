<?php declare(strict_types=1);

namespace Trinet\LoggerModule\Factory;

use Psr\Container\ContainerInterface;
use Zend\Log\Logger as ZendLogger;
use Zend\Log\Writer\Stream;

class LoggerFactory
{
    public function __invoke(ContainerInterface $container): ZendLogger
    {
        $path = $this->getConfig($container)['trinet']['logger']['path'];
        $dataFormat = $this->getConfig($container)['trinet']['logger']['date-format'];
        $logger = new ZendLogger;
        $writer = new Stream($path . date($dataFormat) . '-error.log');
        $logger->addWriter($writer);

        return $logger;
    }

    private function getConfig(ContainerInterface $container): iterable
    {
        return $container->get('Config');
    }
}
