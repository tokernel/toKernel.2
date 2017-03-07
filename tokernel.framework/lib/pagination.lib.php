<?php
/**
 * toKernel - Universal PHP Framework.
 * Data pagination lib
 * 
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   library
 * @package    framework
 * @subpackage library
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2017 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    2.1.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 2.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * pagination_lib class
 * 
 * @author David A. <tokernel@gmail.com>
 */
class pagination_lib {

/**
 * Library object for working with
 * libraries in this class
 *
 * @access protected
 * @var object
 */
 protected $lib;

/**
 * Configuration array
 * 
 * @access protected
 * @var array
 */	
 protected $config = array();

/**
 * Numbers count in pagination
 *
 * @access protected
 * @var int
 */
 protected $numbers_count = 10;

/**
 * Class constructor
 * 
 * @access public
 * @param mixed $config_arr
 * @return void
 */
 public function __construct($config_arr = NULL) {

	$this->lib = lib::instance();

 	$this->config = array(
	    'display_numbers' => true,
	    'allow_divider_tag' => true,
	    'allow_first_last_tags' => true,
	    'container_tag' => '<ul class="pagination">{var.content}</ul>',
	    'go_first_tag' => '<li class="{var.item_class}"><a href="{var.url}" class="{var.paging_item_class}" data-number="{var.number}"><i class="fa fa-angle-left"></i><i class="fa fa-angle-left"></i></a></li>',
	    'go_prev_tag' => '<li class="{var.item_class}"><a href="{var.url}" class="{var.paging_item_class}" data-number="{var.number}"><i class="fa fa-angle-left"></i></a></li>',
	    'number_tag' => '<li class="{var.item_class} hidden-xs"><a href="{var.url}" class="{var.paging_item_class}" data-number="{var.number}">{var.number}</a></li>',
	    'active_number_tag' => '<li class="{var.item_class}"><a href="{var.url}" class="{var.paging_item_class}" data-number="{var.number}">{var.number}</a></li>',
	    'divider_tag' => '<li class="{var.item_class} hidden-xs"><a href="#">...</a></li>',
	    'go_next_tag' => '<li class="{var.item_class}"><a href="{var.url}" class="{var.paging_item_class}" data-number="{var.number}"><i class="fa fa-angle-right"></i></a></li>',
	    'go_last_tag' => '<li class="{var.item_class}"><a href="{var.url}" class="{var.paging_item_class}" data-number="{var.number}"><i class="fa fa-angle-right"></i><i class="fa fa-angle-right"></i></a></li>',
	    'paging_item_class' => 'paging_item',
	    'active_item_class' => 'active',
	    'disabled_item_class' => 'disabled',
        'offset_var' => '{var.offset}'
    );

	if(!is_null($config_arr)) {
		$this->config = array_merge($this->config, $config_arr);
	}

 } // end constructor
 
/**
 * Return instance of this object
 * 
 * @access public
 * @param mixed $config_arr
 * @return object
 */ 
 public function instance($config_arr = NULL) {

	$obj = clone $this;
	$obj->__construct($config_arr);

 	return $obj;

 } // End func instance
 
/**
 * Return 1 if not valid offset number
 * 
 * @access public
 * @param mixed $offset
 * @return integer
 */	
 public function to_offset($offset) {

	if(!$this->lib->valid->digits($offset, 1)) {
		return 1;
	}

	return $offset;

 } // end func to_offset

