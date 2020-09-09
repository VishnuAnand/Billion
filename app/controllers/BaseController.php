<?php
    namespace controllers;

    class BaseController{

        public function __construct(){
            $this->load = new \core\view\View(
                new \core\view\ViewLoader(BASEPATH.'/app/views/'),
                new \core\view\Templating()
            );
        }
    }
?>