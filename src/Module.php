<?php declare(strict_types=1);

namespace Trinet\LoggerModule;

use Throwable;
use Trinet\LoggerModule\Factory\LoggerFactory;
use Zend\EventManager\EventInterface;
use Zend\Log\Logger;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorInterface;

class Module implements ServiceProviderInterface, ConfigProviderInterface, BootstrapListenerInterface
{
    const LOGGER = __NAMESPACE__ . '\Logger';

    public function onBootstrap(EventInterface $mvcEvent)
    {
        if (!$mvcEvent instanceof MvcEvent) {
            return;
        }

        $sharedManager = $mvcEvent->getApplication()->getEventManager()->getSharedManager();
        if (!$sharedManager) {
            return;
        }

        $sharedManager->attach(Application::class, MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'exceptionLogger']);
        $sharedManager->attach(Application::class, MvcEvent::EVENT_RENDER_ERROR, [$this, 'exceptionLogger']);
    }

    public function exceptionLogger(MvcEvent $mvcEvent): void
    {
        $exception = $this->fetchMvcException($mvcEvent);
        if (!$exception) {
            return;
        }

        $serviceManager = $mvcEvent->getApplication()->getServiceManager();
        $logger = $this->getLogger($serviceManager);

        $logStr = "\n";

        do {
            $logStr .=
                sprintf(
                    "%s:%d %s (%d) [%s]\n",
                    $exception->getFile(),
                    $exception->getLine(),
                    $exception->getMessage(),
                    $exception->getCode(),
                    get_class($exception)
                )
                . $exception->getTraceAsString()
                . "\n\n";
        } while ($exception = $exception->getPrevious());

        $logger->crit($logStr);
    }

    private function getLogger(ServiceLocatorInterface $sm): Logger
    {
        return $sm->get(self::LOGGER);
    }

    private function fetchMvcException(MvcEvent $mvcEvent): ?Throwable
    {
        return $mvcEvent->getParam('exception');
    }

    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                self::LOGGER => LoggerFactory::class,
            ],
        ];
    }

    public function getConfig(): iterable
    {
        return [
            'trinet' => [
                'logger' => [
                    'path' => './data/log/',
                    'date-format' => 'Y-m-d',
                ],
            ],
        ];
    }
}
