<?php

namespace VuBib\Test;

use Behat\Mink\Element\Element;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use DMore\ChromeDriver\ChromeDriver;


class MinkTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Mink session
     *
     * @var Session
     */
    protected $session;

    /**
     * Get base URL of running VuFind instance.
     *
     * @param string $path Relative path to add to base URL.
     *
     * @return string
     */
    protected function getUrl($path = '')
    {
        $base = 'http://zeusnew.ops.villanova.edu/panta_rhei_demo';
        return $base . $path;
    }

    /**
     * Get the Mink driver, initializing it if necessary.
     *
     * @return Selenium2Driver
     */
    protected function getMinkDriver()
    {
        return new Selenium2Driver('chrome');
    }

    /**
     * Get a Mink session.
     *
     * @return Session
     */
    protected function getMinkSession()
    {
        if (empty($this->session)) {
            // google-chrome --disable-gpu --headless --remote-debugging-port=9222 --remote-debugging-address=0.0.0.0
            $this->session = new Session(new ChromeDriver('http://localhost:9223', null, 'data:;'));
            $this->session->start();
        }
        return $this->session;
    }

    /**
     * Shut down the Mink session.
     *
     * @return void
     */
    protected function stopMinkSession()
    {
        if (!empty($this->session)) {
            $this->session->stop();
            $this->session = null;
        }
    }

    /**
     * Test an element for visibility.
     *
     * @param Element $element Element to test
     *
     * @return bool
     */
    protected function checkVisibility(Element $element)
    {
        return $element->isVisible();
    }

    /**
     * Wait for an element to exist, then retrieve it.
     *
     * @param Element $page     Page element
     * @param string  $selector CSS selector
     * @param int     $timeout  Wait timeout (in ms)
     *
     * @return mixed
     */
    protected function findCss(Element $page, $selector, $timeout = 1000)
    {
        $session = $this->getMinkSession();
        $session->wait($timeout, "$('$selector').length > 0");
        $result = $page->find('css', $selector);
        $this->assertTrue(is_object($result), 'findCss - Element not found: ' . $selector);
        return $result;
    }

    /**
     * Click on a CSS element.
     *
     * @param Element $page     Page element
     * @param string  $selector CSS selector
     * @param int     $timeout  Wait timeout (in ms)
     *
     * @return mixed
     */
    protected function clickCss(Element $page, $selector, $timeout = 1000)
    {
        $result = $this->findCss($page, $selector, $timeout);
        for ($tries = 0; $tries < 3; $tries++) {
            try {
                $result->click();
                return $result;
            } catch (\Exception $e) {
                // Expected click didn't work... snooze and retry
                $this->snooze();
            }
        }
        throw $e ?? new \Exception('Unexpected state reached.');
    }

    /**
     * Set a value within an element selected via CSS; retry if set fails
     * due to browser bugs.
     *
     * @param Element $page     Page element
     * @param string  $selector CSS selector
     * @param string  $value    Value to set
     * @param int     $timeout  Wait timeout for CSS selection (in ms)
     * @param int     $retries  Retry count for set loop
     *
     * @return Element
     */
    protected function findCssAndSetValue(Element $page, $selector, $value,
        $timeout = 1000, $retries = 6
    ) {
        $field = $this->findCss($page, $selector, $timeout);

        if (
            $field->getTagName() === 'select' ||
            $field->getTagName() === 'option'
        ) {
            $field->selectOption($value);
            return $field;
        }

        // Workaround for Chromedriver bug; sometimes setting a value
        // doesn't work on the first try.
        for ($i = 0; $i < $retries; $i++) {
            $field->setValue($value);
            // Did it work? If so, we're done and can leave....
            if ($field->getValue() === $value) {
                return $field;
            }
        }

        throw new \Exception('Failed to set value after ' . $retries . ' attempts.');
    }

    /**
     * Retrieve a link and assert that it exists before returning it.
     *
     * @param Element $page Page element
     * @param string  $text Link text to match
     *
     * @return mixed
     */
    protected function findAndAssertLink(Element $page, $text)
    {
        $link = $page->findLink($text);
        $this->assertTrue(is_object($link), 'link "' . $text . '" not found.');
        return $link;
    }

    /**
     * Check whether an element containing the specified text exists.
     *
     * @param Element $page     Page element
     * @param string  $selector CSS selector
     * @param string  $expected Expected text
     *
     * @return bool
     */
    protected function assertElementText(Element $page, $selector, $expected)
    {
        $matched = false;
        $text = null;
        foreach ($page->findAll('css', $selector) as $current) {
            switch ($current->getTagName()) {
                case 'input':
                case 'option':
                case 'select':
                case 'textarea':
                    $text = $current->getValue();
                    break;
                default:
                    $text = $current->getText();
            }
            if ($text === $expected) {
                $matched = true;
                break;
            }
        }
        $this->assertTrue(
            $matched,
            'Element text doesn\'t match: ' . ($text ?? '(empty)') . ' !== ' . $expected . ' (' . $selector . ')'
        );
        return $matched;
    }

    /**
     * Logout helper function
     */
    protected function logout(): void
    {
        $session = $this->getMinkSession();
        $session->visit($this->getUrl('/login?logout=y'));
    }

    /**
     * Login helper function
     */
    protected function login($username = 'asnagy', $pwd = 'asnagy'): Element
    {
        $this->logout();
        $session = $this->getMinkSession();
        $session->visit($this->getUrl('/'));
        $page = $session->getPage();
        $this->findCssAndSetValue($page, '[name="user_name"]', $username);
        $this->findCssAndSetValue($page, '[name="user_pwd"]', $pwd);
        $this->clickCss($page, '[name="submitt"]');
        return $page;
    }

    /**
     * Navigate to url
     */
    protected function goto($path, $session = null): Element
    {
        if ($session === null) {
            $session = $this->getMinkSession();
        }
        $session->visit($this->getUrl($path));
        return $session->getPage();
    }

    /**
     * Detect 500 errors
     */
    protected function checkFor500Error(): void
    {
        $this->assertEquals(200, $this->getMinkSession()->getDriver()->getStatusCode());
    }

    /**
     * Standard teardown method.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->stopMinkSession();
    }

    /**
     * Standard tear-down.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        // No teardown actions at this time.
    }
}
