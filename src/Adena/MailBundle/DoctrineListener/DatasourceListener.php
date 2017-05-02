<?php
namespace Adena\MailBundle\DoctrineListener;

use Adena\MailBundle\Entity\Datasource;
use Adena\CoreBundle\Tools\EncryptTool;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;

class DatasourceListener
{

    /**
     * @var EncryptTool
     */
    private $encryptTool;

    public function __construct( EncryptTool $encryptTool )
    {
        $this->encryptTool = $encryptTool;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Datasource ) {
            return;
        }

        $entity->setPassword($this->encryptTool->encrypt($entity->getPassword()));
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Datasource ) {
            return;
        }

        $entity->setPassword($this->encryptTool->encrypt($entity->getPassword()));
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if (!$entity instanceof Datasource ) {
            return;
        }

        $entity->setPassword($this->encryptTool->decrypt($entity->getPassword()));
    }
}
