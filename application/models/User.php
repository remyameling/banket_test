<?php 

class App_Model_User extends App_Model_table{

	
	
	public function fetchByUserName($user_name)
	{
		//return array('id'=>31);
		
    	$table  	= $this->getTable();
		$row 		= $table->fetchRow($table->select()->where('user_name = ?', $user_name));
		
		if ($row != NULL)
			return $this->fetchEntry($row['id']);
		else
			return array();
    }
    
	public function fetchByGroup($group_id)
    {
		assert($group_id !== NULL);
			
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('u'=>'user'));
		$select->where('u.group_id = ?',$group_id);
		
		$this->Log($select->assemble());
				
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
				
		return $rows;
    }
    
	public function fetchRoleByUserName($user_name)
    {
		assert($user_name !== NULL);
			
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('u'=>'user'),array());
		$select->join(array('g'=>'group'),'u.group_id = g.id',array('role'=>'group_uniquename'));
		$select->where('u.user_name = ?',$user_name);
		
		//die($select->assemble());
				
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
				
		if ((NULL !== $rows) && (isset($rows[0])))
		{
			return $rows[0];//['role'];
		}
		else
			return NULL;
    }
    
	public function fetchRoleByUserId($user_id)
    {
		assert($user_id !== NULL);
		
		
			
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('u'=>'user'),array());
		$select->join(array('g'=>'group'),'u.group_id = g.id',array('role'=>'group_uniquename'));
		$select->where('u.id = ?',$user_id);
				
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
				
		if ((NULL !== $rows) && (isset($rows[0])))
		{
			return $rows[0];
		}
		else
			return NULL;
    }
    
	public function fetchMostActive($limit=5)
    {
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();

		$fields = $this->getTable()->getFields();
		
		$select->from(array('u'=>'user'),$fields);
		$select->limit($limit);
				
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
		
		$this->Log($select->assemble());
				
		if ((NULL !== $rows) && (isset($rows[0])))
		{
			return $rows;
		}
		else
			return NULL;
    }
    
	public function checkUniqueUsername($user_name,$id=NULL){
    
    		// check if $user_name already exists
    		$db     = Zend_Registry::getInstance()->dbAdapter;
			$select = $db->select();

			$select->from(array('u'=>'user'));
			$select->where("user_name = ?",$user_name);
			
			if ($id !== NULL)
				$select->where("id <> ?",$id);
			
			$stmt = $db->query($select);
			$rows = $stmt->fetchAll();
			
			if (count($rows) > 0)
				return true;
			else
				return false;
    }
    
	public function checkUniqueEmail($user_email,$id=NULL){
    
    		// check if $user_email already exists
    		
    		$db     = Zend_Registry::getInstance()->dbAdapter;
			$select = $db->select();

			$select->from(array('u'=>'user'));
			$select->where("user_email = ?",$user_email);
			
			if ($id !== NULL)
				$select->where("id <> ?",$id);
			
			$stmt = $db->query($select);
			$rows = $stmt->fetchAll();
			
			if (count($rows) > 0)
				return true;
			else
				return false;
    }
    
	public function fetchByEmailAddress($user_email){
    
    		// fetch user by e-mail address
    		
    		$db     = Zend_Registry::getInstance()->dbAdapter;
			$select = $db->select();

			$select->from(array('u'=>'user'));
			$select->where("user_email = ?",$user_email);
			
			$stmt = $db->query($select);
			$rows = $stmt->fetchAll();
			
			if (count($rows) == 1)
				return $rows[0];
			else
				return NULL;
    }
    
	public function delete($id,$bDeleteMember=true,$bDeleteManager=true)
	{
		$data = $this->fetchEntry($id);
		
		if ($bDeleteMember)
		{
			// verwijder member indien aanwezig
			
			$memberMdl  = new App_Model_Member();
			$data       = $memberMdl->fetchByUserId($id);
			if (!empty($data))
				$memberMdl->delete($data['id']);
		}
		
		parent::delete($id);
	}
	
	public function fetchDefault()
    {
		$db     = Zend_Registry::getInstance()->dbAdapter;
		$select = $db->select();		
		
		$select->from(array('u'=>'user'));
		$select->order('user_alias asc');
		$select->limit(1);
		
		$stmt = $db->query($select);
		$rows = $stmt->fetchAll();
				
		return $rows[0];		
    }
	
}