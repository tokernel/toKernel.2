<form method="post">
	<?php if(!empty($this->messages)) { ?>
	<pre><?php print_r($this->messages); ?></pre>
	<?php } ?>
	Title<br/>
	<input type="text" name="title" value="{var.title}">
	<br />
	Name<br/>
	<input type="text" name="name" value="{var.name}">
	<br />
	Age (DOB)<br/>
	<input type="text" name="age" value="{var.age}">
	<br />
	Email<br/>
	<input type="text" name="email" value="{var.email}">
	<br />
	Phone numbers<br />
	<input type="text" name="phone_number[]" value="{var.phone_number_1}">
	<br />
	<input type="text" name="phone_number[]" value="{var.phone_number_2}">
	<br />
	<input type="text" name="phone_number[]" value="{var.phone_number_3}">

	<br />
	<input type="submit" value="Submit">
</form>