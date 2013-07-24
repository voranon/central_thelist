<?php

class thelist_utility_querybuilder
{
	private $query;
	private $showcolumns;
	private $tables;
	private $searchon;
	private $where;
	
	function __construct()
	{
		
	}
	
	function build_query($showcolumns=null,$tables,$where=null,$searchon=null){
		
		if($tables != null)
		{
			$this->query=$this->build_showcolumns($showcolumns).'&#10;'.
		    	         $this->build_tables($tables).'&#10;'.
		        	     $this->build_where($where).'&#10;'.
		            	 $this->build_searchon($searchon);
		}else{
			$this->query;
		}
		return $this->query;
		
	}
	
	
	private function build_showcolumns($showcolumns=null)
	{
		if($showcolumns==null){
			$this->showcolumns = 'SELECT *';
		}else{
			$showcolumns = substr_replace($showcolumns ,"",-1);
			$showcolumns = explode(",",$showcolumns);
			foreach ( $showcolumns as $showcolumn){
				$this->showcolumns.= $showcolumn.',';
			}
			$this->showcolumns = substr_replace($this->showcolumns,"",-1);
			$this->showcolumns = 'SELECT '.$this->showcolumns;
		}
		return $this->showcolumns;
	}
	
	private function build_tables($tables){
		
		$tables = substr_replace($tables ,"",-1);
		
		$tables = explode(",",$tables);
		
		$this->tables = 'FROM ';
		
		foreach($tables as $table){
			$this->tables.=$table.','; 	
		}
		$this->tables = substr_replace($this->tables,"",-1);
		return $this->tables;
	}
	
	private function build_where($where=null){
		return $this->where;
	}
	
	private function build_searchon($searchon=null){
		return $this->searchon;
	}
	
	
	
	private function get_query()
	{
		return $this->query;
	}

}
?>