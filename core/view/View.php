<?php
    namespace core\view;
    class View{

        public function __construct($viewLoader, $engine){
        
            $this->viewLoader = $viewLoader;
        
            $this->engine = $engine;
        
        }
        
        public function model($modelName){

            $path=BASEPATH.'\\app\\models\\'.$modelName.'.php';

            if( file_exists($path) ){
                $class = '\models\\'.$modelName; 
                return $obj=new $class;
            }else{
                echo "Model doesn't exist";
            }
        }

        public function view($viewName, $variables = []){
            $phpcode = $this->engine->parse(
        
                $this->viewLoader->load($viewName),
        
                $variables
            );
            echo $this->renderString($phpcode);
            
            $script1="";
            $script1=file_get_contents(BASEPATH.'/app/segments/script/script.seg');
            echo $script1;
            
            $script="";
            if(file_exists(BASEPATH.'/app/segments/script/'.$viewName.'.js')){
                $script=file_get_contents(BASEPATH.'/app/segments/script/'.$viewName.'.js');
            }
            echo "<script>".$script."</script>";
            
        }

                
        //--------------------------------------------------------------------

        /**
         * Builds the output based upon a string and any
         * data that has already been set.
         * Cache does not apply, because there is no "key".
         *
         * @param string  $view     The view contents
         * @param array   $options  Reserved for 3rd-party uses since
         *                          it might be needed to pass additional info
         *                          to other template engines.
         * @param boolean $saveData If true, will save data for use with any other calls,
         *                          if false, will clean the data after displaying the view,
         *                             if not specified, use the config setting.
         *
         * @return string
         */
        public function renderString(string $view): string
        {
            
            ob_start();
            $incoming = '?>' . $view;
            eval($incoming);
            $output = ob_get_contents();
            @ob_end_clean();

            return $output;
        }
    }
?>