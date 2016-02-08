<?php

class argos_controller extends CI_Controller 
{
	
	function __construct()
	{
		parent::__construct();
		$this->load->model('argos_model');		
	}
	
	// Function to display the homepage on the front end.
	function homePage()
	{
		$this->load->view('argos_view');		
	}
	
	/* This function listens to the request method and interacts with the model. 
		It then converts the data that was retrieved to JSON format and sends it to the view. */
	function argosDeals()
	{
		$requestType = strtolower($this->input->server('REQUEST_METHOD'));
		
		if ($requestType == "get")
		{
			$result = array();
			$result = $this->argos_model->getDeals();
			echo json_encode($result);
		}
	}
}