<h2>This is TEST 2 View file in: </h2>
<p><?php echo __FILE__; ?></p>
<p><?php echo 'Config param is:' . $this->config->item_get('data', 'EXAMPLE'); ?></p>
<h3>Language expression</h3>
<p><?php echo $this->language('sstitle'); ?></p>
<h3>Getting data from params</h3>
<p>a: <?php echo $this->a; ?></p>
<p>b: {var.b}</p>
<p>c: <?php echo $this->c; ?></p>
<p>d: {var.d}</p>
<p>x: <?php echo $this->x; ?></p>
<p>y: {var.y}</p>
<h3>App vars</h3>
<p>{var.base_url}</p>
