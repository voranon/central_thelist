<?php

//exception codes 17000-17099

class thelist_model_serviceplan
{
	private $logs;
	private $user_session;
	private $_time;
	
	private $_service_plan_id;
	private $_service_plan_type;
	private $_service_plan_name;
	private $_service_plan_desc;
	private $_service_plan_install_required_time;
	private $_service_plan_permanent_install_only;
	private $_service_plan_default_mrc;
	private $_service_plan_default_nrc;
	private $_service_plan_default_mrc_term;
	private $_activate;
	private $_deactivate;
	
	private $_service_plan_eq_type_maps=null;
	private $_service_plan_group=null;
	private $_service_plan_help=null;
	//private $_service_plan_service_point_interface_feature_maps=null;
	private $_sp_if_feature_requirement=null;
	private $all_if_types=null;
	private $service_plan_if_type_maps=null;
	
	private $interface_type_as=null;
	private $interface_type_bs=null;
	
	private $_is_active=null;
	private $is_expired=null;
	private $is_editable=null;
	
	//corrected
	private $_service_plan_option_maps=null;
	
		
	private $service_plan_eq_type_groups=null;

	public function __construct($service_plan_id)
	{
		$this->_service_plan_id		= $service_plan_id;	

		$this->logs					= Zend_Registry::get('logs');
		$this->user_session			= new Zend_Session_Namespace('userinfo');
		$this->_time				= Zend_Registry::get('time');
		
		$sql=	"SELECT * FROM service_plans
				WHERE service_plan_id='".$this->_service_plan_id."'
				";
		
		$service_plan = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql);
	
		$this->_service_plan_type						=$service_plan['service_plan_type'];
		$this->_service_plan_name						=$service_plan['service_plan_name'];
		$this->_service_plan_desc						=$service_plan['service_plan_desc'];
		$this->_service_plan_permanent_install_only		=$service_plan['service_plan_permanent_install_only'];
		$this->_service_plan_install_required_time		=$service_plan['service_plan_install_required_time'];
		$this->_service_plan_default_mrc				=$service_plan['service_plan_default_mrc'];
		$this->_service_plan_default_nrc				=$service_plan['service_plan_default_nrc'];
		$this->_service_plan_default_mrc_term			=$service_plan['service_plan_default_mrc_term'];
		$this->_activate								=$service_plan['activate'];
		$this->_deactivate								=$service_plan['deactivate'];

	}
	//public function get_id(){
	//	return $this->_service_plan_id;
	//}
	public function get_service_plan_id()
	{
		return $this->_service_plan_id;
	}
	public function get_service_plan_name()
	{
		return $this->_service_plan_name;
	}
	public function get_service_plan_type()
	{
		return $this->_service_plan_type;
	}
	public function get_service_plan_desc()
	{
		return $this->_service_plan_desc;
	}
	public function get_service_plan_install_required_time()
	{
		return $this->_service_plan_install_required_time;
	}
	public function get_service_plan_permanent_install_only()
	{
		return $this->_service_plan_permanent_install_only;
	}
	public function get_service_plan_default_mrc()
	{
		return $this->_service_plan_default_mrc;
	}
	public function get_service_plan_default_nrc()
	{
		return $this->_service_plan_default_nrc;
	}
	public function get_service_plan_default_mrc_term(){
		return $this->_service_plan_default_mrc_term;
	}
	public function get_activate(){
		return $this->_activate;
	}
	public function get_deactivate(){
		return $this->_deactivate;
	}
	public function get_service_plan_group()
	{
		if ($this->_service_plan_group == null) {
			
			$sql55	=	"SELECT * FROM service_plan_group_mapping spgm
						 INNER JOIN service_plan_groups spg ON spg.service_plan_group_id=spgm.service_plan_group_id
						 WHERE spgm.service_plan_id='".$this->_service_plan_id."'
						";
			
			$this->_service_plan_group = Zend_Registry::get('database')->get_thelist_adapter()->fetchRow($sql55);
		}
		
		return $this->_service_plan_group;
	}
	public function get_service_plan_group_name()
	{
		$this->get_service_plan_group();

		return $this->_service_plan_group['service_plan_group_name'];
	}
	public function get_service_plan_eq_type_maps()
	{
		if ($this->_service_plan_eq_type_maps == null) {
			//add all the service plan eq_types
			$sql2 = 	"SELECT * FROM service_plan_eq_type_mapping
						WHERE service_plan_id='".$this->_service_plan_id."'
						";
			
			$service_plan_eq_type_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
			
			if (isset($service_plan_eq_type_maps['0'])) {
				foreach ($service_plan_eq_type_maps as $service_plan_eq_type_map) {
						
					$this->_service_plan_eq_type_maps[$service_plan_eq_type_map['service_plan_eq_type_map_id']] = new Thelist_Model_serviceplaneqtypemap($service_plan_eq_type_map['service_plan_eq_type_map_id']);
			
				}
			}
		}
		
		return $this->_service_plan_eq_type_maps;
	}
	
	public function get_service_plan_option_maps()
	{
		//complete
		if ($this->_service_plan_option_maps == null) {

			$sql = 	"SELECT * FROM service_plan_option_mapping
					 WHERE service_plan_id='".$this->_service_plan_id."'
					";
			
			$service_plan_option_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			if (isset($service_plan_option_maps['0'])) {
					
				foreach ($service_plan_option_maps as $service_plan_option_map) {
					$this->_service_plan_option_maps[$service_plan_option_map['service_plan_option_map_id']] = new Thelist_Model_serviceplanoptionmap($service_plan_option_map['service_plan_option_map_id']);	
				}
			}
		}
		
		return $this->_service_plan_option_maps;
	}
	
	public function get_service_plan_help()
	{
		if ($this->_service_plan_help == null) {
			$sp_help = Zend_Registry::get('database')->get_service_plan_help()->fetchAll("service_plan_id=".$this->_service_plan_id);
			
			//all the help in array
			if (isset($sp_help['0'])) {
					
				$this->_service_plan_help = $sp_help;
			}
		}
		
		return $this->_service_plan_help;
	}
	
