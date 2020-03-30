<?php

namespace VuBibTest\Integration;

class BasicTest extends \VuBib\Test\MinkTestCase
{
    /**
     * Test that the home page is available.
     *
     * @return void
     */
    public function testHomePage()
    {
        $session = $this->getMinkSession();
        $session->visit($this->getUrl('/'));
        $page = $session->getPage();
        $this->assertTrue(false !== strstr($page->getContent(), 'User Name'));
    }

    /**
     * Test that the home page is available.
     *
     * @return void
     */
    public function testLogin()
    {
        $page = $this->login('asnagy', 'asnagy');
        $this->assertTrue(
            false !== strstr($page->getContent(), 'This is the home page')
        );
    }

    /**
     * Test that the home page is available.
     *
     * @return void
     */
    public function testCredentials()
    {
        $page = $this->login('asnagy', 'asnagy');
        $basicMenu = $page->findAll('css', 'li.dropdown');

        $page = $this->login('dkatz', 'pr1test');
        $adminMenu = $page->findAll('css', 'li.dropdown');

        $this->assertTrue(count($adminMenu) > count($basicMenu));
    }
}
