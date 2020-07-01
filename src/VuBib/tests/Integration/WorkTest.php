<?php

namespace VuBibTest\Integration;

class WorkTest extends \VuBib\Test\MinkTestCase
{
    /**
     * Fill all fields from an array
     */
    protected function fillFields($page, $map)
    {
        foreach ($map as $name => $value) {
            $this->findCssAndSetValue($page, '[name="' . $name . '"]', $value);
        }
    }

    /**
     * Check all fields from an array
     */
    protected function checkFields($page, $map)
    {
        foreach ($map as $name => $value) {
            $this->assertElementText($page, '[name="' . $name . '"]', $value);
        }
    }

    /**
     * Test AC controls
     *
     * @return void
     */
    protected function _testAC($page, $acsInputSelector, $term, $title)
    {
        $this->findCssAndSetValue($page, $acsInputSelector, $term);
        sleep(1); // wait for autocomplete

        $acItems = $page->findAll('css', '.ac-item');
        $this->assertTrue(is_array($acItems));
        foreach ($acItems as $el) {
            $this->assertFalse(strstr($el->getText(), 'undefined'));
            $this->assertFalse(strstr($el->getText(), 'no results'));
        }

        // Test selection
        $acItems[0]->click();
        $this->assertElementText(
            $page,
            str_replace('acs-input', 'acs-title', $acsInputSelector),
            $title
        );
    }

    /**
     * Test AC results
     *
     * @return void
     */
    protected function _testACResults($page, $acsInputSelector, $term, $expected)
    {
        $this->findCssAndSetValue($page, $acsInputSelector, $term);
        sleep(1); // wait for autocomplete

        $acItems = $page->findAll('css', '.ac-item');
        $this->assertTrue(is_array($acItems));
        $found = false;
        foreach ($acItems as $el) {
            if ($el->getText() === $expected) {
                $found = true;
                break;
            }
        }
        self::assertTrue($found);
    }

    /**
     * Test add remove buttons
     *
     * @return void
     */
    protected function _testAddRemove($page, $addId, $removeId, $checkName, $countClass)
    {
        // add test
        $startCount = count($page->findAll('css', $countClass));
        $this->clickCss($page, $addId);
        $this->assertEquals($startCount + 1, count($page->findAll('css', $countClass)));

        // no removal without checks (failing because of dialog)
        // $this->clickCss($page, $removeId);
        // $this->assertEquals($startCount + 1, count($page->findAll('css', $countClass)));
        // $this->getMinkSession()->getDriver()->acceptAlert();

        // check one box
        $checks = $page->findAll('css', '[name="' . $checkName . '[]"]');
        $checks[1]->click();
        $this->clickCss($page, $removeId);
        $this->assertEquals($startCount, count($page->findAll('css', $countClass)));
    }

    /**
     * Test classification autocompletes
     *
     * @return void
     */
    protected function _testClassificationControls($page)
    {
        // Switch to tab
        $this->clickCss($page, '[href="#Classification"]');

        // Item open from the start
        $this->findCss($page, '.select_source_fl');

        // Ajax test
        $this->findCssAndSetValue($page, '[data-source-col="0"] select', '1'); // Bibliography
        sleep(1); // wait for ajax
        $this->findCssAndSetValue($page, '[data-source-col="1"] select', '2'); // Milieu
        sleep(1); // wait for ajax
        $this->findCssAndSetValue($page, '[data-source-col="2"] select', '3'); // Afrique du Nord
        sleep(1); // wait for ajax
        $this->findCssAndSetValue($page, '[data-source-col="3"] select', '4'); // Bibliographie
        sleep(1); // wait for ajax

        // Make sure no new column appeared
        $this->assertNull($page->find('css', '[data-source-col="4"]'));

        $this->_testAddRemove($page, '#fl_add', '#fl_remove', 'removeFolder', '.source_fl_row');
    }

    /**
     * Test publisher autocompletes
     *
     * From feedback: 2020-02-24
     *
     * @return void
     */
    protected function _testPublisherControls($page)
    {
        // Switch to tab
        $this->clickCss($page, '[href="#Publisher"]');

        $this->_testAC($page, '#Publisher .acs-input', 'S.n.', 'S.n.');
        sleep(1); // wait for ajax
        $this->assertFalse(
            $this->findCss($page, '#Publisher .pub-locations')
            ->hasAttribute('disabled')
        );
        $this->findCssAndSetValue($page, '#Publisher .pub-locations', '3865'); // Limerick
        $this->findCssAndSetValue($page, '[name="pub_yrFrom[]"]', '1989');
        $this->findCssAndSetValue($page, '[name="pub_yrTo[]"]', '2020');

        $this->_testAddRemove($page, "#pub_add", "#pub_remove", 'removePublisher', '.pub_row');
    }

