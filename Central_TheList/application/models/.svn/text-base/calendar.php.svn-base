<?php

class thelist_model_calendar{

	private $database;
	private $_time;
	private $date;
	
	public function __construct($date=null)
	{
		$this->date 	 = $date;
		$this->_time	 = Zend_Registry::get('time');

	}
	
	
	
	public function check_calendar($unit_id,$start,$stop,$uid,$installation_time=0){
	
		// for testing only
		//$installation_time = 45;
			
		$date	   = $this->date;		
		$temp 	   = explode('/',$date);
		$date 	   = $temp[2].'-'.$temp[0].'-'.$temp[1];
		$sql_start = $date.' '.$start;
		$sql_stop  = $date.' '.$stop;
			
			
		$task_status = new Thelist_Utility_items('install_calendar_status');
			
		$sql="SELECT ca.calendar_appointment_status,ca.calendar_appointment_id
					  FROM calendar_appointment_task_mapping catm
					  LEFT OUTER JOIN tasks t ON catm.task_id=t.task_id
					  LEFT OUTER JOIN calendar_appointments ca ON catm.calendar_appointment_id = ca.calendar_appointment_id
					  WHERE t.task_owner=".$uid."
					  AND ( 
	      					ca.scheduled_start_time BETWEEN '".$sql_start."' AND '".$sql_stop."'
							OR
	      					ca.scheduled_end_time BETWEEN '".$sql_start."' AND '".$sql_stop."'
	      					OR
	    				  	(
	      						ca.scheduled_start_time <= '".$sql_start."'
	      						AND
	      						ca.scheduled_end_time >= '".$sql_stop."'
	    				  	)
	    				  )
					 
	    			  GROUP BY ca.calendar_appointment_id
					 ";
			
		$is_there_task = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
			
		$output= '';
		
		if( count($is_there_task) > 0 ){
			// there's an appointment
	
			$appointment_id 	= $is_there_task[0]['calendar_appointment_id'];
	
			$timeslot 			= ceil($installation_time/15)-1;
			$overlap_time		= $timeslot*15;
			$sql_nextstart      = $date.' '.$this->_time->add_minute($start,15);
			$sql_nextstop		= $date.' '.$this->_time->add_minute($start,14+$overlap_time);
				
				
			$sql="SELECT count(*)
							  FROM calendar_appointment_task_mapping catm
							  LEFT OUTER JOIN tasks t ON catm.task_id=t.task_id
							  LEFT OUTER JOIN calendar_appointments ca ON catm.calendar_appointment_id = ca.calendar_appointment_id
							  WHERE t.task_owner=".$uid."
							  AND ( 
									ca.scheduled_start_time BETWEEN '".$sql_nextstart."' AND '".$sql_nextstop."'
									OR
									ca.scheduled_end_time BETWEEN '".$sql_nextstart."' AND '".$sql_nextstop."'
									OR
								  	(
										ca.scheduled_start_time <= '".$sql_nextstart."'
										AND
										ca.scheduled_end_time >= '".$sql_nextstop."'
								  	)
								  )
							  GROUP BY ca.calendar_appointment_id
							 ";
				
			$overlap = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
				
			if( $overlap ){
				// overlap more than 15 min
	
				if( $is_there_task[0]['calendar_appointment_status'] == $task_status->get_id('confirmed') ){
					// for confirmed
						
					$output = "<td name='timeslot' bubble='yes' appointment_id='".$appointment_id."' status='confirmed' start='".$start."' stop='".$stop."' date='".$date."' uid='".$uid."' style='background-color:#".$task_status->get_value('confirmed')."'>
									   		confirmed
									   </td>";
	
				}else if(  $is_there_task[0]['calendar_appointment_status'] == $task_status->get_id('tentative') ){
					// for tentative
						
					$output = "<td name='timeslot' bubble='yes' appointment_id='".$appointment_id."' status='tentative' start='".$start."' stop='".$stop."' date='".$date."' uid='".$uid."' style='background-color:#".$task_status->get_value('tentative')."'>
											tentative
									   </td>";
	
				}
			}else{ // purpple
				
// 				 $output = "<td name='timeslot' bubble='yes' appointment_id='".$appointment_id."' status='available' start='".$start."' stop='".$stop."' date='".$date."' uid='".$uid."' style='background-color:#".$task_status->get_value('overlapping')."'>
// 				15 mins overlap
// 				</td>";
				
	
				///  for tempolary 6/25/2012
				if( $is_there_task[0]['calendar_appointment_status'] == $task_status->get_id('confirmed') ){
					// for confirmed
	
					$output = "<td name='timeslot' bubble='yes' appointment_id='".$appointment_id."' status='confirmed' start='".$start."' stop='".$stop."' date='".$date."' uid='".$uid."' style='background-color:#".$task_status->get_value('confirmed')."'>
															   		confirmed
															   </td>";
	
				}else if(  $is_there_task[0]['calendar_appointment_status'] == $task_status->get_id('tentative') ){
					// for tentative
	
					$output = "<td name='timeslot' bubble='yes' appointment_id='".$appointment_id."' status='tentative' start='".$start."' stop='".$stop."' date='".$date."' uid='".$uid."' style='background-color:#".$task_status->get_value('tentative')."'>
																	tentative
															   </td>";
	
				}
			}
				
				
	
	
		}
		
		else{				// there's no appointment
		
			
			$sql="SELECT COUNT(*) AS exist
						  FROM user_unit_group_mapping uugm
						  LEFT OUTER JOIN unit_groups ug ON uugm.unit_group_id = ug.unit_group_id
						  LEFT OUTER JOIN unit_group_mapping ugm ON ug.unit_group_id = ugm.unit_group_id
						  WHERE uugm.user_id = ".$uid."
						  AND ugm.unit_id = ".$unit_id."
						  AND uugm.startdatetime <= '".$sql_start."'
						  AND uugm.enddatetime >= '".$sql_stop."'";
	
			$tech_available = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
	
			if($tech_available){
				// if tech availble at that time
					
				$timeslot 			= ceil($installation_time/15);
				$overlap_time		= $timeslot*15;
					
				$sql_nextstart      = $date.' '.$start;
				$sql_nextstop		= $date.' '.$this->_time->add_minute($start,$overlap_time-1);
				$d					= $this->_time->add_minute($start,$overlap_time-1);
					
				$sql="SELECT ca.calendar_appointment_id,TIME(ca.scheduled_start_time ) AS 'time'
							  FROM calendar_appointment_task_mapping catm
							  LEFT OUTER JOIN tasks t ON catm.task_id=t.task_id
							  LEFT OUTER JOIN calendar_appointments ca ON catm.calendar_appointment_id = ca.calendar_appointment_id
							  WHERE t.task_owner=".$uid."
							  AND ( 
									ca.scheduled_start_time BETWEEN '".$sql_nextstart."' AND '".$sql_nextstop."'
								  )
							  GROUP BY ca.calendar_appointment_id
							 ";
					
				$overlap_appointments = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql);
					
				if( count($overlap_appointments) > 0){
					// there's overlap
	
						
					$min = explode( ':',$this->_time->subtract_time($d,$overlap_appointments[0]['time'] ));
					$min = $min[1];
					if( $min  < 15 ){
						// overlap less than 15 mins
						
// 						 $output = "<td name='timeslot' status='tentative' start='".$start."' stop='".$stop."' date='".$date."' uid='".$uid."' style='background-color:#".$task_status->get_value('overlapping')."'>
// 						15 mins overlap
// 						</td>";
						
							
						///  for tempolary   6/25/2012
						$output = "<td name='timeslot' status='available' start='".$start."' stop='".$stop."' date='".$date."' uid='".$uid."' style='background-color:#".$task_status->get_value('available')."'>
											available
										   </td>";
					}else{// overlap more than 15 mins
						$output = "<td name='timeslot' status='available' start='".$start."' stop='".$stop."' date='".$date."' uid='".$uid."' style='background-color:#".$task_status->get_value('available')."'>
											available
										   </td>";
					}
	
						
	
				}else{
					$output = "<td name='timeslot' status='available' start='".$start."' stop='".$stop."' date='".$date."' uid='".$uid."' style='background-color:#".$task_status->get_value('available')."'>
											available
									   </td>";
				}
					
					
					
					
					
					
					
			}else if(!$tech_available){
					
				$output = "<td name='timeslot' status='not available' start='".$start."' stop='".$stop."' date='".$date."' uid='".$uid."' style='background-color:#".$task_status->get_value('not available')."'>
										not available
								   </td>";
					
			}
	
		}
			
		return $output;
			
			
			
			
			
			
	}

}
?>