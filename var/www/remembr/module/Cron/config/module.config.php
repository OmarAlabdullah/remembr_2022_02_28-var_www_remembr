<?php

return array(
    // Placeholder for console routes
    'controllers' => array(
        'invokables' => array(
            'Cron\Controller\CronHandler' => 'Cron\Controller\CronHandlerController',
            'Cron\Controller\SendPrivateMessagesReminders' => 'Cron\Controller\SendPrivateMessagesRemindersController',
            'Cron\Controller\SendPageMessagesReminders' => 'Cron\Controller\SendPageMessagesRemindersController',
            'Cron\Controller\SendCommentMessagesReminders' => 'Cron\Controller\SendCommentMessagesRemindersController'
        ),
    ),
    // cron route
    'console' => array(
        'router' => array(
            'routes' => array(
                'private-messages' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'send-private-messages-reminders <freq>',
                        'defaults' => array(
                            'controller' => 'Cron\Controller\SendPrivateMessagesReminders',
                            'action' => 'task'
                        ),
                    )
                ),
                 'page-messages' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'send-page-messages-reminders <freq>',
                        'defaults' => array(
                            'controller' => 'Cron\Controller\SendPageMessagesReminders',
                            'action' => 'task'
                        ),
                    )
                ),
                 'comment-messages' => array(
                    'type' => 'simple',
                    'options' => array(
                        'route' => 'send-comment-messages-reminders <freq>',
                        'defaults' => array(
                            'controller' => 'Cron\Controller\SendCommentMessagesReminders',
                            'action' => 'task'
                        ),
                    )
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'cron/mail/layout' => __DIR__ . '/../view/layout/mail.twig',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);