    /**
     * "van N" should return "van Neer, Joost"
     *
     * Feedback 2020-02-25
     */
    protected function _testAgentControls($page)
    {
        // Switch to tab
        $this->clickCss($page, '[href="#Agents"]');

        // Item open from the start
        $this->findCss($page, '.agent_type');

        // Selection
        $this->findCssAndSetValue($page, '.agent_type', '1'); // Author
        $this->_testAC($page, '.agent-acs .acs-input', 'LOEFFELBERGER', 'LOEFFELBERGER');
        $this->assertElementText($page, '.agent-fname', 'Michael'); // Feedback 2020-02-25
    }

    /**
     * After editing from the Review screen, return to review
     *
     * Feedback 2020-03-17
     */
    public function testReturnToRightManagementPage(): void
    {
        $this->login('dkatz', 'pr1test');

        // === Review === //

        $page = $this->goto('/Work/manage?action=review');
        // Edit first item
        $this->findAndAssertLink($page, 'Edit')->click();
        // Cancel
        $this->clickCss($page, '[name="submit_cancel"]');
        // Back at review?
        $this->assertElementText($page, '.crumbs.navbar-text', 'Home > Work > Review');

        // Delete first item
        $this->findAndAssertLink($page, 'Delete')->click();
        // Cancel
        $this->clickCss($page, '[value="Cancel"]');
        // Back at review?
        $this->assertElementText($page, '.crumbs.navbar-text', 'Home > Work > Review');

        // === Classify === //

        $page = $this->goto('/Work/manage?action=classify');
        // Edit first item
        $this->findAndAssertLink($page, 'Edit')->click();
        // Cancel
        $this->clickCss($page, '[name="submit_cancel"]');
        // Back at review?
        $this->assertElementText($page, '.crumbs.navbar-text', 'Home > Work > Classify');

        // Delete first item
        $this->findAndAssertLink($page, 'Delete')->click();
        // Cancel
        $this->clickCss($page, '[value="Cancel"]');
        // Back at review?
        $this->assertElementText($page, '.crumbs.navbar-text', 'Home > Work > Classify');
    }

    /**
     * Feedback 2020-02-25
     */
    public function testWorkTypeFields()
    {
        $this->login();
        $page = $this->goto('/Work/new');

        // TODO: Loop
        $this->clickCss($page, '[href="#General"]');
        $field = $this->findCssAndSetValue($page, '[name="work_type"]', 3); // book

        $this->clickCss($page, '[href="#Citation"]');
        // TODO: Check fields are correct
    }

    /**
     * "van N" should return "van Neer, Joost"
     * "De Anagni" should return "De Anagni, Juvenalis"
     *
     * Feedback 2020-02-25
     */
    public function testAgentLastNameAC()
    {
        $this->login();
        $page = $this->goto('/Work/new');
        $this->clickCss($page, '[href="#Agents"]');

        $this->_testACResults($page, '#Agents .acs-input', 'van N', 'van Neer, Joost');
        $this->_testACResults($page, '#Agents .acs-input', 'De Anagni', 'De Anagni, Juvenalis');
    }

