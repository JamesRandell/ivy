<?php

class Ivy_Workflow extends Ivy_Database
{

	private $pkKey = '';
	private $tableName = '';
	public $data;
	public $collar = '';
	
	
	public function __construct ($file, $fieldArray = null)
	{
		parent::__construct ($file, $fieldArray);
		$this->pkKey = $this->schema['tableSpec']['pk'][0];
		$this->tableName = $this->schema['tableSpec']['name'];
	}
	
	
	public function insert ($dataArray = array ())
	{
		(string) $where = '';
		(string) $pkKey = '';
		(object) $wfStatus = '';
		(object) $wf = '';
		(int) $pkValue = 0;
		
		
		if (empty($dataArray)) {
			if (empty($_POST)) {
				return FALSE;
			} else {
				$dataArray = $_POST;
			}
		}

		if (parent::insert($dataArray) === FALSE) {
			return FALSE;
		}

		foreach ($this->data[0] as $key => $value) {
			if (empty($this->data[0][$key])) {
				continue;
			}
			$where .= " $key = '$value' AND ";
		}
		
		$where = rtrim($where, ' AND ');

		$r = parent::select($where);
		
		$pkKey = $this->schema['tableSpec']['pk'][0];
		$pkValue = $this->data[0][$pkKey];
		
		$wfStatus = new Ivy_Database('ivy_workflowstatus');
		
		
		$wf = new Ivy_Database('ivy_workflowstate');
		/*
		 * Order by the number (order of the states in a workflow) 
		 * so that the first record is always the first state to use
		 */
		$wf->order = 'NUMBER ASC';
		
		$wf->select("SITEID = '" . SITE . "' AND TITLE = 'Default'");
		

		$array['CONTENTID'] = $pkValue;
		$array['SITEID'] = SITE;
		$array['STATEID'] = $wf->data[0]['WORKFLOWSTATEID'];

		$wfStatus = new Ivy_Database('ivy_workflowstatus');
		$wfStatus->insert($array);
	}
	
