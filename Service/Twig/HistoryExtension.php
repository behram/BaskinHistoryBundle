<?php

namespace Baskin\HistoryBundle\Service\Twig;

use Baskin\HistoryBundle\Service\Stringifier;
use Doctrine\ORM\EntityManager;
use Gedmo\Loggable\Entity\LogEntry;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class HistoryExtension extends \Twig_Extension
{
    /** @var EntityManager */
    private $em;

    /** @var \Twig_Environment */
    private $twig;

    private $template;

    public function __construct(RegistryInterface $registry, \Twig_Environment $twig, $template)
    {
        $this->em = $registry->getManager();
        $this->twig = $twig;
        $this->template = $template;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getLogs', array($this, 'getLogs'), array('is_safe' => array('html'))),
        );
    }

    public function getLogs($entity)
    {
        if (!is_object($entity)) {
            return '';
        }

        return $this->twig->render($this->template, array('logEntities' => $this->logsFromEntity($entity)));
    }

    /**
     * @param $entity
     * @return array
     */
    private function logsFromEntity($entity)
    {
        $stringifier = new Stringifier();
        /** @var LogEntryRepository $repo */
        $repo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        /** @var LogEntry[] $logs */
        $logs = array_reverse($repo->getLogEntries($entity));
        $logsArray = array();
        $logLastData = array();
        if (is_array($logs)) {
            foreach ($logs as $log) {
                if (!$log instanceof LogEntry || !is_array($log->getData())) {
                    continue;
                }
                $logRow = new \stdClass();
                $logRow->id = $log->getId();
                $logRow->loggedAt = $log->getLoggedAt();
                $logRow->username = $log->getUsername();
                $logRow->action = $log->getAction();
                $logRow->data = array();
                foreach ($log->getData() as $name => $value) {
                    $dataRow = array('name' => $name, 'old' => null, 'new' => $stringifier->getString($value));
                    if (isset($logLastData[$name])) {
                        $dataRow['old'] = $stringifier->getString($logLastData[$name]);
                    }
                    $logLastData[$name] = $value;
                    $logRow->data[] = (object)$dataRow;
                }
                $logsArray[] = $logRow;
            }
        } else {
            $logsArray = array();
        }

        return array_reverse($logsArray);
    }

    public function getName()
    {
        return 'history_extension';
    }
}
