<?php
/**
 * Manage Publisher Action
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
namespace VuBib\Action\Publisher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Router;
use Mezzio\Template;
use Laminas\Paginator\Paginator;

/**
 * Class Definition for ManagePublisherAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ManagePublisherAction implements MiddlewareInterface
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
     * Laminas\Db\Adapter\Adapter
     *
     * @var $adapter
     */
    protected $adapter;

    /**
     * String
     *
     * @var $pub_id
     */
    protected $pub_id;

    //private $dbh;
    //private $qstmt;

    /**
     * ManagePublisherAction constructor.
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
        $this->pub_id = "";
    }

    /**
     * Publisher > Search.
     *
     * @param Array $params url query parameters
     *
     * @return Array
     */
    protected function searchPublisher($params)
    {
        // search by name
        if (!empty($params['name'])) {
            $table = new \VuBib\Db\Table\Publisher($this->adapter);

            return $table->findRecords($params['name']);
        }
        // search by location
        if (!empty($params['location'])) {
            $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);

            return $table->findRecords($params['location']);
        }
        // search by letter
        if (!empty($params['letter'])) {
            $table = new \VuBib\Db\Table\Publisher($this->adapter);

            return $table->displayRecordsByName($params['letter']);
        }
    }

    /**
     * Delete publisher.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doDelete($post)
    {
        $locs = [];
        if (null !== $post['pub_id']) {
            foreach ($post['pub_id'] as $pubId) {
                $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
                $table->deleteRecordByPub($pubId);

                $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
                $table->deletePublisherRecord($pubId, $locs);

                $table = new \VuBib\Db\Table\Publisher($this->adapter);
                $table->deleteRecord($pubId);
            }
        }
    }

    /**
     * Merge publishers.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doMerge($post)
    {
        if (isset($post['dest_loc'])) {
            $dest_loc_id = array_search('merge', $post['dest_loc']);
        }

        foreach ($post['src_loc'] as $source_locid => $action) {
            if ($action == 'move') {
                //update workpub set pubid=destpubid
                //where pubid=srcpubid and locid = $source_locid
                $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
                $table->movePublisher(
                    $post['mrg_src_id'],
                    $post['mrg_dest_id'], $source_locid
                );
                //update publoc set pubid = destpubid
                //where pubid=srcpubid and id=$source_locid
                $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
                $table->movePublisher(
                    $post['mrg_src_id'],
                    $post['mrg_dest_id'], $source_locid
                );
            } elseif ($action == 'merge') {
                //update workpub set pubid=destpubid and locid=mrgpublocid
                //where pubid=srcpubid and locid=$source_locid
                $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
                $table->mergePublisher(
                    $post['mrg_src_id'],
                    $post['mrg_dest_id'], $source_locid, $dest_loc_id
                ); //this
                //delete $source_locid from publoc
                $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
                $table->mergePublisher($post['mrg_src_id'], $source_locid);
            }
        }

        //Delete source publisher
        $table = new \VuBib\Db\Table\Publisher($this->adapter);
        $table->deleteRecord($post['mrg_src_id']);
    }

    /**
     * Add publisher.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doNew($post)
    {
        $table = new \VuBib\Db\Table\Publisher($this->adapter);
        return $table->insertPublisherAndReturnId($post['name_publisher']);
        //$table->insertRecords($post['name_publisher']);
    }

    /**
     * Edit publisher.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doEdit($post)
    {
        if (null !== $post['id']) {
            $table = new \VuBib\Db\Table\Publisher($this->adapter);
            $table->updateRecord($_POST['id'], $_POST['publisher_newname']);
        }
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
        //add a new publisher
        if ($post['action'] == 'new') {
            if ($post['submitt'] == 'Save') {
                return $this->doNew($post);
            }
        }
        //edit a publisher
        if ($post['action'] == 'edit') {
            if ($post['submitt'] == 'Save') {
                $this->doEdit($post);
            }
        }
        //delete a publisher */
        if ($post['action'] == 'delete') {
            if ($post['submitt'] == 'Delete') {
                $this->doDelete($post);
            }
        }
        //merge publishers
        if ($post['action'] == 'merge_publisher') {
            if ($post['submitt'] == 'Save') {
                $this->doMerge($post);
            }
        }
    }

    /**
     * Get records to display.
     *
     * @param Array $params url query parameters
     * @param Array $post   contains posted elements of form
     *
     * @return Paginator                  $paginator
     */
    protected function getPaginator($params, $post)
    {
        $newpud_id = "";
        //search
        if (!empty($params)) {
            if (!empty($params['name']) || !empty($params['location'])
                || !empty($params['letter'])
            ) {
                return $this->searchPublisher($params);
            }
        }

        //edit, delete actions on publisher
        if (!empty($post['action'])) {
            if ($post['action'] == 'new') {
                $this->pub_id = $this->doAction($post);
            } else {
                //add edit delete merge publisher
                $this->doAction($post);
            }

            //Cancel edit\delete
            if ($post['submitt'] == 'Cancel') {
                $table = new \VuBib\Db\Table\Publisher($this->adapter);

                return new Paginator(
                    new \Laminas\Paginator\Adapter\DbTableGateway($table)
                );
            }
        }

        // default: blank/missing search
        $table = new \VuBib\Db\Table\Publisher($this->adapter);

        return new Paginator(new \Laminas\Paginator\Adapter\DbTableGateway($table));
    }

    /**
     * Set search parameters for pagination.
     *
     * @param Array $query url query parameters
     *
     * @return Array                  $searchParams
     */
    protected function getSearchParams($query)
    {
        $searchParams = [];
        if (!empty($query['name'])) {
            $searchParams[] = 'name=' . urlencode($query['name']);
        }
        if (!empty($query['location'])) {
            $searchParams[] = 'location=' . urlencode($query['location']);
        }
        if (!empty($query['letter'])) {
            $searchParams[] = 'letter=' . urlencode($query['letter']);
        }
        return $searchParams;
    }

    /**
     * Fetches distinct initial letters of publishers.
     *
     * @return empty
     */
    protected function getLetters()
    {
        $table = new \VuBib\Db\Table\Publisher($this->adapter);
        $characs = $table->findInitialLetter();
        return $characs;
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface  $request  server-side request.
     * @param RequestHandlerInterface $handler  CallBack Handler.
     *
     * @return HtmlResponse
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $characs = $this->getLetters();

        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::publisher/manage', $this->router,
            $this->template, $this->adapter
        );
        list($query, $post) = $simpleAction->getQueryAndPost($request);

        $paginator = $this->getPaginator($query, $post);
        $paginator->setDefaultItemCountPerPage(15);

        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::publisher/manage', $this->router,
            $this->template, $this->adapter
        );
        $pgs = $simpleAction->getNextPrevious($paginator, $query);

        $searchParams = $this->getSearchParams($query);

        if (null !== $searchParams) {
            $searchParams = implode('&', $searchParams);
        } else {
            $searchParams = '';
        }

        if (isset($post['action']) && (($post['action'] == 'merge_publisher')
            || ($post['action'] == 'new'))
        ) {
            $searchParams = ($post['action'] == 'merge_publisher') ?
                             $post['mrg_dest_id'] : $this->pub_id;

            // get publisher locations
            $table = new \VuBib\Db\Table\PublisherLocation($this->adapter);
            $paginator = $table->findPublisherLocations($searchParams);
            //$paginator = $table->findPublisherLocations($post['mrg_dest_id']);

            return new HtmlResponse(
                $this->template->render(
                    'vubib::publisher/manage_location',
                    [
                    'rows' => $paginator,
                    'previous' => $pgs['prev'],
                    'next' => $pgs['nxt'],
                    'countp' => $pgs['cp'],
                    'searchParams' => $searchParams,
                    'request' => $request,
                    'adapter' => $this->adapter,
                    ]
                )
            );
        } else {
            return new HtmlResponse(
                $this->template->render(
                    'vubib::publisher/manage',
                    [
                    'rows' => $paginator,
                    'previous' => $pgs['prev'],
                    'next' => $pgs['nxt'],
                    'countp' => $pgs['cp'],
                    'searchParams' => $searchParams,
                    'carat' => $characs,
                    ]
                )
            );
        }
    }
}
