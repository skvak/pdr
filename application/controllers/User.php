<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User class.
 * 
 * @extends CI_Controller
 */
class User extends CI_Controller {

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct() 
	{	
		parent::__construct();
		$this->load->model('user_model');	
	}
	
	
	public function index() 
	{	
	}
	
	/**
	 * register function.
	 * 
	 * @access public
	 * @return void
	 */
	public function register() 
	{
		// load form helper and validation library
		$this->load->helper('form');
		$this->load->library('form_validation');
		$this->load->model('admin_model');
		$data['cars'] = $this->admin_model->get_cars();
		
		// set validation rules
		$this->form_validation->set_rules('login', 'Логин', 'trim|required|alpha_numeric|min_length[3]|is_unique[users.login]', array('is_unique' => 'Этот логин уже занят. Пожалуйста введите другой.'));
		$this->form_validation->set_rules('name', 'Имя', 'trim|required|alpha_numeric|min_length[2]');
		$this->form_validation->set_rules('surname', 'Фамилия', 'trim|required|alpha_numeric|min_length[2]');
		$this->form_validation->set_rules('sex', 'Пол', 'required');
		$this->form_validation->set_rules('birthsday', 'Дата рождения', 'trim|required');
		$this->form_validation->set_rules('email', 'e-mail', 'trim|required|valid_email|is_unique[users.email]', array('is_unique' => 'Данный e-mail уже используется. Пожалуйста введите другой.'));
		$this->form_validation->set_rules('tel', 'Телефон', 'trim|required|alpha_numeric|min_length[10]');
		$this->form_validation->set_rules('password', 'Пароль', 'trim|required|min_length[6]');
		$this->form_validation->set_rules('password_confirm', 'Подтверждение пароля', 'trim|required|min_length[6]|matches[password]');
		
		if ($this->form_validation->run() === false) 
		{
			// validation not ok, send validation errors to the view
			$this->load->view('header');
			$this->load->view('user/error', $data);
			$this->load->view('user/register/register', $data);
			$this->load->view('footer');	
		}

		else 
		{
			$register = $this->input->post();
			
			if ($this->user_model->create_user($register)) 
			{
				// user creation ok
				$this->load->view('header');
				$this->load->view('user/register/register_success', $data);
				$this->load->view('footer');	
			} 
			
			else 
			{
				// user creation failed, this should never happen
				$data['error'] = 'Что-то пошло не так. Please try again.';
				
				// send error to the view
				$this->load->view('header');
				$this->load->view('user/error', $data);
				$this->load->view('user/register/register', $data);
				$this->load->view('footer');
			}	
		}
	}
		
	/**
	 * login function.
	 * 
	 * @access public
	 * @return void
	 */
	public function login() 
	{
		// create the data object
		$data = new stdClass();
		
		// load form helper and validation library
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		// set validation rules
		$this->form_validation->set_rules('login', 'Login', 'required|alpha_numeric');
		$this->form_validation->set_rules('password', 'Password', 'required');
		
		if ($this->form_validation->run() == false) 
		{
			// validation not ok, send validation errors to the view
			$this->load->view('header');
			$this->load->view('user/error', $data);
			$this->load->view('user/login/login');
			$this->load->view('footer');	
		} 
		
		else 
		{
			// set variables from the form
			$login = $this->input->post('login');
			$password = $this->input->post('password');
			
			if ($this->user_model->resolve_user_login($login, $password)) 
			{
				$user_id = $this->user_model->get_user_id_from_username($login);
				$user    = $this->user_model->get_user($user_id);
				$user_data = $this->user_model->get_user_data($user_id);
				
				//set session user data
				$session_data = array(
						'id' => session_id(),
						'user_id' => (int)$user->id,
						'login' => (string)$user->login,
						'logged_in' => (bool)true,
						'user_enabled' => (int)$user_data->user_enabled,
						'user_rights' => (int)$user_data->user_rights, );

				$this->session->set_userdata($session_data);
		
				// user login ok
				$this->load->view('header');
				$this->load->view('user/login/login_success', $data);
				$this->load->view('footer');	
			} 

			else 
			{
				// login failed
				$data->error = 'Неверный логин или пароль. Повторите ввод.';
				
				// send error to the view
				$this->load->view('header');
				$this->load->view('user/error', $data);
				$this->load->view('user/login/login', $data);
				$this->load->view('footer');	
			}
		}	
	}

	/**
	 * logout function.
	 * 
	 * @access public
	 * @return void
	 */
	public function logout() 
	{
		// create the data object
		$data = new stdClass();
		
		if ($this->session->has_userdata('logged_in') != NULL && $this->session->has_userdata('logged_in') === true) 
		{
            $data->login = $this->session->userdata('login');
            $this->session->sess_destroy();

            // user logout ok
            $this->load->view('header');
            $this->load->view('user/logout/logout_success', $data);
            $this->load->view('footer');  	
		} 

		else 
		{
			// there user was not logged in, we cannot logged him out,
			// redirect him to site root
			redirect('/');
		}	
	}
}
