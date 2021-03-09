<?php
    class Users extends Controller{
        public function __construct()
        {
            $this->userModel = $this->model('User');
        }

        public function register(){
            //check for post
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                //process the form

                //Sanitize POST data
                $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
                $data = [
                    'name' => trim($_POST['name']),
                    'email' => trim($_POST['email']),
                    'password' => trim($_POST['password']),
                    'confirm_password' => trim($_POST['confirm_password']),
                    'name_error' =>  '',
                    'email_error' =>  '',
                    'password_error' =>  '',
                    'confirm_password_error' =>  ''
                ];

                //validate email
                if(empty($data['email']))
                {
                    $data['email_error'] = 'Please enter email';
                }else{
                    //check email
                    if($this->userModel->findUserByEmail($data['email'])){
                        $data['email_error'] = 'Email is already taken';
                    }
                }

                if(empty($data['name']))
                {
                    $data['name_error'] = 'Please enter the name';
                }

                //validate passsword
                if(empty($data['password'])){
                    $data['password_error'] = 'Please enter the password';
                }elseif(strlen(($data['password']))<6){
                    $data['password_error'] = 'Password must be at least 6 characters';
                }

                if(empty($data['confirm_password'])){
                    $data['confirm_password_error'] = 'Please confirm the password';
                }else{
                    if($data['password'] != $data['confirm_password']){
                    $data['confirm_password_error'] = 'Password do not match';
                }}

                //make sure errors are empty
                if(empty($data['email_error']) && empty($data['password_error']) && empty($data['confirm_password_error']
                 && empty($data['name_error']))){
                     //validated 
                     
                     //Hash Password
                     $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                     //register user
                     if($this->userModel->register($data)){
                        flash('register_success', ' You are registered and can log in');
                        redirect('users/login');
                     }else{
                         die("Something went wrong");
                     }
                 }else{
                     //load view with errors
                     $this->view('users/register', $data);
                 }

            }else{
                //init data
                $data = [
                    'name' => '',
                    'email' => '',
                    'password' => '',
                    'confirm_password' => '',
                    'name_error' => '',
                    'email_error' => '',
                    'password_error' => '',
                    'confirm_password_error' => ''
                ];
                //load view
                $this->view('/users/register', $data);

            }
        }

        public function login(){
            //check for post
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                //process the form
                $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
                $data = [
                    'email' => trim($_POST['email']),
                    'password' => trim($_POST['password']),
                    'email_error' =>  '',
                    'password_error' =>  ''
                ];

                if(empty($data['email']))
                {
                    $data['email_error'] = 'Please enter email';
                }

                //validate passsword
                if(empty($data['password'])){
                    $data['password_error'] = 'Please enter the password';
                }

                //check for user/email exist
                if($this->userModel->findUserByEmail($data['email'])){
                    //user found 
                }else{
                    $data['email_error'] = 'No user found';
                }

                if(empty($data['email_error']) && empty($data['password_error'])){
                     //validated 
                     $loggedUser = $this->userModel->login($data['email'], $data['password']);
                     if($loggedUser){
                         //create session
                        $this->createUserSession($loggedUser);
                     }else{
                         $data['password_error'] = 'Password incorrect';
                         $this->view('users/login', $data);
                     }
                     //check and set logged user
                 }else{
                     //load view with errors
                     $this->view('users/login', $data);
                 }
            }else{
                //init data
                $data = [
                    'email' => '',
                    'password' => '',
                    'email_error' => '',
                    'password_error' => ''
                ];
                //load view
                $this->view('/users/login', $data);

            }
        }

        public function createUserSession($user){
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_email'] = $user->email;
            $_SESSION['user_name'] = $user->name;
            redirect('posts');

        }

        public function logout(){
            unset($_SESSION['user_id']);
            unset($_SESSION['user_email']);
            unset($_SESSION['user_name']);
            session_destroy();
            redirect('users/login');
        }

        
    }