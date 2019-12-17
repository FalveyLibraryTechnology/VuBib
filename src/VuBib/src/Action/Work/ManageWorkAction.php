<?php
/**
 * Manage Work Action
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
namespace VuBib\Action\Work;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Mysqli\Connection;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Paginator\Paginator;

/**
 * Class Definition for ManageWorkAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class ManageWorkAction implements MiddlewareInterface
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

    /**
     * ManageWorkAction constructor.
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
     * Fetches distinct initial letters of each work.
     *
     * @param Array  $params url query parameters
     * @param String $order  Order by string
     *
     * @return Array
     */
    protected function workLetters($params, $order)
    {
        if (isset($params['action'])) {
            //review records
            if ($params['action'] == 'review') {
                $table = new \VuBib\Db\Table\Work($this->adapter);

                return $table->displayReviewRecordsByLetter(
                    $params['letter'], $order
                );
            } elseif ($params['action'] == 'classify') {
                //classify records
                $table = new \VuBib\Db\Table\Work($this->adapter);

                return $table->displayClassifyRecordsByLetter(
                    $params['letter'], $order
                );
            } else {
                $table = new \VuBib\Db\Table\Work($this->adapter);

                return $table->displayRecordsByName($params['letter'], $order);
            }
        }
    }

    /**
     * Work > Search.
     *
     * @param Array $params url query parameters
     *
     * @return paginator $paginator
     */
    protected function findWork($params)
    {
        $table = new \VuBib\Db\Table\Work($this->adapter);
        $paginator = $table->findRecords($params['find_worktitle']);

        return $paginator;
    }

    /**
     * Fetch works to be reviewed and classified.
     *
     * @param Array  $params url query parameters
     * @param String $order  Order by string
     *
     * @return paginator $paginator
     */
    protected function workReviewClassify($params, $order)
    {
        //Display works which need review
        if ($params['action'] == 'review') {
            $table = new \VuBib\Db\Table\Work($this->adapter);
            $paginator = $table->fetchReviewRecords($order);

            return $paginator;
        }
        //Display works which are to be classified under folders
        if ($params['action'] == 'classify') {
            $table = new \VuBib\Db\Table\Work($this->adapter);
            $paginator = $table->fetchClassifyRecords($order);

            return $paginator;
        }
    }

    /**
     * Adds new work.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAdd($post)
    {
        if (isset($post['submit_save'])) {
            if ($post['submit_save'] == 'Save') {
                if (!empty($post['pr_work_lookup_id'])) {
                    $pr_workid = $post['pr_work_lookup_id'];
                } else {
                    $pr_workid = -1;
                }
                //insert General(work)
                $table = new \VuBib\Db\Table\Work($this->adapter);
                $wk_id = $table->insertRecords(
                    $pr_workid, $post['work_type'], $post['new_worktitle'],
                    $post['new_worksubtitle'], $post['new_workparalleltitle'],
                    $post['description'], date('Y-m-d H:i:s'),
                    $post['user'], $post['select_workstatus'],
                    $post['pub_yrFrom']
                );

                //extract classification rows
                foreach ($post['arr'] as $row) {
                    $fl[] = explode(',', trim($row, ','));
                }

                //extract folder ids for each row
                for ($i = 0; $i < count($fl); ++$i) {
                    $folder[$i] = $fl[$i][count($fl[$i]) - 1];
                }

                //insert classification(work_folder)
                if ($folder[0] != null) {
                    $table = new \VuBib\Db\Table\Work_Folder($this->adapter);
                    $table->insertWorkFolderRecords($wk_id, $folder);
                }

                //insert Publisher(work_publisher)
                if ($post['pub_id'][0] != null) {
                    $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
                    $table->insertRecords(
                        $wk_id, $post['pub_id'],
                        $post['pub_location'], $post['pub_yrFrom'],
                        $post['pub_yrTo']
                    );
                    //$table->insertRecords($wk_id,$post['pub_id'],$post['publoc_id'],$post['pub_yrFrom'],$post['pub_yrTo']);
                }

                //insert Agent(work_agent)
                if ($post['agent_id'][0] != null) {
                    $table = new \VuBib\Db\Table\WorkAgent($this->adapter);
                    $table->insertRecords(
                        $wk_id,
                        $post['agent_id'], $post['agent_type']
                    );
                }

                //map work to citation(work_workattribute)
                $workWorkAttr_id = [];
                foreach ($post as $key => $value) {
                    if ((preg_match("/^[a-z]+\,\d+[a-z]+\,\d+$/", $key))
                        && ($value != null)
                    ) {
                        $keys = preg_split("/[a-z]+\,/", $key);
                        $workWorkAttr_id[] = $keys[1];
                        $workWorkAttr_value[] = $keys[2];
                    }
                    if ((preg_match("/^[a-z]+\,\d+$/", $key)) && ($value != null)) {
                        $workWorkAttr_id[] = preg_replace("/^[a-z]+\,/", '', $key)
                           . '<br />';
                        $workWorkAttr_value[] = $value;
                    }
                }
                if (count($workWorkAttr_id) > 0) {
                    $table = new \VuBib\Db\Table\Work_WorkAttribute($this->adapter);
                    $table->insertRecords($wk_id, $workWorkAttr_id, $workWorkAttr_value);
                }
            }
        }
    }

    /**
     * Edits work.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doEdit($post)
    {
        if (isset($post['submit_save'])) {
            if ($post['submit_save'] == 'Save') {
                //echo "<pre>"; print_r($post); echo "</pre>"; die();
                if (!empty($post['pr_work_lookup_id'])) {
                    $pr_workid = $post['pr_work_lookup_id'];
                } else {
                    $pr_workid = -1;
                }
                // Safety against errors
                $connection = new Connection();
                //update General(work)
                $connection->beginTransaction();
                $table = new \VuBib\Db\Table\Work($this->adapter);
                $table->updateRecords(
                    $pr_workid, $post['id'], $post['work_type'],
                    $post['work_title'], $post['work_subtitle'],
                    $post['work_paralleltitle'], $post['description'],
                    date('Y-m-d H:i:s'), $post['user'],
                    $post['work_status'], $post['pub_yrFrom']
                );
                $connection->commit();

                // update classifications
                //extract classification rows
                $folder = [];
                if (isset($post['classification_row'])) {
                    foreach ($post['classification_row'] as $row) {
                        $fl[] = explode(',', trim($row, ','));
                    }
                    //extract folder ids for each row
                    for ($i = 0; $i < count($fl); ++$i) {
                        $folder[$i] = $fl[$i][count($fl[$i]) - 1];
                    }

                }
                $connection->beginTransaction();
                //delete all workfolders
                $table = new \VuBib\Db\Table\Work_Folder($this->adapter);
                $table->deleteRecordByWorkId($post['id']);
                //update classification(work_folder)
                if ($folder[0] != null) {
                    //insert all workfolders again
                    $table = new \VuBib\Db\Table\Work_Folder($this->adapter);
                    $table->insertWorkFolderRecords($post['id'], $folder);
                }
                $connection->commit();

                //update Publisher(work_publisher)
                $connection->beginTransaction();
                //delete all publishers
                $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
                $table->deleteRecordByWorkId($post['id']);
                //insert all publishers again
                if (isset($post['pub_id'])/* && $post['pub_id'][0] != null*/) {
                    $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
                    $table->insertRecords(
                        $post['id'], $post['pub_id'],
                        $post['pub_location'], $post['pub_yrFrom'],
                        $post['pub_yrTo']
                    );
                    //$table->insertRecords($post['id'], $post['pub_id'],
                    //$post['publoc_id'],$post['pub_yrFrom'],$post['pub_yrTo']);
                }
                $connection->commit();

                //update Agent(work_agent)
                $connection->beginTransaction();
                //delete all agents
                $table = new \VuBib\Db\Table\WorkAgent($this->adapter);
                $table->deleteRecordByWorkId($post['id']);
                //insert Agents again
                if (isset($post['agent_id']) && $post['agent_id'][0] != null) {
                    $table = new \VuBib\Db\Table\WorkAgent($this->adapter);
                    $table->insertRecords($post['id'], $post['agent_id'], $post['agent_type']); //this
                }
                $connection->commit();

                // update workattributes
                //map work to citation(work_workattribute)
                $workWorkAttr_id = [];
                foreach ($post as $key => $value) {
                    if ((preg_match("/^[a-z]+\,\d+([a-z]+\,\d+)+$/", $key))
                        && ($value != null)
                    ) {
                        $keys = preg_split("/[a-z]+\,/", $key);
                        $workWorkAttr_id[] = $keys[1];
                        if (count($keys) == 4) {
                            $workWorkAttr_value[] = $keys[3];
                        } else {
                            $workWorkAttr_value[] = $keys[2];
                        }
                    }
                    if ((preg_match("/^[a-z]+\,\d+$/", $key)) && ($value != null)) {
                        $workWorkAttr_id[] = preg_replace("/^[a-z]+\,/", '', $key);
                        $workWorkAttr_value[] = $value;
                    }
                }

                $connection->beginTransaction();
                //delete workattribute records
                $table = new \VuBib\Db\Table\Work_WorkAttribute($this->adapter);
                $table->deleteRecordByWorkId($post['id']);
                //insert workattributes again
                if (!empty($workWorkAttr_id) && $workWorkAttr_id[0] != null) {
                    $table = new \VuBib\Db\Table\Work_WorkAttribute($this->adapter);
                    $table->insertRecords(
                        $post['id'],
                        $workWorkAttr_id,
                        $workWorkAttr_value
                    );
                }
                $connection->commit();
            }
        }
    }

    /**
     * Deletes work.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doDelete($post)
    {
        if (isset($post['submitt'])) {
            if ($post['submitt'] == 'Delete') {
                if (null !== $post['work_id']) {
                    foreach ($post['work_id'] as $workId) {
                        $table = new \VuBib\Db\Table\WorkAgent($this->adapter);
                        $table->deleteRecordByWorkId($workId);
                        $table = new \VuBib\Db\Table\Work_Folder($this->adapter);
                        $table->deleteRecordByWorkId($workId);
                        $table = new \VuBib\Db\Table\WorkPublisher($this->adapter);
                        $table->deleteRecordByWorkId($workId);
                        $table = new \VuBib\Db\Table\Work_WorkAttribute(
                            $this->adapter
                        );
                        $table->deleteRecordByWorkId($workId);
                        $table = new \VuBib\Db\Table\Work($this->adapter);
                        $table->deleteRecordByWorkId($workId);
                    }
                }
            }
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
        //add a new work type
        if ($post['action'] == 'work_new') {
            $this->doAdd($post);
        }
        if ($post['action'] == 'work_edit') {
            $this->doEdit($post);
        }
        if ($post['action'] == 'delete') {
            $this->doDelete($post);
        }
    }

    /**
     * Get records to display.
     *
     * @param Array $params url query parameters
     * @param Array $post   contains posted elements of form
     *
     * @return Paginator $paginator
     */
    protected function getPaginator($params, $post)
    {
        $order = "";
        //order by columns
        if (!empty($params['orderBy'])) {
            /*$isAsc = isset($params['sort_ord'])? (bool) $params['sort_ord']: 1;
            echo "iasAsc is " . $isAsc;
            //$isAsc = isset($params['sort_ord'])? "ASC":"DESC";
            if ($isAsc) {
                $sort_ord = "ASC";
            } else {
                $sort_ord = "DESC";
            }*/
            $sort_ord = $params['sort_ord'];
            $ord_by = $params['orderBy'];

            if ($ord_by == "type") {
                $ord_by = "type_id";
            } elseif ($ord_by == "created") {
                $ord_by = "create_date";
            } elseif ($ord_by == "modified") {
                $ord_by = "modify_date";
            }

            $order = $ord_by . " " . $sort_ord;
        }
        // search by letter
        if (!empty($params['letter'])) {
            return $this->workLetters($params, $order);
        }
        // Work Lookup
        if (!empty($params['find_worktitle'])) {
            return $this->findWork($params);
        }
        if (!empty($params['action'])) {
            return $this->workReviewClassify($params, $order);
        }
        if (!empty($post['action'])) {
            //add edit delete work
            $this->doAction($post);
        }

        //Cancel edit\delete
        if (isset($post['submit_cancel'])) {
            if ($post['submit_cancel'] == 'Cancel') {
                $table = new \VuBib\Db\Table\Work($this->adapter);

                return new Paginator(
                    new \Zend\Paginator\Adapter\DbTableGateway($table)
                );
            }
        }

        //order by columns
        if (isset($order) && $order !== '') {
            $table = new \VuBib\Db\Table\Work($this->adapter);
            return new Paginator(
                new \Zend\Paginator\Adapter\DbTableGateway(
                    $table, null, $order, null, null
                )
            );
        }
        // default: blank/missing search
        $table = new \VuBib\Db\Table\Work($this->adapter);
        return new Paginator(new \Zend\Paginator\Adapter\DbTableGateway($table));
    }

    /**
     * Set search parameters for pagination.
     *
     * @param Array $query url query parameters
     *
     * @return Paginator                  $paginator
     */
    protected function getSearchParams($query)
    {
        $searchParams = [];
        $ord = "";
        if (isset($query['orderBy'])) {
            $ord = 'orderBy=' . urlencode($query['orderBy']) .
                   '&sort_ord=' . urlencode($query['sort_ord']);
        }
        if (!empty($query['find_worktitle'])) {
            $searchParams[] = 'find_worktitle=' .
                 urlencode($query['find_worktitle']);
        }
        if (!empty($query['letter']) && $query['action'] == 'alphasearch') {
            $searchParams[] = 'letter=' . urlencode($query['letter']) .
                '&action=' . urlencode($query['action']) . '&' . $ord;
        }
        if (isset($query['action'])) {
            if ($query['action'] == 'review') {
                if (!empty($query['letter'])) {
                    $searchParams[] = 'action=' . urlencode($query['action']) .
                       '&letter=' . urlencode($query['letter']) . '&' . $ord;
                } else {
                    $searchParams[] = 'action=' . urlencode($query['action']) .
                      '&' . $ord;
                }
            }
            if ($query['action'] == 'classify') {
                if (!empty($query['letter'])) {
                    $searchParams[] = 'action=' . urlencode($query['action']) .
                       '&letter=' . urlencode($query['letter']) . '&' . $ord;
                } else {
                    $searchParams[] = 'action=' . urlencode($query['action'])
                        . '&' . $ord;
                }
            }
        }
        if (isset($query['orderBy']) && !isset($query['action'])) {
            $searchParams[] = $ord;
            //'orderBy=' . urlencode($query['orderBy'])
            //  . '&sort_ord=' . urlencode($query['sort_ord']);
        }
        return $searchParams;
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface $request  server-side request.
     * @param ResponseInterface      $response response to client side.
     * @param callable               $next     CallBack Handler.
     *
     * @return HtmlResponse
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::work/manage', $this->router,
            $this->template, $this->adapter
        );
        list($query, $post) = $simpleAction->getQueryAndPost($request);

        if (isset($query['action'])) {
            if ($query['action'] == 'review') {
                $table = new \VuBib\Db\Table\Work($this->adapter);
                $characs = $table->findInitialLetterReview();
            } elseif ($query['action'] == 'classify') {
                $table = new \VuBib\Db\Table\Work($this->adapter);
                $characs = $table->findInitialLetterClassify();
            } elseif ($query['action'] == 'alphasearch') {
                $table = new \VuBib\Db\Table\Work($this->adapter);
                $characs = $table->findInitialLetter();
            }
        } else {
            $table = new \VuBib\Db\Table\Work($this->adapter);
            $characs = $table->findInitialLetter();
        }

        $paginator = $this->getPaginator($query, $post);
        $paginator->setDefaultItemCountPerPage(15);
        //$allItems = $paginator->getTotalItemCount();

        $simpleAction = new \VuBib\Action\SimpleRenderAction(
            'vubib::work/manage', $this->router,
            $this->template, $this->adapter
        );
        $pgs = $simpleAction->getNextPrevious($paginator, $query);

        $searchParams = $this->getSearchParams($query);

        if (null !== $searchParams) {
            $searchParams = implode('&', $searchParams);
        } else {
            $searchParams = '';
        }

        if (isset($query['action'])) {
            if ($query['action'] == 'review') {
                return new HtmlResponse(
                    $this->template->render(
                        'vubib::work/review',
                        [
                        'rows' => $paginator,
                        'previous' => $pgs['prev'],
                        'next' => $pgs['nxt'],
                        'countp' => $pgs['cp'],
                        'searchParams' => $searchParams,
                        'carat' => $characs,
                        'request' => $request,
                        'adapter' => $this->adapter,
                        ]
                    )
                );
            }
            if ($query['action'] == 'classify') {
                return new HtmlResponse(
                    $this->template->render(
                        'vubib::work/classify',
                        [
                        'rows' => $paginator,
                        'previous' => $pgs['prev'],
                        'next' => $pgs['nxt'],
                        'countp' => $pgs['cp'],
                        'searchParams' => $searchParams,
                        'carat' => $characs,
                        'request' => $request,
                        'adapter' => $this->adapter,
                        ]
                    )
                );
            }
            if ($query['action'] == 'alphasearch') {
                return new HtmlResponse(
                    $this->template->render(
                        'vubib::work/manage',
                        [
                        'rows' => $paginator,
                        'previous' => $pgs['prev'],
                        'next' => $pgs['nxt'],
                        'countp' => $pgs['cp'],
                        'searchParams' => $searchParams,
                        'carat' => $characs,
                        'request' => $request,
                        'adapter' => $this->adapter,
                        ]
                    )
                );
            }
        } else {
            return new HtmlResponse(
                $this->template->render(
                    'vubib::work/manage',
                    [
                    'rows' => $paginator,
                    'previous' => $pgs['prev'],
                    'next' => $pgs['nxt'],
                    'countp' => $pgs['cp'],
                    'searchParams' => $searchParams,
                    'carat' => $characs,
                    'request' => $request,
                    'adapter' => $this->adapter,
                    ]
                )
            );
        }
    }
}
