<?php
fputs(STDERR, "Ntentan Error!\n");
fputs(STDERR, strip_tags("$message\n"));
if($showTrace === true)
{
    foreach($trace as $trace_item)
    {
        fputs(STDERR, "{$trace_item["file"]}\t{$trace_item["line"]}\t"
         . $trace_item["class"] . $trace_item["type"]
         . $trace_item["function"] . "\n"
        );
    }
}
