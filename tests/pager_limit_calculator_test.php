<?php
define('APPROOT', realpath(dirname(__FILE__).'/../').'/');

include APPROOT.'lib/pagercalculator.class.php';

require_once 'PHPUnit.php';

class PagerCalculatorTest extends PHPUnit_TestCase {
	public function test_max_page() {
		$mp = PagerCalculator::calculate_maxpage(17, 18);
		$this->assertEquals(0, $mp);

		$mp = PagerCalculator::calculate_maxpage(18, 18);
		$this->assertEquals(0, $mp);

		$mp = PagerCalculator::calculate_maxpage(19, 18);
		$this->assertEquals(1, $mp);
	}

	public function test_offset_and_limit() {
		$pc = new PagerCalculator(18, 18);
		$page = $pc->calculate(null);
		$this->assertEquals(0, $pc->get_offset());
		$this->assertEquals(18, $pc->get_limit());
		$this->assertEquals(0, $page);


		$pc = new PagerCalculator(36, 18);
		$page = $pc->calculate(0);
		$this->assertEquals(18, $pc->get_offset());
		$this->assertEquals(18, $pc->get_limit());
		$this->assertEquals(0, $page);


		$pc = new PagerCalculator(40, 18);
		$page = $pc->calculate(null);
		$this->assertEquals(0, $pc->get_offset());
		$this->assertEquals(4, $pc->get_limit());
		$this->assertEquals(2, $page);
	}

	public function test_item_count_on_last_page() {
		$lpc = PagerCalculator::calculate_item_count_on_last_page(40, 18);
		$this->assertEquals(4, $lpc);

		$lpc = PagerCalculator::calculate_item_count_on_last_page(36, 18);
		$this->assertEquals(0, $lpc);

		$lpc = PagerCalculator::calculate_item_count_on_last_page(6, 18);
		$this->assertEquals(6, $lpc);
	}
}

$runner = new PHPUnit;
$suite = new PHPUnit_TestSuite('PagerCalculatorTest');
$result = $runner->run($suite);
print $result->toHTML();
