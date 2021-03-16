<?php

require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Base.php");
require_once(WP_PLUGIN_DIR . LUCK_DIR_ENTITIES . "/Room.php");

class Socket
{
    private $socket;
    private $entity;
    
    public function __construct($data)
    {
        $this->socket = $data;
    }
    
    public function change()
    {
    	$class_name = ucfirst($this->socket['type']);
    	$this->entity = new $class_name();
	    
		if($this->socket['event'] == 'delete') {
			$this->entity->delete($this->socket['id']);
		} else {
            $entity = $this->entity->show($this->socket['id']);

            if(!empty($entity)) {
                $this->entity->delete($this->socket['id']);
                $this->entity->save($entity);
            }
        }
    }
}
