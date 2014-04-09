<?php

class SmokeTest extends TestCase {

	/**
	 * Basic home page test.
	 *
	 * @return void
	 */
	public function testBasicHomePage()
	{
		$this->client->request('GET', '/');
		$this->assertTrue($this->client->getResponse()->isOk());

	}

    public function testBasicTranslationPage() {
        $this->client->request('GET', '/TESTTRANS');
        $this->assertTrue($this->client->getResponse()->isOk());
    }

}