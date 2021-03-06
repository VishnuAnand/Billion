<?php
    namespace controllers;

    class IndexController extends BaseController{

        public function index(){

            $data=$this->load->model('IndexModel');
            $name=$data->getName();
            $version=$data->getVersion();
            $framework=$data->getFramework();
            $data1 = [
                'name'   => $name,
                'version' => $version,
                'framework' => $framework,
                'sample' => [
                        ['title' => 'Title 1', 'body' => 'Body 1'],
                        ['title' => 'Title 2', 'body' => 'Body 2'],
                        ['title' => 'Title 3', 'body' => 'Body 3'],
                        ['title' => 'Title 4', 'body' => 'Body 4'],
                        ['title' => 'Title 5', 'body' => 'Body 5']
                ]
            ];
            
            //$data->connection("test");    //Serve Database name to open connection
            //$data->addData("Hello");      //Call custom model function
            $this->load->view('hello', $data1);
        }

        public function login(){
            $this->load->view('login');
        }

        public function get(){
            $name=$_POST['name'];
            $data=['name'=>$name];
            $this->load->view('form',$data);
        }
    }
?>