    /**
     * Generate a new record
     */
    public function testMakeNewRecord(): string
    {
        $this->login();

        $page = $this->goto('/Work/new');

        // fields by tab
        $fields = [
            'General' => [
                'work_title' => 'Test Title',
                'work_subtitle' => 'Test SubTitle',
                'work_paralleltitle' => 'Test Parallel Title',
                'description' => 'Test Description',
                'work_type' => '3' //  Book
            ],
            'Publisher' => [
                'pub_id[]' => '5201', // S.n.
                'pub_yrFrom[]' => '1989',
                'pub_yrTo[]' => '2020'
            ],
            'Agents' => [
                'agent_id[]' => '13662' // LOEFFELBERGER
            ],
            'Citation' => [
                'wkatid,1' => '6010', // Theophaneia
                'wkatid,13' => '21082', // Dissertation
                'wkatid,5' => '5549' // japonais
            ],
        ];

        $this->clickCss($page, '[href="#General"]');
        $this->fillFields($page, $fields['General']);
        $this->_testAC(
            $page, '#parent-work-lookup .acs-input',
            'Flambeaux', "Flambeaux de la vie de L'Eglise"
        );
        $this->clickCss($page, '[name="work_status"][value="2"]'); // Unseen Source Doc

        $this->_testClassificationControls($page);

        $this->_testPublisherControls($page);

        $this->_testAgentControls($page);

        $this->clickCss($page, '[href="#Citation"]');
        $this->_testAC($page, '[id="Series:1"]', 'Theophaneia', 'Theophaneia');
        $this->_testAC($page, '[id="Material Designation:13"]', 'Dissertation', 'Dissertation');
        $this->_testAC($page, '[id="Language:5"]', 'japonais', 'japonais');

        $this->clickCss($page, '#submit_save');
        $this->checkFor500Error();

        // Managing table
        $page = $this->goto('/Work/manage?orderBy=created&sort_ord=DESC');
        $this->assertElementText($page, '.title a', 'Test Title');
        $link = $this->findCss($page, '.title a');
        $createdWorkId = explode('?id=', $link->getAttribute('href'))[1]; // to pass to later tests

        // Check modified date
        $date = $this->findCss($page, '#work-' . $createdWorkId . ' .created')->getText();
        $this->assertElementText($page, '#work-' . $createdWorkId . ' .modified', $date);

        // Let's go!
        $link->click();

        // Check fields
        foreach (['General', 'Publisher', 'Agents', 'Citation'] as $tab) {
            $this->clickCss($page, '[href="#' . $tab . '"]');
            $this->checkFields($page, $fields[$tab]);
        }
        /**
         * Check that status is set
         *
         * From feedback: 2020-02-24
         */
        $this->assertTrue(
            $this->findCss($page, '[name="work_status"][value="2"]')->isChecked()
        );

        return $createdWorkId;
    }

    /**
     * Generate a new record
     *
     * @depends testMakeNewRecord
     */
    public function testEditRecord($workId): void
    {
        $this->login();

        $page = $this->goto('/Work/edit?id=' . $workId);

        $this->fillFields($page, ['work_title' => 'Editted Test Title']);
        $this->clickCss($page, '[name="work_status"][value="0"]'); // Feedback: 2020-02-25

        // Feedback: 2020-03-23
        // Add a second agent
        $this->clickCss($page, '[href="#Agents"]');
        $this->clickCss($page, '#agent_add');
        $agentTypes = $page->findAll('css', '.agent_type');
        $agentTypes[1]->selectOption('1');
        $this->findCssAndSetValue($page, '.agent-acs.acs-editing .acs-input', 'Meuthen');
        sleep(1);
        $page->find('css', '.ac-item')->click();

        $this->clickCss($page, '#submit_save');
        $this->checkFor500Error();

        // Check fields
        $page = $this->goto('/Work/edit?id=' . $workId);
        $this->assertTrue(
            $this->findCss($page, '[name="work_status"][value="0"]')->isChecked()
        );

        // Feedback 2020-03-02: Agents go missing on save
        $this->clickCss($page, '[href="#Agents"]');
        $agentIds = $page->findAll('css', '[name="agent_id[]"]');
        $this->assertEquals(2, count($agentIds));
        $this->assertEquals($agentIds[0]->getValue(), '4918'); // Meuthen
        $this->assertEquals($agentIds[1]->getValue(), '13662'); // LOEFFELBERGER
    }

    /**
     * Generate a new record
     *
     * @depends testMakeNewRecord
     */
    public function testDeleteRecord($workId): void
    {
        $this->login();

        $page = $this->goto('/Work/manage?orderBy=created&sort_ord=DESC');
        $this->clickCss($page, '[name="selectWork[]"][value="' . $workId . '"]');
        $this->clickCss($page, '#delWork');
        $this->checkFor500Error();

        $this->clickCss($page, '[name="submitt"][value="Cancel"]'); // cancel
        $this->checkFor500Error();

        // Work remains
        $page = $this->goto('/Work/manage?orderBy=created&sort_ord=DESC');
        $this->findCss($page, '#work-' . $workId);
        $this->clickCss($page, '[name="selectWork[]"][value="' . $workId . '"]');
        $this->clickCss($page, '#delWork');
        $this->checkFor500Error();

        $this->checkFields($page, ['work_id[]' => $workId]);
        $this->clickCss($page, '[name="submitt"][value="Delete"]'); // confirm
        $this->checkFor500Error();

        // Work deleted
        $page = $this->goto('/Work/manage?orderBy=created&sort_ord=DESC');
        self::assertNull($page->find('css', '#work-' . $workId));
    }
}
