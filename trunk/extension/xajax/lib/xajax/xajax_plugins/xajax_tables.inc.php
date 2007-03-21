<?php
	class xajaxExtTables extends xajaxResponsePlugin {
		var $sCallName = "xajax.ext.tables";
		
		// tables
		function appendTable($table, $parent) {
			$command = array('n'=>'et_at', 't'=>$parent);
			$this->addCommand($command, $table);	
		}
		function insertTable($table, $parent, $position) {
			$command = array('n'=>'et_it', 't'=>$parent, 'p'=>$position);
			$this->addCommand($command, $table);
		}
		function deleteTable($table) {
			$this->addCommand(array('n'=>'et_dt'), $table);
		}
		// rows
		function appendRow($row, $parent, $position = null) {
			$command = array('n'=>'et_ar', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			$this->addCommand($command, $row);
		}
		function insertRow($row, $parent, $position = null, $before = null) {
			$command = array('n'=>'et_ir', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			if (null != $before)
				$command['c'] = $before;
			$this->addCommand($command, $row);
		}
		function replaceRow($row, $parent, $position = null, $before = null) {
			$command = array('n'=>'et_rr', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			if (null != $before)
				$command['c'] = $before;
			$this->addCommand($command, $row);
		}
		function deleteRow($parent, $position = null) {
			$command = array('n'=>'et_dr', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			$this->addCommand($command, null);
		}
		function assignRow($values, $parent, $position = null, $start_column = null) {
			$command = array('n'=>'et_asr', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			if (null != $start_column)
				$command['c'] = $start_column;
			$this->addCommand($command, $values);
		}
		function assignRowProperty($property, $value, $parent, $position = null) {
			$command = array('n'=>'et_asr', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			$this->addCommand($command, array('p'=>$property, 'v'=>$value));
		}
		// columns
		function appendColumn($column, $parent, $position = null) {
			$command = array('n'=>'et_acol', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			$this->addCommand($command, $column);
		}
		function insertColumn($column, $parent, $position = null) {
			$command = array('n'=>'et_icol', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			$this->addCommand($command, $column);
		}
		function replaceColumn($column, $parent, $position = null) {
			$command = array('n'=>'et_rcol', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			$this->addCommand($command, $column);
		}
		function deleteColumn($parent, $position = null) {
			$command = array('n'=>'et_dcol', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			$this->addCommand($command, null);
		}
		function assignColumn($values, $parent, $position = null, $start_row = null) {
			$command = array('n'=>'et_ascol', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			if (null != $start_row)
				$command['c'] = $start_row;
			$this->addCommand($command, $values);
		}
		function assignColumnProperty($property, $value, $parent, $position = null) {
			$command = array('n'=>'et_ascol', 't'=>$parent);
			if (null != $position)
				$command['p'] = $position;
			$this->addCommand($command, array('p'=>$property, 'v'=>$value));
		}
		function assignCell($row, $column, $value) {
			$this->addCommand(array('n'=>'et_asc', 't'=>$row, 'p'=>$column), $value);
		}
		function assignCellProperty($row, $column, $property, $value) {
			$this->addCommand(array('n'=>'et_asc', 't'=>$row, 'p'=>$column), array('p'=>$property, 'v'=>$value));
		}
	}
	$xpm_instance = &xajaxPluginManager::getInstance();
	$xpm_instance->registerResponsePlugin(new xajaxExtTables());
?>