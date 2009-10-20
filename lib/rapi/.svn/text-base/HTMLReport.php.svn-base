<?php
class HTMLReport extends Report
{
	public $htmlHeaders;

	public function output()
	{
		if($this->htmlHeaders)
		{
			header('Content-type: text/html');
			print "<html>
					<head>
						<title>Report</title>
						<style>
						table
						{
						border-collapse:collapse;
						border:2px solid black;
						}

						td
						{
						padding:3px;
						border:1px solid grey;
						}

						thead
						{
						background-color:#e0e0e0;
						font-weight:bold;
						}
						</style>
					</head>
				<body>";
		}

		foreach($this->contents as $content)
		{
			switch($content->getType())
			{
			case "text":
				print "<p>".$content->getText()."</p>";
				break;

			case "table":
				print "<table><thead><tr><td>";
				$headers = $content->getHeaders();
				print implode("</td><td>",$headers);
				print "</td></tr></thead><tbody>";
				foreach($content->getData() as $row)
				{
					print "<tr><td>".implode("</td><td>",$row)."</td></tr>";
				}
				print "</tbody></table>";
				break;
			}
		}

		if($this->htmlHeaders)
		{
			print "</body></html>";
		}
		die();
	}
}
?>
