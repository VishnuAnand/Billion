<?php
    namespace models;

    class IndexModel extends BaseModel{

        function getName(){
            return "Vishnu Anand";
        }

        function addData($data){
            //$this->insert("test1","value",$data);
        }
        
        public function getVersion(){
            return "V1.0.2-rc1";
        }

        public function getFramework(){
            return "Million Framework";
        }



    }
?>