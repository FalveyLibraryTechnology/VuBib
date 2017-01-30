<?php

namespace App\Action\WorkType;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Paginator;

class AttributeManageOptionsAction
{
    private $router;
    private $template;
    private $adapter;

    public function __construct(Router\RouterInterface $router, Template\TemplateRendererInterface $template = null, Adapter $adapter)
    {
        $this->router   = $router;
        $this->template = $template;
        $this->adapter  = $adapter;
    }
    
    protected function getPaginator($query, $post)
    {
        if (!empty($post['action'])) {
            //add new option
            if ($post['action'] == "new") {
                if ($post['submitt'] == "Save") {
                    $table = new \App\Db\Table\WorkAttribute_Option($this->adapter);
                    $table->addOption($post['id'], $post['new_option'], $post['option_value']);
                }
            }
            //edit option
            if ($post['action'] == "edit") {
                if ($post['submitt'] == "Save") {
                    if (!is_null($post['id'])) {
                        $table = new \App\Db\Table\WorkAttribute_Option($this->adapter);
                        $table->updateOption($post['id'], $post['edit_option'], $post['edit_value']);
                    }
                }
            }
            //delete option
            if ($post['action'] == "delete") {
                if ($post['submitt'] == "Delete") {
                    if (!is_null($post['id'])) {
                        $table = new \App\Db\Table\Work_Workattribute($this->adapter);
                        $table->deleteRecordByValue($query['id'], $post['id']);
                        $table = new \App\Db\Table\WorkAttribute_Option($this->adapter);
                        $table->deleteOption($query['id'], $post['id']);
                    }
                }
            }
            //Cancel add\edit\delete
            if ($post['submitt'] == "Cancel") {
                $table = new \App\Db\Table\WorkAttribute_Option($this->adapter);
                $paginator = $table->displayAttributeOptions($query['id']);
                return $paginator;
            }
        }
        // default: blank for listing in manage
        $table = new \App\Db\Table\WorkAttribute_Option($this->adapter);
        $paginator = $table->displayAttributeOptions($query['id']);
        return $paginator;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $countPages = 0;
        $query = $request->getqueryParams();
        if (!empty($query['action'])) {
            $action = $query['action'];
        }
        $post = [];
        if ($request->getMethod() == "POST") {
            $post = $request->getParsedBody();
        }
        $paginator = $this->getPaginator($query, $post);
        $paginator->setDefaultItemCountPerPage(10);
        $allItems = $paginator->getTotalItemCount();
        $countPages = $paginator->count();

        $currentPage = isset($query['page']) ? $query['page'] : 1;
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
        
        return new HtmlResponse(
            $this->template->render(
                'app::worktype::manage_attribute_options',
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