// 	public function get_service_point_interface_requirements()
// 	{
// 		if ($this->_service_plan_service_point_interface_feature_maps == null) {
			
			////inteface feature requirements from the interface in the service point.
// 			$sp_sp_if_feat_maps = Zend_Registry::get('database')->get_service_plan_service_point_interface_feature_mapping()->fetchAll("service_plan_id=".$this->_service_plan_id);
			
// 			if (isset($sp_sp_if_feat_maps['0'])) {
			
// 				$this->_service_plan_service_point_interface_feature_maps = array();
			
// 				foreach ($sp_sp_if_feat_maps as $sp_sp_if_feat_map) {
						
// 					$this->_service_plan_service_point_interface_feature_maps[$sp_sp_if_feat_map['if_feature_id']] = new Thelist_Model_equipmentinterfacefeature($sp_sp_if_feat_map['if_feature_id']);
			
// 				}
// 			}
// 		}
// 		return $this->_service_plan_service_point_interface_feature_maps;
// 	}
// 	public function get_service_point_interface_requirement($if_feature_id)
// 	{
// 		return $this->_service_plan_service_point_interface_feature_maps[$if_feature_id];
// 	}
	
	////// for all  options
// 	public function get_optional_options()
// 	{
		
// 		$sql="SELECT spom.service_plan_option_map_id
// 			  FROM service_plan_option_mapping spom
// 			  LEFT OUTER JOIN service_plan_option_groups spog ON spom.service_plan_option_group_id = spog.service_plan_option_group_id
// 			  LEFT OUTER JOIN service_plan_options spo ON spom.service_plan_option_id = spo.service_plan_option_id
// 			  WHERE spog.service_plan_option_required_quantity = 0
// 			  AND spog.service_plan_option_max_quantity > 0
// 			  AND service_plan_id =".$this->_service_plan_id."
// 			  ORDER BY spog.service_plan_option_group_id";
		
// 		$options = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
// 		$service_plan_options='';
		
// 		foreach($options as $option){
// 			$service_plan_options[ $option['service_plan_option_map_id'] ] = new Thelist_Model_serviceplaneqtypemap( $option['service_plan_option_map_id'] );
// 		}
		
// 		return $service_plan_options;
// 	} 
	
// 	public function get_required_more_withchoices_options()
// 	{
		
