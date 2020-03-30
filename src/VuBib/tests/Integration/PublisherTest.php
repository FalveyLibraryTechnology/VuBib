<?php

namespace VuBibTest\Integration;

class PublisherTest extends \VuBib\Test\MinkTestCase
{
    /**
     * Test that the home page is available.
     *
     * From feedback: 2020-02-24
     */
    public function testAddLocation(): void
    {
        $session = $this->getMinkSession();
        $this->login('dkatz', 'pr1test');
        $session->visit($this->getUrl('/Publisher/manage_location?id=4612')); // A Bruccoli Clark Layman Book
        $page = $session->getPage();
        $this->clickCss($page, '.add-location-link');
        $this->checkFor500Error();
    }
}
