<?php

namespace VuBibTest\Integration;

class ClassificationTest extends \VuBib\Test\MinkTestCase
{
    /**
     * After editing a classification, return to the right spot in the heirarchy
     *
     * From feedback: 2020-02-24
     */
    public function testClassificationEditReturn(): void
    {
        $this->login('dkatz', 'pr1test');

        // Set session
        $page = $this->goto('/Classification/manage?id=23993&fl=Test&action=get_children');

        $this->findAndAssertLink($page, 'Edit')->click();
        $this->assertElementText($page, '#edittexten', 'Test A');
        $this->findCssAndSetValue($page, '#edittexten', 'Test A!');

        $this->clickCss($page, '[name="submit"][value="Save"]');

        $this->assertElementText($page, 'a.folder_name', 'Test A');

        $page = $this->goto('/Classification/edit?id=23994&action=edit');
        $this->assertElementText($page, '#edittexten', 'Test A!');
        $this->findCssAndSetValue($page, '#edittexten', 'Test A');

        $this->clickCss($page, '[name="submit"][value="Save"]');
    }
}