 /**
  * Convert offset to dabase query offset value.
  * This function is useful, if you want to mysql query limit by offset.
  *
  * @access public
  * @param int $offset
  * @param int $limit
  * @return int
  * @since v.2.0.0
  */
 public function to_db_offset($offset, $limit) {

	 if($offset <= 1) {
		 return 0;
	 }

	 return $offset * $limit - $limit;

 } // End func to_db_offset

/**
 * Return pagination buffer as string
 * 
 * @access public
 * @param integer $total
 * @param integer $limit
 * @param integer $offset
 * @param string $base_url
 * @return mixed 
 */ 
 public function run($total, $limit, $offset, $base_url = '#') {
 
 	if($offset < 0 or $total <= 0 or $limit <= 0 or $total <= $limit) {
    	return '';
    }

	$pages_count = ceil($total / $limit);

	$buffer = '';

	// If offset is 1, than first items should be disabled
	if($offset == 1) {
		$class = $this->config['disabled_item_class'];
	} else {
		$class = '';
	}

	// If don't displaying numbers, than show Go first link
	if($this->config['allow_first_last_tags'] == true) {

		$go_first_tag = $this->config['go_first_tag'];
		$go_first_tag = str_replace('{var.item_class}', $class, $go_first_tag);

		$url = $base_url;
		$number = 1;

		if($url != '#') {
            $url = $this->num_to_url($url, $number);
		}

		$go_first_tag = str_replace('{var.url}', $url, $go_first_tag);
		$go_first_tag = str_replace('{var.number}', $number, $go_first_tag);

		$go_first_tag = str_replace('{var.paging_item_class}', $this->config['paging_item_class'], $go_first_tag);

		$buffer .= $go_first_tag;
	}

	// Go prev link
	$go_prev_tag = $this->config['go_prev_tag'];
	$go_prev_tag = str_replace('{var.item_class}', $class, $go_prev_tag);

	$url = $base_url;

	if($offset == 1) {
		$number = 1;
	} else {
		$number = ($offset - 1);
	}

	if($url != '#') {
        $url = $this->num_to_url($url, $number);
	}

	$go_prev_tag = str_replace('{var.url}', $url, $go_prev_tag);
	$go_prev_tag = str_replace('{var.number}', $number, $go_prev_tag);
	$go_prev_tag = str_replace('{var.paging_item_class}', $this->config['paging_item_class'], $go_prev_tag);

	$buffer .= $go_prev_tag;

	// Pagination numbers
	if($this->config['display_numbers'] == true) {

		// Case 1. Pages count less or equal to 10
		if($pages_count <= $this->numbers_count) {
			$buffer .= $this->number_tag($offset, range(1, $pages_count), $base_url);
		}

		// Case 2. Pages count > 10
		if($pages_count > $this->numbers_count) {

			$divider = $this->config['divider_tag'];
			$divider = str_replace('{var.item_class}', $this->config['disabled_item_class'], $divider);

			$d = 5;
			$first_number = $offset - $d;
			if($first_number < 1) {
				$first_number = 1;
			}

			if(($pages_count - $first_number) < $this->numbers_count) {
				$first_number = $pages_count - $this->numbers_count + 1;
			}

			$last_number = ($first_number + ($this->numbers_count - 1));

			if($last_number > $pages_count) {
				$last_number = $pages_count;
			}

			$items_arr = array();

			for($i = $first_number; $i <= $last_number; $i++) {
				$items_arr[] = $i;
			}

			if($this->config['allow_divider_tag'] == true and $first_number > 1) {
				$buffer .= $divider;
			}

			$buffer .= $this->number_tag($offset, $items_arr, $base_url);

			if($this->config['allow_divider_tag'] == true and $last_number < $pages_count) {
				$buffer .= $divider;
			}

		}

	} // End if numbers

	// Go Next Link
	if($offset == $pages_count) {
		$class = $this->config['disabled_item_class'];
	} else {
		$class = '';
	}

	$go_next_tag = $this->config['go_next_tag'];
	$go_next_tag = str_replace('{var.item_class}', $class, $go_next_tag);

	$url = $base_url;

	if($offset == $pages_count) {
		 $number = $pages_count;
	} else {
		 $number = ($offset + 1);
	}

	if($url != '#') {
        $url = $this->num_to_url($url, $number);
	}

	$go_next_tag = str_replace('{var.url}', $url, $go_next_tag);
	$go_next_tag = str_replace('{var.number}', $number, $go_next_tag);
	$go_next_tag = str_replace('{var.paging_item_class}', $this->config['paging_item_class'], $go_next_tag);

	$buffer .= $go_next_tag;

	// If don't displaying numbers, than show Go last link
	if($this->config['allow_first_last_tags'] == true) {

		$go_last_tag = $this->config['go_last_tag'];
		$go_last_tag = str_replace('{var.item_class}', $class, $go_last_tag);

		$url = $base_url;
		$number = $pages_count;

		if($url != '#') {
            $url = $this->num_to_url($url, $number);
		}

		$go_last_tag = str_replace('{var.url}', $url, $go_last_tag);
		$go_last_tag = str_replace('{var.number}', $number, $go_last_tag);
		$go_last_tag = str_replace('{var.paging_item_class}', $this->config['paging_item_class'], $go_last_tag);

		$buffer .= $go_last_tag;
	}

	$buffer = str_replace('{var.content}', $buffer, $this->config['container_tag']);

	return $buffer;

 } // end func run

/**
 * Build and return number tag
 *
 * @access protected
 * @param int $offset
 * @param array $items_arr
 * @param string $base_url
 * @return string
 * @since v.2.0.0
 */
 protected function number_tag($offset, $items_arr, $base_url) {

	 $buffer = '';

	 foreach($items_arr as $i) {

		 if($offset == $i) {
			 $class = 'active';
		 } else {
			 $class = '';
		 }

		 $url = $base_url;
		 if($url != '#') {
		     $url = $this->num_to_url($url, $i);
		 }

		 $num_tag = $this->config['number_tag'];
		 $num_tag = str_replace('{var.item_class}', $class, $num_tag);
		 $num_tag = str_replace('{var.url}', $url, $num_tag);
		 $num_tag = str_replace('{var.number}', $i, $num_tag);
		 $num_tag = str_replace('{var.paging_item_class}', $this->config['paging_item_class'], $num_tag);
		 $buffer .= $num_tag;
	 }

	 return $buffer;

 } // End func number_tag

/**
 * Add page number to URL
 * If {var.offset} specified in url, it will be replaced to number
 * i.e. http://example.com/news/{var.offset}/something-else/here
 * will replace the {var.offset} to page number
 *
 * Else the number will be added to end of the url
 *
 * @access protected
 * @param string $url
 * @param int $number
 * @return string
 */
protected function num_to_url($url, $number) {

    if(strpos($url, $this->config['offset_var']) !== false) {
        $url = str_replace($this->config['offset_var'], $number, $url);
    } else {
        $url .= '/' . $number;
    }

    return $url;
}


/* End of class pagination_lib */
}

/* End of file */
?>