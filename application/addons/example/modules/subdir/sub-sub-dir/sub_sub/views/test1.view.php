<h2>This is TEST 1 View file in: </h2>
<p><?php echo __FILE__; ?></p>
<p><?php echo 'Config param is:' . $this->config->item_get('data', 'EXAMPLE'); ?></p>
<p><?php echo $this->language('fromlngtoscreen'); ?></p>
<p>
<?php
 echo $this->language(
 		'withargsexp',
	    array(
	    	'David',
		    TK_SHORT_NAME
	    )
	 );
?>
</p>
<h1>