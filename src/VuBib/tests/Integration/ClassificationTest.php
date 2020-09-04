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
        $session = $this->getMinkSession();
        $page = $this->goto(
            '/Classification/manage?id=293&fl=Punique&action=get_children',
            $session
        );
        $firstName = $this->findCss($page, '.folder_name')->getText();
        $testName = '_TestTestTest';

        $this->findAndAssertLink($page, 'Edit')->click();
        $this->assertElementText($page, '#edittextfr', $firstName);
        $this->findCssAndSetValue($page, '#edittextfr', $testName);

        $editedUrl = $session->getCurrentUrl();
        $this->clickCss($page, '[name="submit"][value="Save"]');
        $this->checkFor500Error();

        $this->findAndAssertLink($page, $testName);

        $session->visit($editedUrl);
        $page = $session->getPage();
        $this->assertElementText($page, '#edittextfr', $testName);
        $this->findCssAndSetValue($page, '#edittextfr', $firstName);

        $this->clickCss($page, '[name="submit"][value="Save"]');
    }

    public function testAddClassification(): string
    {
        $this->login('dkatz', 'pr1test');

        // Make new root folder
        $page = $this->goto('/Classification/manage');
        $this->findAndAssertLink($page, 'Add Branch')->click();
        $this->findCssAndSetValue($page, '#newclassif_frenchtitle', '__DELETEME__');
        $this->clickCss($page, '[name="submit"][value="Save"]');
        $this->checkFor500Error();
        $this->findAndAssertLink($page, 'Biographie');
        $this->findAndAssertLink($page, '__DELETEME__');

        // Make new child folder (fill in all the things)
        // $this->findAndAssertLink($page, 'Biographie')->click();
        $this->findAndAssertLink($page, 'Add Branch')->click();
        $this->findCssAndSetValue($page, '#newclassif_sortorder', '999');
        $this->findCssAndSetValue($page, '#newclassif_engtitle', '__DELETEME__');
        $this->findCssAndSetValue($page, '#newclassif_frenchtitle', '__DELETEME__');
        $this->findCssAndSetValue($page, '#newclassif_germantitle', '__DELETEME__');
        $this->findCssAndSetValue($page, '#newclassif_dutchtitle', '__DELETEME__');
        $this->findCssAndSetValue($page, '#newclassif_spanishtitle', '__DELETEME__');
        $this->findCssAndSetValue($page, '#newclassif_italiantitle', '__DELETEME__');
        $this->clickCss($page, '[name="submit"][value="Save"]');
        $this->checkFor500Error();
        $link = $this->findAndAssertLink($page, '__DELETEME__');

        preg_match('/id=([^&]+)&/', $link->getAttribute('href'), $matches);
        return $matches[1];
    }

    /**
     * @depends testAddClassification
     */
    public function testDisabledMoveOption($id)
    {
        $this->login('dkatz', 'pr1test');

        // Go to move
        $page = $this->goto('/Classification/move?id=' . $id . '&action=move');
        $this->findCss($page, 'option[disabled]');
    }

    /**
     * @depends testAddClassification
     */
    public function testDeleteClassification($id): void
    {
        $this->login('dkatz', 'pr1test');

        // Count __DELETEME__s
        $page = $this->goto('/Classification/manage');
        $oldItemCount = count($page->findAll('named', ['content', '__DELETEME__']));

        // Test confirmation menu
        $page = $this->goto('/Classification/edit?id=' . $id . '&action=edit');
        $this->checkHidden($page, '[name="submit"][value="Delete"]');

        $this->clickCss($page, '#delete-confirm');
        $this->findCss($page, '[name="submit"][value="Delete"]');

        $this->clickCss($page, '.delete-cancel');
        $this->checkHidden($page, '[name="submit"][value="Delete"]');

        // Test delete
        $this->clickCss($page, '#delete-confirm');
        $this->clickCss($page, '[name="submit"][value="Delete"]');
        $this->checkFor500Error();

        $newItemCount = count($page->findAll('named', ['content', '__DELETEME__']));
        $this->assertTrue($newItemCount < $oldItemCount, '__DELETEME__ not deleted');

        // Delete all __DELETEME__
        $link = $page->findLink('__DELETEME__');
        while (is_object($link)) {
            preg_match('/id=([^&]+)&/', $link->getAttribute('href'), $matches);
            $page = $this->goto(
                '/Classification/edit?id=' . $matches[1] . '&action=edit'
            );
            $this->clickCss($page, '#delete-confirm');
            $this->clickCss($page, '[name="submit"][value="Delete"]');
            $link = $page->findLink('__DELETEME__');
        }
    }
}
