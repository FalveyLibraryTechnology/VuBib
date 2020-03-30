<?php

namespace VuBibTest\Integration;

class GetWorkDetailsTest extends \VuBib\Test\MinkTestCase
{
    /**
     * Check autocomplete results work for publishers
     *
     * From feedback: 2020-02-24
     */
    public function testPublisherAutoComplete(): void
    {
        $session = $this->getMinkSession();
        $session->visit($this->getUrl('/Work/get_work_details?autofor=publisher&term=S.n.'));
        $page = $session->getPage();
        $this->assertTrue(false === strstr($page->getContent(), 'error: default process bottom'));
        $this->assertTrue(false !== strstr($page->getContent(), 'S.n.'));
    }
}
