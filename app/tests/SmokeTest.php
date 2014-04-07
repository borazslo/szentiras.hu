<?php

class SmokeTest extends TestCase {

	/**
	 * Basic home page test.
	 *
	 * @return void
	 */
	public function testBasicHomePage()
	{
		$crawler = $this->client->request('GET', '/');

		$this->assertTrue($this->client->getResponse()->isOk());
	}

}