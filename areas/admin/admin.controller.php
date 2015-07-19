<?php
	class admin_controller
	{
		public function __construct()
		{
			$this->opus =& opus::$instance;
		}

		public function index()
		{
			$view_data['page_title'] = 'Admin';
			$view_data['css'] = $this->opus->load->css(array('admin'));
			$view_data['js'] = $this->opus->load->js(array('admin'));
			$view_data['partial'] = $this->opus->load->view('list-rooms', null, TRUE);
			
			$this->opus->load->view('admin-template', $view_data);
		}
	}
?>