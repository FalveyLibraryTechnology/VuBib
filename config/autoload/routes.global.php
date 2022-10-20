<?php
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;

return [
    'dependencies' => [
        'invokables' => [
            Mezzio\Router\RouterInterface::class => Mezzio\Router\FastRouteRouter::class,
            VuBib\PingAction::class => VuBib\PingAction::class,
        ],
        'factories' => [
            VuBib\Action\HomePageAction::class => VuBib\Action\HomePageFactory::class,
            //VuBib\Action\LoginPageAction::class => VuBib\Action\LoginPageFactory::class,
            VuBib\Action\DefaultPageAction::class => VuBib\Action\DefaultPageFactory::class,
            //VuBib\Action\SimpleRenderAction::class => VuBib\Action\SimpleRenderFactory::class,

            VuBib\Action\Work\GetWorkDetailsAction::class => VuBib\Action\Work\GetWorkDetailsFactory::class,
            VuBib\Action\Work\ManageWorkAction::class => VuBib\Action\Work\ManageWorkFactory::class,
            VuBib\Action\Work\ClassifyWorkAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Work\DeleteWorkAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Work\EditWorkAction::class => VuBib\Action\Work\EditWorkFactory::class,
            VuBib\Action\Work\NewWorkAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Work\ReviewWorkAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Work\SearchWorkAction::class => VuBib\Action\SimpleRenderFactory::class,

            VuBib\Action\WorkType\AttributeManageOptionsAction::class => VuBib\Action\WorkType\AttributeManageOptionsFactory::class,
            VuBib\Action\WorkType\AttributesWorkTypeAction::class => VuBib\Action\WorkType\AttributesWorkTypeFactory::class,
            VuBib\Action\WorkType\ManageSubAttributesAction::class => VuBib\Action\WorkType\ManageSubAttributesFactory::class,
            VuBib\Action\WorkType\ManageWorkTypeAction::class => VuBib\Action\WorkType\ManageWorkTypeFactory::class,
            VuBib\Action\WorkType\DeleteAttributeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\DeleteOptionAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\DeleteSubAttributeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\DeleteWorkTypeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\EditAttributeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\EditOptionAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\EditSubAttributeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\EditSubAttributeValuesAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\EditWorkTypeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\ManageWorkTypeAttributeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\MergeDuplicateOptionAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\MergeOptionAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\NewAttributeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\NewOptionAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\NewSubAttributeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\NewWorkTypeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\WorkType\SearchOptionAction::class => VuBib\Action\SimpleRenderFactory::class,

            VuBib\Action\Classification\ManageClassificationAction::class => VuBib\Action\Classification\ManageClassificationFactory::class,
            VuBib\Action\Classification\MoveClassificationAction::class => VuBib\Action\Classification\MoveClassificationFactory::class,
            VuBib\Action\Classification\NewClassificationAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Classification\MergeClassificationAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Classification\ExportListClassificationAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Classification\EditClassificationAction::class => VuBib\Action\SimpleRenderFactory::class,

            VuBib\Action\Agent\ManageAgentAction::class => VuBib\Action\Agent\ManageAgentFactory::class,
            VuBib\Action\Agent\NewAgentAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Agent\FindAgentAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Agent\EditAgentAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Agent\DeleteAgentAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Agent\MergeAgentAction::class => VuBib\Action\SimpleRenderFactory::class,

            VuBib\Action\AgentType\ManageAgentTypeAction::class => VuBib\Action\AgentType\ManageAgentTypeFactory::class,
            VuBib\Action\AgentType\NewAgentTypeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\AgentType\EditAgentTypeAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\AgentType\DeleteAgentTypeAction::class => VuBib\Action\SimpleRenderFactory::class,

            VuBib\Action\Publisher\ManagePublisherAction::class => VuBib\Action\Publisher\ManagePublisherFactory::class,
            VuBib\Action\Publisher\ManagePublisherLocationAction::class => VuBib\Action\Publisher\ManagePublisherLocationFactory::class,
            VuBib\Action\Publisher\AddPublisherLocationAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Publisher\DeleteMergePublisherLocationAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Publisher\DeletePublisherAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Publisher\EditPublisherAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Publisher\EditPublisherLocationAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Publisher\FindPublisherAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Publisher\MergePublisherAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Publisher\NewPublisherAction::class => VuBib\Action\SimpleRenderFactory::class,

            VuBib\Action\Language\ManageLanguageAction::class => VuBib\Action\Language\ManageLanguageFactory::class,
            VuBib\Action\Language\NewLanguageAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Language\EditLanguageAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\Language\DeleteLanguageAction::class => VuBib\Action\SimpleRenderFactory::class,

            VuBib\Action\User\ManageUserAction::class => VuBib\Action\User\ManageUserFactory::class,
            VuBib\Action\User\AccessUserAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\User\DeleteUserAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\User\EditUserAction::class => VuBib\Action\SimpleRenderFactory::class,
            VuBib\Action\User\NewUserAction::class => VuBib\Action\SimpleRenderFactory::class,

            VuBib\Action\Preferences\ChangePasswordPreferencesAction::class => VuBib\Action\Preferences\ChangePasswordPreferencesFactory::class,
        ],
        /*'aliases'  =>
        [
            VuBib\Action\Agent\NewAgentAction::class => VuBib\Action\SimpleRenderAction::class,
        ],*/
    ],

    'routes' => [
        [
            'name' => 'home',
            'path' => '/',
            'middleware' => [
                \VuBib\Middleware\AuthenticationMiddleware::class,
                BodyParamsMiddleware::class,
                VuBib\Action\HomePageAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'api.ping',
            'path' => '/api/ping',
            'middleware' => VuBib\PingHandler::class,
        ],

        [
            'name' => 'default',
            'path' => '/default_latest',
            'middleware' => VuBib\Action\DefaultPageAction::class,
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_work',
            'path' => '/Work/new',
            'middleware' => [
                \VuBib\Middleware\AuthenticationMiddleware::class,
                //BodyParamsMiddleware::class,
                VuBib\Action\Work\EditWorkAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
           'name' => 'get_work_details',
            'path' => '/Work/get_work_details',
            'middleware' => [
                \VuBib\Middleware\AuthenticationMiddleware::class,
                //BodyParamsMiddleware::class,
                VuBib\Action\Work\GetWorkDetailsAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'search_work',
            'path' => '/Work/search',
            'middleware' => [
                \VuBib\Middleware\AuthenticationMiddleware::class,
                //BodyParamsMiddleware::class,
                VuBib\Action\Work\SearchWorkAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_work',
            'path' => '/Work/manage',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Work\ManageWorkAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_work',
            'path' => '/Work/edit',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Work\EditWorkAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_work',
            'path' => '/Work/delete',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Work\DeleteWorkAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'review_work',
            'path' => '/Work/review',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Work\ReviewWorkAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'classify_work',
            'path' => '/Work/classify',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Work\ClassifyWorkAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_worktype',
            'path' => '/WorkType/new',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\NewWorkTypeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_worktype',
            'path' => '/WorkType/manage',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\ManageWorkTypeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_worktype',
            'path' => '/WorkType/edit',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\EditWorkTypeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_worktype',
            'path' => '/WorkType/delete',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\DeleteWorkTypeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_worktypeattribute',
            'path' => '/WorkType/manage_attribute',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\ManageWorkTypeAttributeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'attributes_worktype',
            'path' => '/WorkType/attributes',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\AttributesWorkTypeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_attribute',
            'path' => '/WorkType/new_attribute',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\NewAttributeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_attribute',
            'path' => '/WorkType/edit_attribute',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\EditAttributeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_attribute',
            'path' => '/WorkType/delete_attribute',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\DeleteAttributeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_subattributes',
            'path' => '/WorkType/manage_subattributes',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\ManageSubAttributesAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_subattribute',
            'path' => '/WorkType/new_subattribute',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\NewSubAttributeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_subattribute',
            'path' => '/WorkType/edit_subattribute',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\EditSubAttributeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_subattribute',
            'path' => '/WorkType/delete_subattribute',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\DeleteSubAttributeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_subattribute_values',
            'path' => '/WorkType/edit_subattribute_values',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\EditSubAttributeValuesAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_attribute_options',
            'path' => '/WorkType/manage_attribute_options',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\AttributeManageOptionsAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_option',
            'path' => '/WorkType/new_option',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\NewOptionAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_option',
            'path' => '/WorkType/edit_option',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\EditOptionAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_option',
            'path' => '/WorkType/delete_option',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\DeleteOptionAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'merge_options',
            'path' => '/WorkType/merge_options',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\MergeOptionAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'merge_duplicate_option',
            'path' => '/WorkType/merge_duplicate_option',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\MergeDuplicateOptionAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'search_attribute_option',
            'path' => '/WorkType/search_attribute_option',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\WorkType\SearchOptionAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_classification',
            'path' => '/Classification/new',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Classification\NewClassificationAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_classification',
            'path' => '/Classification/manage',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Classification\ManageClassificationAction::class,
            ],
            'allowed_methods' => ['GET', 'POST'],
        ],

        [
            'name' => 'merge_classification',
            'path' => '/Classification/merge',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Classification\MergeClassificationAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'exportlist_classification',
            'path' => '/Classification/export',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Classification\ExportListClassificationAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_classification',
            'path' => '/Classification/edit',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Classification\EditClassificationAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'move_classification',
            'path' => '/Classification/move',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Classification\MoveClassificationAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_agent',
            'path' => '/Agent/new',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Agent\NewAgentAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'find_agent',
            'path' => '/Agent/find',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Agent\FindAgentAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_agent',
            'path' => '/Agent/manage',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Agent\ManageAgentAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_agent',
            'path' => '/Agent/edit',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Agent\EditAgentAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_agent',
            'path' => '/Agent/delete',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Agent\DeleteAgentAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'merge_agent',
            'path' => '/Agent/merge',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Agent\MergeAgentAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_agenttype',
            'path' => '/AgentType/new',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\AgentType\NewAgentTypeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_agenttype',
            'path' => '/AgentType/manage',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\AgentType\ManageAgentTypeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_agenttype',
            'path' => '/AgentType/edit',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\AgentType\EditAgentTypeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_agenttype',
            'path' => '/AgentType/delete',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\AgentType\DeleteAgentTypeAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_publisher',
            'path' => '/Publisher/newpublisher',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Publisher\NewPublisherAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'find_publisher',
            'path' => '/Publisher/findpublisher',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Publisher\FindPublisherAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_publisher',
            'path' => '/Publisher/managepublisher[/page/{page}]',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Publisher\ManagePublisherAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'add_publisher_location',
            'path' => '/Publisher/new_location',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Publisher\AddPublisherLocationAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_publisher_location',
            'path' => '/Publisher/edit_location',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Publisher\EditPublisherLocationAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_merge_publisher_location',
            'path' => '/Publisher/delete_merge_location',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Publisher\DeleteMergePublisherLocationAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_publisherlocation',
            'path' => '/Publisher/manage_location',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Publisher\ManagePublisherLocationAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_publisher',
            'path' => '/Publisher/edit_publisher',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Publisher\EditPublisherAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_publisher',
            'path' => '/Publisher/delete_publisher',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Publisher\DeletePublisherAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'merge_publisher',
            'path' => '/Publisher/merge',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Publisher\MergePublisherAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_language',
            'path' => '/Language/new',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Language\NewLanguageAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_language',
            'path' => '/Language/manage[/page/{page}]',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Language\ManageLanguageAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_language',
            'path' => '/Language/edit',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Language\EditLanguageAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_language',
            'path' => '/Language/delete',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Language\DeleteLanguageAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'new_user',
            'path' => '/User/new',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\User\NewUserAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'manage_user',
            'path' => '/User/manage',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\User\ManageUserAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'edit_user',
            'path' => '/User/edit',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\User\EditUserAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'delete_user',
            'path' => '/User/delete',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\User\DeleteUserAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'access_user',
            'path' => '/User/access',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\User\AccessUserAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],

        [
            'name' => 'changepassword_preferences',
            'path' => '/Preferences/changepassword',
            'middleware' => [
                //BodyParamsMiddleware::class,
                \VuBib\Middleware\AuthenticationMiddleware::class,
                VuBib\Action\Preferences\ChangePasswordPreferencesAction::class,
            ],
            'allowed_methods' => ['GET','POST'],
        ],
    ],
];
