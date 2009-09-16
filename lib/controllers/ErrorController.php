<?php
class ErrorController extends Controller
{
	public function __construct(&$t,$path)
	{
		Application::setTitle("Access Restricted");
		$this->label = "Error";
		$this->description = "There was an error loading the content that you requested.";
	}

	public function getContents()
	{
		$error_message =
		"You may be seeing this message because
		<ol>
			<li>You may not have the right to access the content you are requesting</li>
			<li>The content you are requesting does not exist</li>
			<li>There is an error with the system</li>
		</ol>";
		return $error_message;
	}
}
?>