// 		$sql="SELECT spom.service_plan_option_map_id
// 			  FROM service_plan_option_mapping spom
// 			  LEFT OUTER JOIN service_plan_option_groups spog ON spom.service_plan_option_group_id = spog.service_plan_option_group_id
// 			  LEFT OUTER JOIN service_plan_options spo ON spom.service_plan_option_id = spo.service_plan_option_id
// 			  WHERE spog.service_plan_option_required_quantity = 1
// 			  AND spog.service_plan_option_max_quantity >= 1
// 			  AND service_plan_id =".$this->_service_plan_id."
// 			  AND spom.service_plan_option_map_id NOT IN(
// 														SELECT service_plan_option_map_id
// 														FROM service_plan_option_mapping
// 														WHERE service_plan_id = ".$this->_service_plan_id."
// 														GROUP BY service_plan_option_group_id
// 														HAVING COUNT( service_plan_option_map_id ) = 1
// 					  									)
// 			  ORDER BY spog.service_plan_option_group_id";
		
// 		$options = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
// 		$service_plan_options='';
		
// 		foreach($options as $option){
// 			$service_plan_options[ $option['service_plan_option_map_id'] ] = new Thelist_Model_serviceplanoptionmap( $option['service_plan_option_map_id'] );
// 		}
		
// 		return $service_plan_options;
// 	}
	
// 	public function get_required_one_withoutchoices_option(){
		
// 		$sql="SELECT spom.service_plan_option_map_id 
// 			  FROM service_plan_option_mapping spom
// 			  LEFT OUTER JOIN service_plan_option_groups spog ON spom.service_plan_option_group_id = spog.service_plan_option_group_id
// 			  LEFT OUTER JOIN service_plan_options spo ON spom.service_plan_option_id = spo.service_plan_option_id
// 		      WHERE spog.service_plan_option_required_quantity = 1
// 			  AND spog.service_plan_option_max_quantity = 1
// 			  AND service_plan_id =".$this->_service_plan_id."
// 			  GROUP BY spog.service_plan_option_group_id
// 			  HAVING COUNT( spog.service_plan_option_group_id ) = 1";
		
// 		$options = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
// 		$service_plan_options='';
		
// 		foreach($options as $option){
// 			$service_plan_options[ $option['service_plan_option_map_id'] ] = new Thelist_Model_serviceplanoptionmap( $option['service_plan_option_map_id'] );
// 		}
		
// 		return $service_plan_options;
// 	}
	
	
	
	////// for all equipments
// 	public function get_optional_equipments(){
		
// 		$sql="SELECT spetm.service_plan_eq_type_map_id
// 			  FROM service_plan_eq_type_mapping spetm
// 			  LEFT OUTER JOIN service_plan_eq_type_groups spetg ON spetm.service_plan_eq_type_group_id = spetg.service_plan_eq_type_group_id
// 			  WHERE spetg.service_plan_eq_type_required_quantity = 0
// 			  AND   spetg.service_plan_eq_type_max_quantity > 0
// 			  AND service_plan_id =".$this->_service_plan_id."
// 			  ORDER BY spetg.service_plan_eq_type_group_id";
		
// 		$equipments = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
// 		$service_plan_equipments='';
					
// 		foreach($equipments as $quipment){
// 			$service_plan_equipments[  $quipment['service_plan_eq_type_map_id'] ]  = new Thelist_Model_serviceplaneqtypemap( $quipment['service_plan_eq_type_map_id'] );
// 		}
		
// 		return $service_plan_equipments;
// 	}
	
// 	public function get_required_more_withchoices_equipments(){
		
// 		$sql="SELECT spetm.service_plan_eq_type_map_id
// 			  FROM service_plan_eq_type_mapping spetm
// 			  LEFT OUTER JOIN service_plan_eq_type_groups spetg ON spetm.service_plan_eq_type_group_id = spetg.service_plan_eq_type_group_id
// 			  WHERE spetg.service_plan_eq_type_required_quantity = 1
// 			  AND   spetg.service_plan_eq_type_max_quantity >= 1
// 			  AND service_plan_id =".$this->_service_plan_id."
// 			  AND spetm.service_plan_eq_type_map_id NOT IN(
// 					     									SELECT service_plan_eq_type_map_id
// 					     									FROM service_plan_eq_type_mapping 	
// 					     									WHERE service_plan_id = ".$this->_service_plan_id."
// 					     									GROUP BY service_plan_eq_type_group_id
// 					     									HAVING COUNT( service_plan_eq_type_group_id ) = 1
// 					    								  )
// 			  ORDER BY spetg.service_plan_eq_type_group_id";
			  
// 		$equipments = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
// 		$service_plan_equipments='';
		
