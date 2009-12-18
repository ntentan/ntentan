<?php
//! An interface for all Elements which interact in one way or the
//! other with a database.
//! \ingroup Form_API
interface DatabaseInterface
{
	//! Sets the data for the form.
	public function setData($data);
	
	//! Gets the data for the form.
	public function getData($storable=false);
}
?>
