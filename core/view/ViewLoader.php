<?php
    namespace core\view;
    class ViewLoader{
        public function __construct($path){
            $this->path = $path;
        }
        
        public function load($viewName){
            $custom="";
            $content=file_get_contents(BASEPATH.'/app/segments/style/styles.seg');
            if(file_exists(BASEPATH.'/app/segments/style/'.$viewName.'.css')){
                $custom=file_get_contents(BASEPATH.'/app/segments/style/'.$viewName.'.css');
            }
            
            $path = $this->path.$viewName.'.php';
        
            if( file_exists($path) ){
                $body=file_get_contents($path);
                echo $content;
                echo "<style>".$custom."</style>";
                return $body;
            }
            throw new Exception("View does not exist: ".$viewName);
        }
    }
?>