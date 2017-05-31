<?php

namespace Adena\MailBundle\Queue;

use Doctrine\ORM\EntityManagerInterface;

class QueueDatabaseIterator implements \Iterator
{
    private $queues = [];
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setQueues(array $queues){
        $this->queues = $queues;
    }

    /**
     * Return the current element
     *
     * @link  http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current()
    {
        return end($this->queues);
    }

    /**
     * Move forward to next element
     *
     * @link  http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next()
    {
        $element = end($this->queues);
        if(!$element)
        {
            return;
        }
        $this->em->getRepository('AdenaMailBundle:Queue')->removeById($element['id']);
        array_pop($this->queues);
        end($this->queues);
    }

    /**
     * Return the key of the current element
     *
     * @link  http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key()
    {
        end($this->queues);
        return key($this->queues);
    }

    /**
     * Checks if current position is valid
     *
     * @link  http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid()
    {
        return end($this->queues);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link  http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind()
    {
        end($this->queues);
    }
}