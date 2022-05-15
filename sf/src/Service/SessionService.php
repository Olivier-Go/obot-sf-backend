<?php

namespace App\Service;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class SessionService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    private function isEntity($class): bool
    {
        if (is_array($class)) {
            return false;
        }
        if ($class instanceof ArrayCollection) {
            return false;
        }
        if (is_object($class)) {
            $class = $this->em->getClassMetadata(get_class($class))->getName();
        }
        return ! $this->em->getMetadataFactory()->isTransient($class);
    }
    
    public function formToSession($form, $session, $sessionKey)
    {
        $data = $form->getData();
        $params = [];
        $types = [];

        if (!empty($data)) {
            foreach ($data as $key => $val) {
                if ($val instanceof \DateTime) {
                    $params[$key] = $val->format('Y-m-d h:m:s');
                    $types[$key]['type'] = 'datetime';
                }
                elseif ($this->isEntity($val)) {
                    $params[$key] = $val->getId();
                    $types[$key]['type'] = 'entity';
                    $types[$key]['class'] = $this->em->getClassMetadata(get_class($val))->getName();
                }
                elseif ($val instanceof ArrayCollection) {
                    $tabId = [];
                    $class = '';
                    if($this->isEntity($val->first())) {
                        $class = $this->em->getClassMetadata(get_class($val->first()))->getName();
                    }
                    foreach($val as $element) {
                        $tabId[] = $element->getId();
                    }
                    $params[$key] = !empty($tabId) ? implode(',', $tabId) : null;
                    $types[$key]['type'] = 'arraycollection';
                    $types[$key]['class'] = $class;
                }
                else {
                    $params[$key] = $val;
                    $types[$key]['type'] = 'text';
                }
            }
        }
        $session->set('ccxt_'.$sessionKey, $params);
        $session->set('ccxt_type_'.$sessionKey, $types);
    }

    /**
     * @throws Exception
     */
    public function sessionToForm($session, $sessionKey): array
    {
        $params = $session->get('ccxt_'.$sessionKey);
        $types = $session->get('ccxt_type_'.$sessionKey);
        $defaults = [];
        if (!empty($params)) {
            foreach ($params as $key => $val) {
                if (isset($types[$key]['type'])) {
                    switch ($types[$key]['type'] ) {
                        case 'datetime':
                            $date = new DateTime($val);
                            $defaults[$key] = $date;
                            break;

                        case 'entity':
                            $entity = $this->em->getRepository($types[$key]['class'])->find($val);
                            $defaults[$key] = $entity;
                            break;

                        case 'arraycollection':
                            $col = null;
                            if(!empty($val)) {
                                $tabId = explode(',', $val);
                                if(is_array($tabId) && count($tabId) > 0) {
                                    $col = new ArrayCollection();
                                    foreach($tabId as $id) {
                                        $col->add($this->em->getRepository($types[$key]['class'])->find($id));
                                    }
                                }
                            }
                            $defaults[$key] = $col;
                            break;

                        default:
                            $defaults[$key] = $val;
                            break;
                    }
                }

            }
        }
        return $defaults;
    }

}