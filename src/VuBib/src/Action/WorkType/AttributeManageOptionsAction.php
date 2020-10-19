<?php
/**
 * Attribute Manage Options Action
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
namespace VuBib\Action\WorkType;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;

/**
 * Class Definition for AttributeManageOptionsAction.
 *
 * @category VuBib
 * @package  Code
 * @author   Falvey Library <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 *
 * @link https://
 */
class AttributeManageOptionsAction implements MiddlewareInterface
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
     * AttributeManageOptionsAction constructor.
     *
     * @param Router\RouterInterface             $router   for routes
     * @param Template\TemplateRendererInterface $template for templates
     * @param Adapter                            $adapter  for db connection
     */
    public function __construct(Router\RouterInterface $router,
        Template\TemplateRendererInterface $template = null, Adapter $adapter = null
    ) {
        $this->router = $router;
        $this->template = $template;
        $this->adapter = $adapter;
    }

    /**
     * Adds attribute options.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doAdd($post)
    {
        if ($post['submitt'] == 'Save') {
            $wkat_id = $post['id'];
            $table = new \VuBib\Db\Table\WorkAttribute_Option($this->adapter);
            $optId = $table->insertOptionAndReturnId(
                $wkat_id, $post['new_option']
            );

            if (isset($post['subattr_id'])) {
                $subat_id = $post['subattr_id'];
                error_log($subat_id);
                //Insert option to subattribute table
                $table = new \VuBib\Db\Table\Attribute_Option_SubAttribute(
                    $this->adapter
                );
                if (!array_filter($post['newsubattr'])) {
                    $table->insertRecord($wkat_id, $optId, $subat_id);
                } else {
                    foreach ($post['newsubattr'] as $subat) {
                        if ($subat != "") {
                            $table->insertRecord(
                                $wkat_id,
                                $optId,
                                $subat_id,
                                $subat
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Edits attribute options.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doEdit($post)
    {
        if ($post['submitt'] == 'Save') {
            if (null !== $post['id']) {
                $wkat_id = $post['wkat_id'];
                $opt_id =  $post['id'];
                $table = new \VuBib\Db\Table\WorkAttribute_Option($this->adapter);
                $table->updateOption($opt_id, $post['edit_option']);

                //delete subattr vals and insert new values for option
                $table = new \VuBib\Db\Table\Attribute_Option_SubAttribute(
                    $this->adapter
                );
                $table->deleteRecordByOptionId($wkat_id, $opt_id);
                if (!array_filter($post['newsubattr'])
                    && isset($post['subattr_id'])
                ) {
                    //empty - no subattr vals
                    $table->insertRecord($wkat_id, $opt_id, $post['subattr_id']);
                } elseif (isset($post['subattr_id'])) {
                    //not empty - has subattr vals
                    foreach ($post['newsubattr'] as $subat) {
                        if ($subat != "") {
                            $table->insertRecord($wkat_id, $opt_id, $post['subattr_id'], $subat);  //this
                        }
                    }
                }
            }
        }
    }

    /**
     * Edits attribute options.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doEditSubAttrVals($post)
    {
        if ($post['submitt'] == 'Save') {
            if (null !== $post['id']) {
                $wkat_id = $post['wkat_id'];
                $opt_id =  $post['id'];

                //delete subattr vals and insert new values for option
                $table = new \VuBib\Db\Table\Attribute_Option_SubAttribute(
                    $this->adapter
                );
                $table->deleteRecordByOptionId($wkat_id, $opt_id);
                if (!array_filter($post['newsubattr'])
                    && isset($post['subattr_id'])
                ) {
                    //empty - no subattr vals
                    $table->insertRecord($wkat_id, $opt_id, $post['subattr_id']);
                } elseif (isset($post['subattr_id'])) {
                    //not empty - has subattr vals
                    foreach ($post['newsubattr'] as $subat) {
                        if ($subat != "") {
                            $table->insertRecord(
                                $wkat_id, $opt_id, $post['subattr_id'], $subat
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Deletes attribute options.
     *
     * @param Array $post  contains posted elements of form
     * @param Array $query url query parameters
     *
     * @return empty
     */
    protected function doDelete($post, $query)
    {
        if (isset($post['submitt'])) {
            if ($post['submitt'] == 'Delete') {
                if (null !== $post['workattropt_id']) {
                    foreach ($post['workattropt_id'] as $workattropt_Id) {
                        $table = new \VuBib\Db\Table\Work_WorkAttribute(
                            $this->adapter
                        );
                        $table->deleteRecordByValue($query['id'], $workattropt_Id);
                        $table = new \VuBib\Db\Table\Attribute_Option_SubAttribute(
                            $this->adapter
                        );
                        $table->deleteRecordByOptionId(
                            $query['id'], $workattropt_Id
                        );
                        $table = new \VuBib\Db\Table\WorkAttribute_Option(
                            $this->adapter
                        );
                        $table->deleteOption($query['id'], $workattropt_Id);
                    }
                }
            }
        }
    }

    /**
     * Merges attribute options duplicate values.
     *
     * @param Array $post contains posted elements of form
     *
     * @return empty
     */
    protected function doMerge($post)
    {
        if ($post['submitt'] == 'Merge') {
            if (null !== $post['workattribute_id']) {
                for ($i = 0; $i < count($post['option_title']); ++$i) {
                    $table = new \VuBib\Db\Table\WorkAttribute_Option(
                        $this->adapter
                    );
                    $rows = $table->getDuplicateOptionRecords(
                        $post['workattribute_id'], $post['option_title'][$i],
                        $post['option_id'][$i]
                    );

                    for ($j = 0; $j < count($rows); ++$j) {
                        $table = new \VuBib\Db\Table\Work_WorkAttribute(
                            $this->adapter
                        );
                        $table->updateWorkAndWorkAttributeValue(
                            $post['workattribute_id'], $post['option_id'][$i],
                            $rows[$j]['id']
                        );

                        //delete option record from attribute_option_subattribute
                        $table = new \VuBib\Db\Table\Attribute_Option_SubAttribute(
                            $this->adapter
                        );
                        $table->deleteRecordByOptionId(
                            $post['workattribute_id'], $rows[$j]['id']
                        );

                        $table = new \VuBib\Db\Table\WorkAttribute_Option(
                            $this->adapter
                        );
                        $table->deleteOption(
                            $post['workattribute_id'], $rows[$j]['id']
                        );
                    }
                }
            }
        }
    }

    /**
     * Merges attribute options.
     *
     * @param Array $post  contains posted elements of form
     * @param Array $query url query parameters
     *
     * @return empty
     */
    protected function doMergeOptions($post, $query)
    {
        if ($post['submitt'] == 'Merge_Options') {
            if (null !== $post['mrg_attr_id']) {
                $src_select = explode(",", $post['src_opts_hidden']);
                foreach ($src_select as $src_workattropt_Id) {
                    //Update wk_wkattr,set src_wkattropt_Id = dest_select
                    //where wkattr_id = $post['mrg_attr_id']
                    $table = new \VuBib\Db\Table\Work_WorkAttribute(
                        $this->adapter
                    );
                    $table->updateWorkAndWorkAttributeValue(
                        $post['mrg_attr_id'],
                        $post['dest_opt_hidden'], $src_workattropt_Id
                    );

                    //Update attr_opt_subat,set src_wkattropt_Id = dest_select
                    //where wkattr_id = $post['mrg_attr_id']
                    $table = new \VuBib\Db\Table\Attribute_Option_SubAttribute(
                        $this->adapter
                    );
                    $table->updateRecordOptionId(
                        $post['mrg_attr_id'],
                        $post['dest_opt_hidden'], $src_workattropt_Id
                    );

                    //delete src attr opt,where id = $src_wkattropt_Id
                    //and wkatt_id = $post['mrg_attr_id']
                    $table = new \VuBib\Db\Table\WorkAttribute_Option(
                        $this->adapter
                    );
                    $table->deleteOption(
                        $post['mrg_attr_id'], $src_workattropt_Id
                    );
                }
            }
        }
    }

    /**
     * Action based on action parameter.
     *
     * @param Array $post  contains posted elements of form
     * @param Array $query url query parameters
     *
     * @return empty
     */
    protected function doAction($post, $query)
    {
        //add new option
        if ($post['action'] == 'new') {
            $this->doAdd($post);
        }
        //edit option
        if ($post['action'] == 'edit') {
            $this->doEdit($post);
        }
        //delete option
        if ($post['action'] == 'delete') {
            $this->doDelete($post, $query);
        }
        //Merge option
        if ($post['action'] == 'merge') {
            $this->doMerge($post);
        }
        //Merge option
        if ($post['action'] == 'merge_options') {
            $this->doMergeOptions($post, $query);
        }
        //Edit option sub attribute values
        if ($post['action'] == 'edit_sattr_vals') {
            $this->doEditSubAttrVals($post);
        }
    }

    /**
     * Searches attribute options.
     *
     * @param Array $query url query parameters
     *
     * @return matched options
     */
    protected function searchOption($query)
    {
        if ($query['submit'] == 'Search') {
            if (null !== $query['worktype_attr']) {
                $table = new \VuBib\Db\Table\WorkAttribute_Option($this->adapter);
                return $table->findRecords(
                    $query['option'], $query['worktype_attr']
                );
            }
        }
    }

    /**
     * Call aprropriate function for each action.
     *
     * @param Array $query url query parameters
     * @param Array $post  contains posted elements of form
     *
     * @return Paginator                  $paginator
     */
    protected function getPaginator($query, $post)
    {
        $order = "";
        //order by columns
        if (!empty($query['orderBy'])) {
            $sort_ord = $query['sort_ord'];
            $ord_by = $query['orderBy'];

            $order = $ord_by . " " . $sort_ord;
        }
        //Attribute Option Lookup
        if (!empty($query['action'])) {
            if ($query['action'] == 'search_option') {
                return $this->searchOption($query);
            }
        }
        if (!empty($post['action'])) {
            //add edit delete merge option
            $this->doAction($post, $query);

            //Cancel add\edit\delete
            if ($post['submitt'] == 'Cancel') {
                $table = new \VuBib\Db\Table\WorkAttribute_Option($this->adapter);
                $paginator = $table->displayAttributeOptions($query['id']);

                return $paginator;
            }
        }

        // default: blank for listing in manage
        $table = new \VuBib\Db\Table\WorkAttribute_Option($this->adapter);
        $paginator = $table->displayAttributeOptions($query['id'], $order);

        return $paginator;
    }

    /**
     * Invokes required template
     *
     * @param ServerRequestInterface  $request  server-side request.
     * @param RequestHandlerInterface $handler  response to client side.
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $countPages = 0;
        $query = $request->getqueryParams();

        $post = [];
        if ($request->getMethod() == 'POST') {
            $post = $request->getParsedBody();
        }
        $paginator = $this->getPaginator($query, $post);
        $paginator->setDefaultItemCountPerPage(15);
        //$allItems = $paginator->getTotalItemCount();
        $countPages = $paginator->count();

        $currentPage = $query['page'] ?? 1;
        if ($currentPage < 1) {
            $currentPage = 1;
        }
        $paginator->setCurrentPageNumber($currentPage);

        if ($currentPage == $countPages) {
            $next = $currentPage;
            $previous = $currentPage - 1;
        } elseif ($currentPage == 1) {
            $next = $currentPage + 1;
            $previous = 1;
        } else {
            $next = $currentPage + 1;
            $previous = $currentPage - 1;
        }

        $searchParams = [];
        if (!empty($query['id'])) {
            $searchParams[] = 'id=' . urlencode($query['id']);
        }

        if (isset($query['action']) && $query['action'] == 'search_option') {
            $searchParams[] = 'action=search_option&worktype_attr='
                  . urlencode($query['worktype_attr'])
                  . '&option=' . urlencode($query['option'])
                  . '&submit=Search';
        }
        return new HtmlResponse(
            $this->template->render(
                'vubib::worktype/manage_attribute_options',
                [
                'rows' => $paginator,
                'previous' => $previous,
                'next' => $next,
                'countp' => $countPages,
                'searchParams' => implode('&', $searchParams),
                'request' => $request,
                'adapter' => $this->adapter,
                ]
            )
        );
    }
}
