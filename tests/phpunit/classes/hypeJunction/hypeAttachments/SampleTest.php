<?php

namespace hypeJunction\hypeAttachments;

class SampleTest extends \PHPUnit_Framework_TestCase {

	function testCanTest() {
		$this->assertInstanceOf(\Elgg\Di\ServiceProvider::class, _elgg_services());
	}

}
