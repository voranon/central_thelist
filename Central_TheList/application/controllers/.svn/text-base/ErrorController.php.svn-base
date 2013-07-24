<?php

class ErrorController extends Zend_Controller_Action
{
	private $database;
	private $logs;
	private $user_session;
	private $acl;
	private $email;
	
	public function init()
	{
		/* Initialize action controller here */
	

	
		$this->logs= Zend_Registry::get('logs');
		 
		$this->user_session = new Zend_Session_Namespace('userinfo');
		
		$this->acl= new Thelist_Utility_acl($this->user_session->role_id);
		
		$this->email = new Thelist_Utility_email();
		 
		$this->_helper->layout->setLayout('error_layout');
	}

    public function errorAction()
    {

        $errors = $this->_getParam('error_handler');
        
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';
            $x='y';
            return;
        }
        
        	$this->view->user=$this->user_session->firstname.' '.$this->user_session->lastname;
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Page not found';
               
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Application error';
                break;
        }
        
        // Log exception, if logger available
        if ($log = $this->getLog()) {
            $log->log($this->view->message, $priority, $errors->exception);
            $log->log('Request Parameters', $priority, $errors->request->getParams());
        }
        $this->view->request   = $errors->request;
        // conditionally display exceptions
        //if ($this->getInvokeArg('displayExceptions') == true) {
      
          $this->view->exception = $errors->exception;
           $this->logs->get_app_logger()->log($errors->exception, Zend_Log::ERR);
          	
          	
          	//dident want to edit the zend log class, so updating this way.
            $user_info = array(
            
           'latitude' 			=> $this->user_session->latitude,
           'longitude' 			=> $this->user_session->longitude,
           'accuracy' 			=> $this->user_session->accuracy,
           'altitude' 			=> $this->user_session->altitude,
           'altitudeaccuracy' 	=> $this->user_session->altitudeaccuracy,
           'heading' 			=> $this->user_session->heading,
           'speed' 				=> $this->user_session->speed,
           'browsercode' 		=> $this->user_session->browsercode,
           'browsername'		=> $this->user_session->browsername,
           'browserversion' 	=> $this->user_session->browserversion,
           'cookiesenabled'		=> $this->user_session->cookiesenabled,
           'platform' 			=> $this->user_session->platform,
           'useragentheader' 	=> $this->user_session->useragentheader,
            
            );
            
            $sql = "SELECT MAX(a_logid) FROM app_event_logs";
            
            $newlogid = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);

           	$return = Zend_Registry::get('database')->set_single_attribute($newlogid, 'app_event_logs', 'userinfo', $this->logs->array_as_xml($user_info));

          
           	//$rows=Zend_Registry::get('database')->get_users()->fetchAll();
          //  foreach($rows as $row){
            
//             	if(($this->acl->role_allow($row['role_id'],3,4)?'allowed':'denied')=='allowed')
//             	{
//             		$this->view->recipient.= $row['firstname'].' '.$row['lastname'];
//             		$this->email->send($row['email'], $errors->exception,'application error');
            		
//             	}
            
         //   }
        //}
        
        $this->view->request   = $errors->request;
        
        
      
    }

    public function getLog()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }

}
?>