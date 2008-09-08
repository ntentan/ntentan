<?php
abstract class DatabaseResult
{
	abstract public function getNumRows();
	abstract public function getNumFields();
	abstract public function fetchRow();
}
?>