// 		foreach($equipments as $quipment){
// 			$service_plan_equipments[  $quipment['service_plan_eq_type_map_id'] ] = new Thelist_Model_serviceplaneqtypemap( $quipment['service_plan_eq_type_map_id'] );
// 		}
		
// 		return $service_plan_equipments;
		
// 	}
	
// 	public function get_required_one_withoutchoices_equipment(){
		
		
// 		$sql="SELECT spetm.service_plan_eq_type_map_id
// 			  FROM service_plan_eq_type_mapping spetm
// 			  LEFT OUTER JOIN service_plan_eq_type_groups spetg ON spetm.service_plan_eq_type_group_id = spetg.service_plan_eq_type_group_id
// 		 	  WHERE spetg.service_plan_eq_type_required_quantity = 1
// 			  AND   spetg.service_plan_eq_type_max_quantity = 1
// 			  AND service_plan_id = ".$this->_service_plan_id."
// 		      GROUP BY spetm.service_plan_eq_type_group_id
// 			  HAVING COUNT( spetm.service_plan_eq_type_group_id ) = 1";
		
// 		$equipments = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
		
// 		$service_plan_equipments='';
		
		
		
// 		foreach(is_array($equipments) || is_object($equipments) ? $equipments : array() as $quipment){
// 			$service_plan_equipments[ $quipment['service_plan_eq_type_map_id'] ] = new Thelist_Model_serviceplaneqtypemap( $quipment['service_plan_eq_type_map_id'] );
// 		}

// 		return $service_plan_equipments;
		
