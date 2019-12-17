<?php
/**
 * Manage Language Action
 *
 * PHP version 5
 *
 * Copyright (c) Falvey Library 2017.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https:// Main Page
 */
namespace VuBib\Action\Language;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Paginator\Paginator;

/**
 * Class Definition for ManageLanguageAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ManageLanguageAction implements MiddlewareInterface
{
    /**
     * Router\RouterInterface
     *
     * @var $router
     */
    protected $router;

    /**
     * Template\TemplateRendererInterface
     *
     * @var $template
     */
    protected $template;

    /**
     * Zend\Db\Adapter\Adapter
     *
     * @var $adapter
     */
    protected $adapter;

    //private $dbh;
    //private $qstmt;

    /**
     * ManageLanguageAction constructor.
     *
     * @param Router\RouterInterface             $router   for routes
     * @param Template\TemplateRendererInterface $template for templates
     * @param Adapter                            $adapter  for db connection
     */
    public function __construct(Router\RouterInterface $router,
        Template\TemplateRendererInterface $template = null, Adapter $adapter
    ) {
        $this->router = $router;
        $this->template = $template;
        $this->adapter = $adapter;
    }

    /**
     * Action based on action parameter.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAction($post)
    {
        //add a new language term
        if ($post['action'] == 'new') {
            if ($post['submitt'] == 'Save') {
                $table = new \VuBib\Db\Table\TranslateLanguage($this->adapter);
                $table->insertRecords(
                    $_POST['de_newlang'], $_POST['en_newlang'],
                    $_POST['es_newlang'], $_POST['fr_newlang'],
                    $_POST['it_newlang'], $_POST['nl_newlang']
                );
            }
        }
        //edit a language term
        if ($post['action'] == 'edit') {
            if ($post['submitt'] == 'Save') {
                if (null !== $post['id']) {
                    $table = new \VuBib\Db\Table\TranslateLanguage($this->adapter);
                    $table->updateRecord(
                        $_POST['id'], $_POST['de_newlang'],
                        $_POST['en_newlang'], $_POST['es_newlang'],
                        $_POST['fr_newlang'], $_POST['it_newlang'],
                        $_POST['nl_newlang']
                    );
                }
            }
        }
        //delete a language term
        if ($post['action'] == 'delete') {
            if ($post['submitt'] == 'Delete') {
                if (null !== $post['id']) {
                    $table = new \VuBib\Db\Table\TranslateLanguage($this->adapter);
                    $table->deleteRecord($post['id']);
                }
            }
        }
    }

    /**
     * Get records to display.
     *
     * @param Array $post contains posted elements of form
     *
     * @return Paginator                  $paginator
     */
    protected function getPaginator($post)
    {
        //edit, delete actions on language
        if (!empty($post['action'])) {
            //add edit delete language
            $this->doAction($post);

            //Cancel edit\delete
            if ($post['submitt'] == 'Cancel') {
                $table = new \VuBib\Db\Table\TranslateLanguage($this->adapter);

                return new Paginator(
                    new \Zend\Paginator\Adapter\DbTableGateway($table)
                );
            }
        }
        // default: blank for listing in manage
        $table = new \VuBib\Db\Table\TranslateLanguage($this->adapter);

        return new Paginator(new \Zend\Paginator\Adapter\DbTableGateway($table));
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface  $request server-side request.
     * @param RequestHandlerInterface $handler request Handler.
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::language/manage', $this->router,
            $this->template, $this->adapter
        );
        list($query, $post) = $simpleAction->getQueryAndPost($request);

        $paginator = $this->getPaginator($post);
        $paginator->setDefaultItemCountPerPage(10);

        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::language/manage', $this->router,
            $this->template, $this->adapter
        );
        $pgs = $simpleAction->getNextPrevious($paginator, $query);

        //$allItems = $paginator->getTotalItemCount();

        return new HtmlResponse(
            $this->template->render(
                'vubib::language/manage',
                [
                    'rows' => $paginator,
                    'previous' => $pgs['prev'],
                    'next' => $pgs['nxt'],
                    'countp' => $pgs['cp'],
                    //'searchParams' => implode('&', $searchParams),
                ]
            )
        );
    }
}
