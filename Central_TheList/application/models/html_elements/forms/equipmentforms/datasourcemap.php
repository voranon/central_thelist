<?php

class thelist_equipmentform_datasourcemap extends Zend_Form{

	public function __construct($options=null)
	{
		parent::__construct($options);

		
	$monitoring_ds_id = new Zend_Form_Element_Select('monitoring_ds_id',
								array(
									'label'      	=> 'Data Source:',
									'decorators' 	=> array(new datasourcemapdecorator($options))
	)
	);
	$monitoring_ds_id->setRegisterInArrayValidator(false);
	$monitoring_ds_id->setRequired(true);
	
	$activate	 = new Zend_Form_Element_Textarea('activate',
	array(
										'label'      	=> 'Activate:',
										'decorators' 	=> array(new datasourcemapdecorator($options))	
	)
	);
	$activate->setRequired(false);
	
	$deactivate	 = new Zend_Form_Element_Textarea('deactivate',
	array(
											'label'      	=> 'Deactivate:',
											'decorators' 	=> array(new datasourcemapdecorator($options))	
	)
	);
	$deactivate->setRequired(false);
		
	if ($options['function_type'] == 'add') {	
		
	$create = new Zend_Form_Element_Submit('create',
	array(
									'label' => 'Create', 'value' => 'C',
									'decorators' => array(new datasourcemapdecorator($options))
	)
	);
	
	$this->addElements(array(
	$monitoring_ds_id,
	$activate,
	$deactivate,
	$create)
	);
		
	} elseif ($options['function_type'] == 'edit') {
		
	$edit = new Zend_Form_Element_Submit('edit',
	array(
										'label' => 'Edit', 'value' => 'E',
										'decorators' => array(new datasourcemapdecorator($options))
	
	)
	);
		
	$delete = new Zend_Form_Element_Submit('delete',
	array(
										'label' => 'Delete', 'value' => 'D',
										'decorators' => array(new datasourcemapdecorator($options))
	
		)
	);
		
	$this->addElements(array(
	$monitoring_ds_id,
	$activate,
	$deactivate,
	$edit,
	$delete)
	);
	
		}
	
	}

}


class datasourcemapdecorator extends Zend_Form_Decorator_Abstract
{
	private $database;
	private $_time;
	private $_function_type;
	private $_monitoring_guid_ds_map_id=null;
	private $_eq_id;
	private $_monitoring_guid_id=null;
	
	public function __construct($options=null){


		$this->_time				= Zend_Registry::get('time');
		
		if (isset($options['function_type'])) {
				
			$this->_function_type = $options['function_type'];

		}
		if (isset($options['monitoring_guid_ds_map_id'])){
			
			$this->_monitoring_guid_ds_map_id = $options['monitoring_guid_ds_map_id'];
				
		}
		if (isset($options['eq_id'])){
				
			$this->_eq_id = $options['eq_id'];
		
		}
		if (isset($options['monitoring_guid_id'])){
		
			$this->_monitoring_guid_id = $options['monitoring_guid_id'];
		
		}
		
		if ($this->_function_type == 'edit') {

		}
		
	
	}
	
	public function render($content)
	{

		$element = $this->getElement();
		$messages = $element->getMessages();
		$name    = htmlentities($element->getFullyQualifiedName());
		$label   = htmlentities($element->getLabel());
		$id      = htmlentities($element->getId());
		$value   = htmlentities($element->getValue());

		if ($name == 'monitoring_ds_id'){
			
			
			if ($this->_function_type == 'add') {
				
				$format=	"<fieldset><legend>Data sources</legend><table style='width:500px'><tr><tr><td>%s</td><td>
							<select name='%s' id='%s' style='width: 300px'>	
							<option value=''>---SELECT ONE---</option>";
				
					$sql = 	"SELECT GROUP_CONCAT(monitoring_ds_id) FROM monitoring_guid_ds_mapping
							WHERE monitoring_guid_id='".$this->_monitoring_guid_id."'
							";
					
					$current_datasources = Zend_Registry::get('database')->get_thelist_adapter()->fetchOne($sql);
					
					
					if ($current_datasources == '') {

						$sql2 = 	"SELECT * FROM monitoring_data_sources mds
									INNER JOIN device_functions df ON df.device_function_id=mds.device_function_id
									INNER JOIN device_function_mapping dfm ON dfm.device_function_id=df.device_function_id
									INNER JOIN equipment_type_software_mapping etsm ON etsm.eq_type_software_map_id=dfm.eq_type_software_map_id
									INNER JOIN equipments e ON e.eq_type_id=etsm.eq_type_id
									WHERE e.eq_id='".$this->_eq_id."'
									";
							
						$datasources = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);

					} else {
						
						$sql2 = 	"SELECT * FROM monitoring_data_sources mds
									INNER JOIN device_functions df ON df.device_function_id=mds.device_function_id
									INNER JOIN device_function_mapping dfm ON dfm.device_function_id=df.device_function_id
									INNER JOIN equipment_type_software_mapping etsm ON etsm.eq_type_software_map_id=dfm.eq_type_software_map_id
									INNER JOIN equipments e ON e.eq_type_id=etsm.eq_type_id
									WHERE e.eq_id='".$this->_eq_id."'
									AND mds.monitoring_ds_id NOT IN (".$current_datasources.")
									";
							
						$datasources = Zend_Registry::get('database')->get_thelist_adapter()->fetchAll($sql2);
						
						
					}

					
				foreach($datasources as $datasource){
		
						$format.= "<option value='".$datasource['monitoring_ds_id']."'>Name: ".$datasource['rrd_ds_name'].". Step: ".$datasource['rrd_step'].".</option>";
		
						}

				$format.="</select></td><td>%s</td></tr>";
			
					return sprintf($format,$label,$name,$name,$element->getView()->formErrors($messages));
				
			} else {
				
				
				
			}


		} elseif ($name == 'activate') {
			
			$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text' value='NOW'></input></td>
								<td>&nbsp;</td>
								</tr>";
			
			return sprintf($format,$label,$name,$name);
			
			
		} elseif ($name == 'deactivate') {
			
			$format="			<tr>
								<td style='width:200px'>%s</td>
								<td style='width:150px'><input name='%s' id='%s' type='text' class='text' value='NEVER'></input></td>
								<td>&nbsp;</td>
								</tr>";
			
			return sprintf($format,$label,$name,$name);
			
			
		}
		
		if ($this->_function_type == 'add') {
			if($name == 'create'){
				$format="			<tr><td colspan='3' align='center'>
						
											<input name='%s' id='%s' type='submit' class='button' value='Create'></input>	
											<input name='eq_id' type='hidden' value='".$this->_eq_id."'></input>
											<input name='monitoring_guid_id' type='hidden' value='".$this->_monitoring_guid_id."'></input>									
											</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
		
			}
				
		} else if ($this->_function_type == 'edit') {
				
			if($name == 'delete'){
				$format="			<tr><td colspan='3' align='center'>
								
													<input name='%s' id='%s' type='submit' class='button' value='Delete'></input>
													<input name='vlan_pri_key_id' type='hidden' value='".$this->_variable."'></input>
													<input name='eq_id' type='hidden' value='".$this->_eq_id."'></input>
													<input name='if_id' type='hidden' value='".$this->_if_id."'></input>	
													</td></tr></table></fieldset>
											";	
		
				return sprintf($format,$name,$name);
					
			}
		
		}
		
	}
}