// 	}
	
	public function get_allinterface_types(){
		
		if( $this->all_if_types == null){
			$sql="
							SELECT DISTINCT(sit.if_type_id) as if_type_id
							FROM service_plan_eq_type_mapping spetm
							INNER JOIN eq_type_group_mapping etgm ON etgm.eq_type_group_id=spetm.eq_type_group_id
							INNER JOIN static_if_types sit ON sit.eq_type_id=etgm.eq_type_id
							WHERE spetm.service_plan_id=".$this->_service_plan_id;
			
			$service_plan_if_types = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			foreach($service_plan_if_types as $service_plan_if_type){
					
				$this->all_if_types[ $service_plan_if_type['if_type_id'] ] = new Thelist_Model_interfacetype( $service_plan_if_type['if_type_id'] );
					
			}
			
		}
		
	
		return $this->all_if_types;
	}
	
	public function get_service_plan_if_type_maps(){
		
		if($this->service_plan_if_type_maps == null){
			
			$sql="SELECT service_plan_if_type_map_id,if_type_id_a,if_type_id_b
						  FROM service_plan_if_type_mapping
						  WHERE service_plan_id=".$this->_service_plan_id;
			
			$service_plan_if_type_maps = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			foreach($service_plan_if_type_maps as $service_plan_if_type_map){
					
				$this->service_plan_if_type_maps[ $service_plan_if_type_map['service_plan_if_type_map_id'] ]['a'] = new Thelist_Model_interfacetype( $service_plan_if_type_map['if_type_id_a'] );
				$this->service_plan_if_type_maps[ $service_plan_if_type_map['service_plan_if_type_map_id'] ]['b'] = new Thelist_Model_interfacetype( $service_plan_if_type_map['if_type_id_b'] );
					
			}
		}
		
		return $this->service_plan_if_type_maps;
		
	}
	
	public function get_interface_type_as(){
		
		$sql="SELECT DISTINCT(sit.if_type_id) AS if_type_id
			  FROM service_plan_eq_type_mapping spetm
			  INNER JOIN eq_type_group_mapping etgm ON etgm.eq_type_group_id=spetm.eq_type_group_id
			  INNER JOIN static_if_types sit ON sit.eq_type_id=etgm.eq_type_id
			  WHERE spetm.service_plan_id=".$this->_service_plan_id."
			  AND if_type_id NOT IN(
			  						SELECT if_type_id_b
		      						FROM service_plan_if_type_mapping spitmb
		      						WHERE service_plan_id = ".$this->_service_plan_id."
		      						)";
		
		$interface_type_as = '';
		
				
		foreach( Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql) as $interface_type_a){
			$interface_type_as[ $interface_type_a['if_type_id'] ] = new Thelist_Model_interfacetype( $interface_type_a['if_type_id'] );
		}
		
		$this->interface_type_as	=	$interface_type_as;
		
		return $this->interface_type_as;
		
	}
	
	
	public function get_interface_type_bs($if_type_a){
		
		$sql="SELECT DISTINCT(sit.if_type_id) AS if_type_id
			  FROM service_plan_eq_type_mapping spetm
			  INNER JOIN eq_type_group_mapping etgm ON etgm.eq_type_group_id=spetm.eq_type_group_id
			  INNER JOIN static_if_types sit ON sit.eq_type_id=etgm.eq_type_id
			  WHERE spetm.service_plan_id=".$this->_service_plan_id."
			  AND if_type_id NOT IN(SELECT if_type_id_b
					   			    FROM service_plan_if_type_mapping spitmb
		      						WHERE service_plan_id = ".$this->_service_plan_id."
		      						)
			  AND if_type_id NOT IN(SELECT if_type_id_a
		      						FROM service_plan_if_type_mapping spitmb
		      						WHERE service_plan_id = ".$this->_service_plan_id."
		      						)
			  AND if_type_id !=".$if_type_a;
		
		$interface_type_bs = '';
		
		
		foreach( Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql) as $interface_type_b){
			$interface_type_bs[ $interface_type_b['if_type_id'] ] = new Thelist_Model_interfacetype( $interface_type_b['if_type_id'] );
		}
		
		
		$this->interface_type_bs	=	$interface_type_bs;
		
		return $this->interface_type_bs;
	
	}
	
	
	public function get_service_plan_eq_type_groups()
	{
		if($this->service_plan_eq_type_groups == null){
			
			$sql="SELECT spetm.eq_type_group_id,spetm.service_plan_eq_type_map_id
			 	  FROM service_plan_eq_type_mapping spetm
			      WHERE spetm.service_plan_id=".$this->_service_plan_id;
				
			$eq_type_groups	=	 Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			foreach( $eq_type_groups as $eq_type_group)
			{
				$this->service_plan_eq_type_groups[ $eq_type_group['eq_type_group_id'] ] = new Thelist_Model_equipmenttypegroup( $eq_type_group['eq_type_group_id'] , $eq_type_group['service_plan_eq_type_map_id'] );
			}
			
		}
			
		return $this->service_plan_eq_type_groups;
	}

	public function add_interface_type_map($if_type_a,$if_type_b){
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		$sql="SELECT COUNT(*) AS exist
			  FROM service_plan_if_type_mapping spitma 
			  WHERE service_plan_id = ".$this->_service_plan_id."
			  AND if_type_id_a=".$if_type_a."
			  AND if_type_id_b=".$if_type_b;
		
		$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if(!$exist){
			$data	= array(
											'service_plan_id'					=> $this->_service_plan_id,
											'if_type_id_a'						=> $if_type_a,
											'if_type_id_b'						=> $if_type_b 
			);
				
			return $sp_sp_if_feat_map_id = Zend_Registry::get('database')->insert_single_row('service_plan_if_type_mapping',$data,get_class($this),$method);
		}
		
	}
	
	public function delete_interface_type_map($if_type_a,$if_type_b)
	{
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		$sql="SELECT service_plan_if_type_map_id
			  FROM service_plan_if_type_mapping spitma 
			  WHERE service_plan_id = ".$this->_service_plan_id."
			  AND if_type_id_a=".$if_type_a."
		      AND if_type_id_b=".$if_type_b;
		
		$service_plan_if_type_map_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		//return $if_type_a.$if_type_b;
		return Zend_Registry::get('database')->delete_single_row($service_plan_if_type_map_id, 'service_plan_if_type_mapping', get_class($this),$method);
	}
	
	
	public function is_active()
	{	
		if($this->_activate != null && $this->_activate != '0000-00-00 00:00:00'){
		
			$time = new Thelist_Utility_time();
			
			if(
			($time->format_date_time($this->_activate, 'epoch') < $time->get_current_date_time_as_epoch()) 
			&& (($time->format_date_time($this->_deactivate, 'epoch') > $time->get_current_date_time_as_epoch()) || ($this->_deactivate == null))) {
				
				$this->_is_active = true;			
				
			} else {
			    $this->_is_active = false;
			}
			
		}else{
			$this->_is_active = false;
		}

		return $this->_is_active;
	}
	
	public function is_expired()
	{
		
		
		
		$today_date		 = strtotime( $this->_time->get_current_date_time() );
		$activate_date 	 = strtotime( $this->_activate );
		$deactivate_date = strtotime( $this->_deactivate );
		
		if( $this->_activate == '0000-00-00 00:00:00' && $this->_deactivate == '0000-00-00 00:00:00'  )
		{
			$this->is_expired = false;
			
		}
		else if( $this->_activate != '0000-00-00 00:00:00' && $this->_deactivate == '0000-00-00 00:00:00')
		{
			$this->is_expired = false;
			
		}
		else if( $this->_activate != '0000-00-00 00:00:00' && $this->_deactivate != '0000-00-00 00:00:00' )
		{
			if( ($activate_date < $today_date) && ($deactivate_date < $today_date) ){
				$this->is_expired = true;
			}else{
				$this->is_expired = false;
			}
			
		}else{
			$this->is_expired = false;
			
		}
		
		return $this->is_expired;
	}
	public function is_editable(){
		
		$this->is_editable = !$this->_is_active() && !$this->is_expired();
		
		return $this->is_editable; 
	}
	
	
	
	public function set_service_plan_name($new_value)
	{
		if ($this->service_plan_name != $new_value) {
			Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'service_plan_name', $new_value);
			$this->service_plan_name = $new_value;
		}
	}
	public function set_service_plan_desc($new_value)
	{
	
		$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'service_plan_desc', $new_value);
		if ($return != false) {
			
			$this->$return['0'] = $return['1'];
		}
		
	}
	
	public function set_service_plan_permanent_install_only($new_value){
		
		$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'service_plan_permanent_install_only', $new_value);
		
		if( $return != false){
			$this->$return['0'] = $return['1'];
		}	
	}
	
	public function set_service_plan_install_required_time($new_value)
	{

		$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'service_plan_install_required_time', $new_value);
		if ($return != false) {
			$this->$return['0'] = $return['1'];
		}

	}
	
	public function set_deactivate($new_value)
	{
	  
		if ($new_value == '') {
			
			return 'wrong format';
			
		}
		
		if ($this->_deactivate != null) {
			
			
				
				$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'deactivate', $new_value);
				if ($return != false) {
			
					$this->$return['0'] = $return['1'];
				}
	
				return true;
				
			

		} elseif ($this->_deactivate == null) {
			
		
			
			$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'deactivate', $new_value);
			if ($return != false) {
			
				$this->$return['0'] = $return['1'];
			}
			
				return true;
			
			
			
			
		}
		
	}
	
	// priviliged update commands
	public function set_activate($new_value)
	{
		
		if ($new_value == '') {
				
				
			return 'wrong format';
				
		}
		
		$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'activate', $new_value);
		if ($return != false) {
				$this->$return['0'] = $return['1'];
		}
			
		return true;
			
	}
	
	public function set_service_plan_type($new_value){
	
		$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'service_plan_type', $new_value);
		if ($return != false) {
			
			$this->$return['0'] = $return['1'];
		}
				
		return true;
				
		
	}
	
	public function set_service_plan_default_mrc($new_value){
	
		$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'service_plan_default_mrc', $new_value);
		if ($return != false) {
		
			$this->$return['0'] = $return['1'];
		}
	
		return true;
	
		
	}
	
	public function set_service_plan_default_nrc($new_value){
	
		
	
		$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'service_plan_default_nrc', $new_value);
		if ($return != false) {
			
			$this->$return['0'] = $return['1'];
		}
	
		return true;
	
		
	}
	
	public function set_service_plan_default_mrc_term($new_value){
	
		$return = Zend_Registry::get('database')->set_single_attribute($this->_service_plan_id, 'service_plans', 'service_plan_default_mrc_term', $new_value);
		
		if ($return != false) {
			
			$this->$return['0'] = $return['1'];

		}
	
		return true;
		
	}
	
	public function add_features($interface_feature_id,$if_feature_value){
		
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
	
		$sql="SELECT COUNT(*) AS exist
			  FROM service_plan_service_point_interface_feature_mapping
			  WHERE service_plan_id = ".$this->_service_plan_id."
			  AND if_feature_id = ".$interface_feature_id;
		
		$exist = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		if(!$exist){
			
			$data	= array(
								'service_plan_id'					=> $this->_service_plan_id,
								'if_feature_id'						=> $interface_feature_id,
								'if_feature_value'					=> $if_feature_value 
						   );
					
			return $sp_sp_if_feat_map_id = Zend_Registry::get('database')->insert_single_row('service_plan_service_point_interface_feature_mapping',$data,get_class($this),$method);
				
		}
	}
	
	public function update_features($interface_feature_id,$interface_feature_value){
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
		
		$sql="SELECT sp_sp_if_feat_map_id
			  FROM service_plan_service_point_interface_feature_mapping
			  WHERE service_plan_id = ".$this->_service_plan_id."
			  AND if_feature_id=".$interface_feature_id;
		$sp_sp_if_feat_amp_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
		
		
		$return = Zend_Registry::get('database')->set_single_attribute($sp_sp_if_feat_amp_id, 'service_plan_service_point_interface_feature_mapping', 'if_feature_value', $interface_feature_value,get_class($this),$method);
		if ($return != false) {
				
			$this->$return['0'] = $return['1'];
		}
		
		return true;
	}
	
	public function delete_features($interface_feature_id){
		$trace  = debug_backtrace();
		$method = $trace[0]["function"];
	
		$sql="SELECT sp_sp_if_feat_map_id
				  FROM service_plan_service_point_interface_feature_mapping
				  WHERE service_plan_id = ".$this->_service_plan_id."
				  AND if_feature_id=".$interface_feature_id;
		$sp_sp_if_feat_amp_id = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
	
		$return = Zend_Registry::get('database')->delete_single_row($sp_sp_if_feat_amp_id, 'service_plan_service_point_interface_feature_mapping', get_class($this),$method);
		
		return true;
	}
	
	
	public function get_features()
	{
		if ($this->_sp_if_feature_requirement == null) {
		
			$sql="SELECT sp_sp_if_feat_map_id,if_feature_id,if_feature_value
				  FROM service_plan_service_point_interface_feature_mapping
				  WHERE service_plan_id = ".$this->_service_plan_id;
			
			$if_feature_ids = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
			//foreach( $if_feature_ids as $if_feature_id)
			if (isset($if_feature_ids['0'])) {
				
				foreach( is_array( $if_feature_ids) || is_object( $if_feature_ids) ?  $if_feature_ids : array() as $if_feature ){
					
					$this->_sp_if_feature_requirement[ $if_feature['if_feature_id'] ] = new Thelist_Model_equipmentinterfacefeature( $if_feature['if_feature_id'] );
					$this->_sp_if_feature_requirement[ $if_feature['if_feature_id'] ]->set_sp_sp_if_feat_map_id( $if_feature['sp_sp_if_feat_map_id'] );
					$this->_sp_if_feature_requirement[ $if_feature['if_feature_id'] ]->set_serviceplan_if_feature_map_value(  $if_feature['if_feature_value'] );
					
				}
			}
		}
		
		return $this->_sp_if_feature_requirement;
	}
	
	public function toArray()
	{
		$obj_content	= print_r($this, 1);
   		$class_name		= get_class($this);
   			
   		//get all private variable names
   		preg_match_all("/\[(.*):".$class_name.":private\]/", $obj_content, $matches);
   		 
   		if (isset($matches['0']['0'])) {
   		
   			$complete['private_variable_names'] = $matches['1'];
   		
   			foreach ($matches['1'] as $index => $private_variable_name) {
   					
   				$one_variable	= $this->$private_variable_name;
   		
   				if (is_array($one_variable)) {
   					$complete['private_variable_type'][$index] = 'array';
   				} elseif (is_object($one_variable)) {
   					$complete['private_variable_type'][$index] = 'object';
   				} else {
   					$complete['private_variable_type'][$index] = 'string';
   				}
   			}
   			
   			foreach ($complete['private_variable_names'] as $private_index => $private_variable) {
   				
   				if ($complete['private_variable_type'][$private_index] == 'object') {
   					
   					if (method_exists($this->$private_variable, 'toArray')) {
   						$return_array[$private_variable] = $this->$private_variable->toArray();
   					} else {
   						$return_array[$private_variable] = 'CLASS IS MISSING toArray METHOD';
   					}
   					
   				} elseif ($complete['private_variable_type'][$private_index] == 'string') {
   					
   					$return_array[$private_variable] = $this->$private_variable;
   					
   				} elseif ($complete['private_variable_type'][$private_index] == 'array') {
   						
   					$array_tools	= new Thelist_Utility_arraytools();
   					$return_array[$private_variable] = $array_tools->convert_mixed_array_to_strings($this->$private_variable);

   				}
   			}
   		}

   		return $return_array;
	}
	
	
}
?>