	public function select ($where = NULL, $fieldArray = array ())
	{
		$this->sql("SELECT ivy_workflowstatus.CONTENTID " .
				"FROM " . $this->tableName . "
				INNER JOIN 
  					ivy_workflowstatus ON " . $this->tableName . "." . $this->pkKey . " = ivy_workflowstatus.CONTENTID 
				INNER JOIN 
  					ivy_workflowstate ON ivy_workflowstatus.STATEID = ivy_workflowstate.WORKFLOWSTATEID 
				WHERE ivy_workflowstatus.SITEID = '" . SITE . "' 
					AND ivy_workflowstate.TYPE = 'PUBLISHED'");

		$this->parentSelect($where, $fieldArray);
	}
	
	public function selectPublished ($where, $fieldArray)
	{
		$this->select($where, $fieldArray);
	}
	
	public function selectUnpublished ($where, $fieldArray)
	{
		$this->sql("SELECT ivy_workflowstatus.CONTENTID " .
				"FROM " . $this->tableName . "
				INNER JOIN 
  					ivy_workflowstatus ON " . $this->tableName . "." . $this->pkKey . " = ivy_workflowstatus.CONTENTID 
				INNER JOIN 
  					ivy_workflowstate ON ivy_workflowstatus.STATEID = ivy_workflowstate.WORKFLOWSTATEID 
				WHERE ivy_workflowstatus.SITEID = '" . SITE . "' 
					AND ivy_workflowstate.TYPE = 'UNPUBLISHED'");

		$this->parentSelect($where, $fieldArray);		
	}
	
	public function selectDeleted ($where, $fieldArray)
	{
		$this->sql("SELECT ivy_workflowstatus.CONTENTID " .
				"FROM " . $this->tableName . "
				INNER JOIN 
  					ivy_workflowstatus ON " . $this->tableName . "." . $this->pkKey . " = ivy_workflowstatus.CONTENTID 
				INNER JOIN 
  					ivy_workflowstate ON ivy_workflowstatus.STATEID = ivy_workflowstate.WORKFLOWSTATEID 
				WHERE ivy_workflowstatus.SITEID = '" . SITE . "' 
					AND ivy_workflowstate.TYPE = 'DELETED'");

		$this->parentSelect($where, $fieldArray);		
	}
	
	
	public function delete ($id)
	{
		
		$this->sql("UPDATE ivy_workflowstatus SET DATEUPDATED = '" . time() . "', STATEID = 
				(SELECT WORKFLOWSTATEID FROM ivy_workflowstate WHERE TYPE = 'DELETED') 
				WHERE CONTENTID = '$id'");
	}
	
	public function progress ($id)
	{
		if (!empty($_POST)) { 
			return $this->sql("UPDATE ivy_workflowstatus SET DATEUPDATED = '" . time() . "', STATEID = 
				'" . $_POST['STATE'] . "'	WHERE CONTENTID = '$id'");
		}
		
		
		
		$this->sql("SELECT STATEID FROM ivy_workflowstatus WHERE CONTENTID = '$id'");
		
		$stateId = $this->data[0]['STATEID'];
		
		$this->sql("SELECT 
		  ivy_workflowstate.WORKFLOWSTATEID,
		  ivy_workflowstate.STATE 
		FROM ivy_workflowuser
		INNER JOIN ivy_workflowstate on ivy_workflowuser.STATEID = ivy_workflowstate.WORKFLOWSTATEID 
		WHERE ivy_workflowuser.COLLAR = '" . $this->collar . "'");

		foreach ($this->data as $id => $data) {
			$array[ $data['WORKFLOWSTATEID'] ] = $data['STATE'];
		}
		$this->schema['fieldSpec'] = array ();
		
		
		$this->schema['fieldSpec'] = array (
			'STATE'	=>	array (
				'front'	=>	array (
					'title'	=>	'Transition to',
					'type'	=>	'select',
					'option'=>	$array,
					'tip'	=>	'You have the option to progress this content item 
								to any of the above states.',
				),
				'back'		=> array (
					'type'		=>	'varchar',
					'size'		=>	26,
				),
			),
		);
		
		$this->schema['data'][0]['STATE'] = $stateId;		
	}
	
	public function queue ($collar, $fieldArray)
	{
		$this->sql("SELECT 
			  ivy_workflowstatus.CONTENTID, 
			  ivy_workflowstate.STATE 
			FROM 
			  ivy_workflowuser
			INNER JOIN 
			  ivy_workflowstate ON ivy_workflowuser.STATEID = ivy_workflowstate.WORKFLOWSTATEID 
			INNER JOIN 
			  ivy_workflowstatus ON ivy_workflowstate.WORKFLOWSTATEID  = ivy_workflowstatus.STATEID 
			INNER JOIN 
			  " . $this->tableName . " ON ivy_workflowstatus.CONTENTID  = " . $this->tableName . "." . $this->pkKey . "
			WHERE ivy_workflowuser.COLLAR = '" . $collar . "'");
		
		
		$t = $this->parentSelect(null, $fieldArray);
		
		$this->schema['fieldSpec']['STATE']['front'] = array (
			'title'	=>	'State',
			'type'	=>	'text'
		);
		
		return $t;
		
	}
	
	private function parentSelect ($where = null, $fieldArray = null)
	{
		(object) $wfStatus = '';
		(string) $string = '';

		
		foreach ($this->data as $id => $data) {
			$string .= $data['CONTENTID'] . ',';
			#$array[ $data['CONTENTID'] ] = $data;
		}
		
		$string = rtrim($string, ',');
		
		if ($where) {
			$where .= ' AND ';
		}



		return parent::select($where . ' ' . $this->pkKey . ' IN (' . $string . ')', $fieldArray);


		
	}

	

	
}
?>